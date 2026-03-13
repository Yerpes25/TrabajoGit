<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>Login - <?php echo e(config('app.name', 'Laravel')); ?></title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>

    <style>
        :root {
            --primary: #62bd19;
            --primary-dark: #4aa012;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f9fafb;
            --white: #ffffff;
            --input-bg: #eef2f6;
            --link-blue: #667eea;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* LAYOUT PRINCIPAL */
        .login-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* PANEL IZQUIERDO (OCULTO EN MÓVIL) */
        .brand-panel {
            display: none; /* Se activa en Desktop */
            background: linear-gradient(135deg, #62bd19 0%, #3e880f 100%);
            color: var(--white);
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            text-align: center;
            position: relative;
        }

        .brand-title {
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .brand-slogan { font-size: 2.5rem; font-weight: 700; margin-bottom: 2rem; z-index: 2; line-height: 1.2; }
        .brand-description { max-width: 400px; font-size: 1.1rem; opacity: 0.9; z-index: 2; margin-bottom: 3rem; }

        .brand-features {
            text-align: left;
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem; /* Espacio uniforme en los 4 lados */
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 2;
        }

        .brand-features div {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem; /* Espacio entre líneas */
        }

        /* LA CLAVE DE LA SIMETRÍA */
        .brand-features div:last-child {
            margin-bottom: 0; /* Eliminamos el espacio extra al final */
        }

        /* PANEL DERECHO (FORMULARIO) */
        .login-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem 1.5rem; /* Margen para que no toque bordes en móvil */
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
            /* Diseño anterior: sin caja, fondo limpio */
        }

        .form-header {
            text-align: left;
            margin-bottom: 2.5rem;
            width: 100%;
        }

        .form-title { font-size: 32px; font-weight: 700; color: var(--text-main); }
        .form-subtitle { color: var(--text-muted); font-size: 1rem; margin-top: 0.5rem; }

        /* INPUTS */
        .input-group { margin-bottom: 1.25rem; position: relative; }

        .input-label {
            display: none; /* Oculto en móvil */
            font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 18px 16px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-size: 1rem;
            background: var(--input-bg);
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: #828c9a; cursor: pointer;
        }

        /* BOTÓN Y OPCIONES */
        .btn-primary {
            width: 100%; padding: 16px; border: none; border-radius: 12px;
            background: var(--primary); color: var(--white); font-weight: 600;
            font-size: 1rem; cursor: pointer; transition: 0.2s; margin-top: 1rem;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .form-options {
            display: flex; justify-content: space-between; align-items: center;
            margin: 1.5rem 0; font-size: 0.9rem;
        }

        .checkbox-label { display: flex; align-items: center; gap: 8px; color: var(--text-main); cursor: pointer; }
        .checkbox-label input { accent-color: var(--primary); width: 18px; height: 18px; }

        .link-secondary { color: var(--link-blue); text-decoration: none; font-weight: 500; }

        .divider {
            display: flex; align-items: center; margin: 1.5rem 0; font-size: 0.75rem;
            color: var(--text-muted); letter-spacing: 1.5px;
        }
        .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--border); }
        .divider span { padding: 0 1rem; }

        /* LOGO */
        .global-logo {
            padding: 1rem 0;
            display: flex;
            justify-content: center;

            /* AÑADE ESTO */
            position: relative;
            top: -40px;  /* Cuanto más alto sea el número negativo, más subirá el logo */
        }
        .global-logo img { width: 75px; height: auto; }

        .footer-links { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted); }

        .footer-links p {
            margin-bottom: 1.25rem; /* Ajusta este número según el espacio que quieras */
        }

        /* === DESKTOP CONFIG === */
        @media (min-width: 900px) {
            .login-container {
                flex-direction: row;
                display: grid;
                grid-template-columns: 1fr 1fr; /* 50/50 exacto */
            }

            .brand-panel { display: flex; }

            .global-logo {
                position: absolute; top: 2rem; right: 3rem; padding: 0;
            }

            .login-form-panel { padding: 4rem; }
            .input-label { display: block; } /* En PC mostramos labels */
            .password-toggle { top: 42px; transform: none; } /* Ajuste por label */
        }
    </style>
</head>
<body>

<div class="login-container">

    <div class="brand-panel">
        <div class="brand-title">Cubetic Gestión Bonos</div>
        <h1 class="brand-slogan">Gestiona tus horas<br>de manera inteligente</h1>
        <p class="brand-description">
            Accede a tu panel para consumir horas, revisar servicios contratados
            y administrar tus bonos de forma rápida.
        </p>
        <div class="brand-features">
            <div>✓ Consulta tus horas disponibles</div>
            <div>✓ Gestiona tus bonos fácilmente</div>
            <div>✓ Acceso multiplataforma 24/7</div>
        </div>
    </div>

    <div class="login-form-panel">

        <div class="global-logo">
            <img src="<?php echo e(asset('favicon.svg')); ?>" alt="Logo Cubetic">
        </div>

        <div class="login-form-container">
            <div class="form-header">
                <h2 class="form-title">Bienvenido</h2>
                <p class="form-subtitle">Inicia sesión en Cubetic Bonos.</p>
            </div>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>

                <div class="input-group">
                    <label for="email" class="input-label">Correo Electrónico</label>
                    <input id="email" type="email" name="email" class="form-input" placeholder="tu@email.com" required autofocus />
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('email'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('email')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Contraseña</label>
                    <input id="password" type="password" name="password" class="form-input" placeholder="••••••••" required />
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('password'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('password')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Recuérdame
                    </label>
                    <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>" class="link-secondary">¿Has olvidado tu contraseña?</a>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">Iniciar Sesión</button>
            </form>

            <div class="divider"><span>ENTERPRISE SSO</span></div>

            <div class="footer-links">
                <p>© <?php echo e(date('Y')); ?> Cubetic Bonos Inc.</p>
                <div style="margin-top: 10px;">
                    <a href="#" class="link-secondary" style="font-size: 12px;">PRIVACIDAD</a> |
                    <a href="#" class="link-secondary" style="font-size: 12px;">TÉRMINOS</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById("password");
        input.type = input.type === "password" ? "text" : "password";
    }
</script>

</body>
</html>
<?php /**PATH /var/www/src/resources/views/auth/login.blade.php ENDPATH**/ ?>