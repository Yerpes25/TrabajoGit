<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreditClientRequest;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Requests\Admin\IssueBonusRequest;
use App\Models\Bonus;
use App\Models\Client;
use App\Services\BalanceService;
use App\Services\AuditService;
use App\Services\UserService;
use App\Services\BonusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

/**
 * Controller para gestionar clientes desde el panel admin.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * La lógica de negocio (asignación de saldo) está en BalanceService.
 */
class AdminClientController extends Controller
{
    private BalanceService $balanceService;
    private UserService $userService;
    private BonusService $bonusService;
    private AuditService $auditService;

    public function __construct(BalanceService $balanceService, AuditService $auditService, UserService $userService, BonusService $bonusService)
    {
        // Inyectar AuditService en BalanceService para que se registre auditoría
        $this->balanceService = new BalanceService($auditService);
        $this->userService = $userService;
        $this->bonusService = new BonusService($this->balanceService, $auditService);
        $this->auditService = $auditService;
    }

    /**
     * Lista todos los clientes con paginación.
     *
     * @return View
     */
    public function index(): View
    {
        // Optimización performance: eager loading de profile y user para evitar N+1
        // NOTE: La vista accede a $client->profile->balance_seconds, sin eager loading haría 1 query por cliente
        $clients = Client::with(['profile', 'user'])
            ->orderBy('created_at', 'desc')
            ->join('users', 'users.id', '=', 'clients.user_id')
            ->select(['clients.*', 'users.email', 'users.name'])
            ->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.clients.create');
    }

    /**
     * Almacena un nuevo cliente.
     *
     * Controller fino: solo valida, crea y redirige.
     * La lógica de negocio está en UserService.
     * Regla: Se crea User + Client + ClientProfile en transacción.
     *
     * @param StoreClientRequest $request
     * @return RedirectResponse
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->userService->createClient(
            $request->getUserData(),
            $request->getClientData()
        );

        $this->auditService->log(
            'client_created',
            Auth::id(),
            'Client',
            $client->id,
            ['name' => $client->name],
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Cliente {$client->name} creado correctamente.");
    }

    /**
     * Muestra el detalle de un cliente: saldo, movimientos y partes.
     *
     * Controller fino: solo carga datos y los pasa a la vista.
     *
     * @param Client $client
     * @return View
     */
    public function show(Client $client): View
    {
        // Cargar perfil con saldo agregado
        $client->load('profile');

        //Cargamos el correo y el nombre del cliente
        $email = $client->user()->first()->email;
        $name = $client->user()->first()->name;

        // Obtener saldo desde el ledger (fuente de verdad)
        $balanceSeconds = $this->balanceService->getBalanceSeconds($client);

        // Cargar movimientos de saldo (paginados) - optimización: eager loading de creator
        // NOTE: La vista accede a $movement->creator->name, sin eager loading haría 1 query por movimiento
        $balanceMovements = $client->balanceMovements()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'movements_page');

        // Cargar partes del cliente (paginados) - optimización: eager loading de relaciones
        // NOTE: La vista accede a $report->technician->name, sin eager loading haría 1 query por parte
        $workReports = $client->workReports()
            ->with(['technician', 'validator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'reports_page');

        // Cargar bonos emitidos al cliente (paginados) - optimización: eager loading de relaciones
        // NOTE: La vista accede a $issue->bonus->name y $issue->issuer->name
        $bonusIssues = $client->bonusIssues()
            ->with(['bonus', 'issuer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'bonuses_page');

        // Cargar bonos activos para el formulario de emisión
        $activeBonuses = Bonus::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.clients.show', compact(
            'client',
            'email',
            'name',
            'balanceSeconds',
            'balanceMovements',
            'workReports',
            'bonusIssues',
            'activeBonuses'
        ));
    }

    /**
     * Emite un bono a un cliente.
     *
     * Controller fino: solo valida, emite y redirige.
     * La lógica de negocio está en BonusService.
     * Regla: Se crea BonusIssue + balance_movement (credit) en transacción.
     *
     * @param IssueBonusRequest $request
     * @param Client $client
     * @return RedirectResponse
     */
    public function issueBonus(IssueBonusRequest $request, Client $client): RedirectResponse
    {
        $bonus = Bonus::findOrFail($request->input('bonus_id'));
        $issuer = Auth::user();

        try {
            $bonusIssue = $this->bonusService->issue(
                $bonus,
                $client,
                $issuer,
                $request->input('note')
            );

            $hours = number_format($bonus->seconds_total / 3600, 2);

            return redirect()->route('admin.clients.show', $client)
                ->with('success', "Bono {$bonus->name} ({$hours} horas) emitido correctamente al cliente {$client->name}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.clients.show', $client)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Asigna saldo (crédito) a un cliente.
     *
     * Controller fino: solo valida, convierte horas a segundos y llama a BalanceService.
     * Regla: El saldo siempre se maneja en segundos internamente.
     *
     * @param CreditClientRequest $request
     * @param Client $client
     * @return RedirectResponse
     */
    public function credit(CreditClientRequest $request, Client $client): RedirectResponse
    {
        // Convertir horas a segundos (regla: saldo siempre en segundos)
        $seconds = $request->getSeconds();

        // Asignar saldo usando BalanceService (lógica de negocio)
        $reason = $request->input('reason', 'admin_credit');
        $metadata = $request->input('metadata', []);

        $this->balanceService->credit(
            $client,
            $seconds,
            $reason,
            'User', // reference_type
            Auth::id(), // reference_id (admin que asigna)
            Auth::id(), // created_by
            $metadata
        );

        $hours = $request->input('hours');

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Se asignaron {$hours} horas ({$seconds} segundos) al cliente {$client->name}.");
    }

    /**
     * Muestra el formulario para editar un cliente.
     *
     * @param Client $client
     * @return View
     */
    public function edit(Client $client): View
    {
        // Cargar relaciones necesarias
        $client->load('user', 'profile');

        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Actualiza un cliente existente.
     *
     * Controller fino: solo valida, actualiza y redirige.
     * La lógica de negocio está en UserService.
     * Regla: Actualiza User + Client en transacción.
     *
     * @param UpdateClientRequest $request
     * @param Client $client
     * @return RedirectResponse
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->userService->updateClient(
            $client,
            $request->getUserData(),
            $request->getClientData()
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Cliente {$client->name} actualizado correctamente.");
    }

    /**
     * "Elimina" un cliente (desactiva si tiene actividad).
     *
     * Regla: Si tiene actividad (work_reports o balance_movements), solo se desactiva (is_active=false).
     * Esto mantiene la integridad referencial de los datos históricos.
     *
     * @param Client $client
     * @return RedirectResponse
     */
    public function destroy(Client $client): RedirectResponse
    {
        $user = $client->user;

        if (!$user) {
            // Si no tiene usuario asociado, eliminar directamente
            $client->delete();
            return redirect()->route('admin.clients.index')
                ->with('success', "Cliente {$client->name} eliminado correctamente.");
        }

        $hasActivity = $this->userService->hasActivity($user);

        if ($hasActivity) {
            // Desactivar en vez de eliminar
            $this->userService->deactivate($user);
            $message = "Cliente {$client->name} desactivado correctamente (tiene actividad asociada).";
        } else {
            // Si no tiene actividad, se puede eliminar físicamente
            // Eliminar ClientProfile primero (si existe)
            if ($client->profile) {
                $client->profile->delete();
            }
            // Eliminar Client
            $client->delete();
            // Eliminar User
            $user->delete();
            $message = "Cliente {$client->name} eliminado correctamente.";
        }

        return redirect()->route('admin.clients.index')
            ->with('success', $message);
    }

    public function sendPasswordEmail(\App\Models\Client $client)
    {
        // Asumiendo que el cliente tiene relación con la tabla users (client->user)
        $user = $client->user;

        if (!$user) {
            return back()->with('error', 'Este cliente no tiene un usuario de acceso asignado.');
        }

        // 1. Generamos un enlace válido por 48 horas
        $url = URL::temporarySignedRoute(
            'client.password.setup',
            now()->addHours(48),
            ['user' => $user->id]
        );

        // 2. Enviamos el correo (usando una vista inline para no crear más archivos de los necesarios)
        Mail::send('emails.setup-password', ['url' => $url, 'client' => $client], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Configura tu contraseña de acceso - Cubetic');
        });

        return back()->with('success', 'Correo enviado correctamente al cliente.');
    }
}
