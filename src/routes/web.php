<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Technician\TechnicianWorkReportController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\ClientDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\ClientPasswordController;

Route::post('/admin/clients/{client}/send-password', [AdminClientController::class, 'sendPasswordEmail'])
    ->name('admin.clients.send-password');

// 2. Rutas públicas para que el cliente configure su contraseña (fuera de los middleware de auth)
Route::get('/configurar-password/{user}', [ClientPasswordController::class, 'create'])
    ->name('client.password.setup')
    ->middleware('signed'); // IMPORTANTE: Protege la ruta para que no se pueda falsificar

Route::post('/configurar-password/{user}', [ClientPasswordController::class, 'store'])
    ->name('client.password.store')
    ->middleware('signed');

Route::get('/', function () {
    return view('welcome');
});

// Rutas de dashboards por rol (protegidas con auth + role middleware)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// Rutas del panel admin (todas protegidas con auth + role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Usuarios
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::post('users/{user}/toggle', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleActive'])
        ->name('users.toggle');

    // Clientes
    Route::resource('clients', \App\Http\Controllers\Admin\AdminClientController::class);
    Route::post('/admin/clients/{client}/send-password', [AdminClientController::class, 'sendPasswordEmail'])->name('admin.clients.send-password');
    Route::post('clients/{client}/credit', [\App\Http\Controllers\Admin\AdminClientController::class, 'credit'])->name('clients.credit');
    Route::post('clients/{client}/bonuses/issue', [\App\Http\Controllers\Admin\AdminClientController::class, 'issueBonus'])->name('clients.bonuses.issue');

    // Técnicos
    Route::resource('technicians', \App\Http\Controllers\Admin\AdminTechnicianController::class);

    // Bonos
    Route::resource('bonuses', \App\Http\Controllers\Admin\AdminBonusController::class);

    // Partes de trabajo
    Route::get('work-reports', [\App\Http\Controllers\Admin\AdminWorkReportController::class, 'index'])->name('work-reports.index');
    Route::get('work-reports/{workReport}', [\App\Http\Controllers\Admin\AdminWorkReportController::class, 'show'])->name('work-reports.show');
    Route::get('work-reports/{workReport}/edit', [\App\Http\Controllers\Admin\AdminWorkReportController::class, 'edit'])->name('work-reports.edit');
    Route::put('work-reports/{workReport}', [\App\Http\Controllers\Admin\AdminWorkReportController::class, 'update'])->name('work-reports.update');

    // Auditoría
    Route::get('audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'role:technician'])->group(function () {
    Route::get('/technician', [TechnicianDashboardController::class, 'index'])->name('technician.dashboard');
});

// Rutas del panel técnico (todas protegidas con auth + role:technician)
Route::middleware(['auth', 'role:technician'])->prefix('technician')->name('technician.')->group(function () {
    // Partes de trabajo
    Route::resource('work-reports', \App\Http\Controllers\Technician\TechnicianWorkReportController::class);

    // Acciones cronómetro
    Route::post('work-reports/{workReport}/start', [\App\Http\Controllers\Technician\TechnicianWorkReportController::class, 'start'])
        ->name('work-reports.start');
    Route::post('work-reports/{workReport}/pause', [\App\Http\Controllers\Technician\TechnicianWorkReportController::class, 'pause'])
        ->name('work-reports.pause');
    Route::post('work-reports/{workReport}/resume', [\App\Http\Controllers\Technician\TechnicianWorkReportController::class, 'resume'])
        ->name('work-reports.resume');
    Route::post('work-reports/{workReport}/finish', [\App\Http\Controllers\Technician\TechnicianWorkReportController::class, 'finish'])
        ->name('work-reports.finish');

    // Evidencias
    Route::post('work-reports/{workReport}/evidences', [\App\Http\Controllers\Technician\TechnicianEvidenceController::class, 'upload'])
        ->name('work-reports.evidences.upload');
    Route::delete('evidences/{evidence}', [\App\Http\Controllers\Technician\TechnicianEvidenceController::class, 'delete'])
        ->name('evidences.delete');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client', [ClientDashboardController::class, 'index'])->name('client.dashboard');
});

// Rutas del portal cliente (todas protegidas con auth + role:client)
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    // Partes de trabajo (solo lectura)
    Route::get('work-reports', [\App\Http\Controllers\Client\ClientWorkReportController::class, 'index'])->name('work-reports.index');
    Route::get('work-reports/{workReport}', [\App\Http\Controllers\Client\ClientWorkReportController::class, 'show'])->name('work-reports.show');
});

// Dashboard genérico (redirige según rol, pero mantenemos por compatibilidad)
Route::get('/dashboard', function () {
    $user = request()->user();
    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'technician' => redirect()->route('technician.dashboard'),
        'client' => redirect()->route('client.dashboard'),
        default => view('dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de perfil (accesibles para todos los usuarios autenticados)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Descarga segura de evidencias (protegida por auth + Policy)
    Route::get('evidences/{evidence}/download', [\App\Http\Controllers\EvidenceDownloadController::class, 'download'])
        ->name('evidences.download');
});

Route::post('/technician/work-reports/{workReport}/validate', [TechnicianWorkReportController::class, 'validate'])
    ->name('technician.work-reports.validate');

require __DIR__ . '/auth.php';
