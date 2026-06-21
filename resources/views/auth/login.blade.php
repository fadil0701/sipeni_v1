<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --login-primary: #1d4ed8;
            --login-primary-dark: #1e3a8a;
            --login-bg: #f1f5f9;
            --login-card-border: #e2e8f0;
            --login-text-main: #0f172a;
            --login-text-muted: #64748b;
            --login-danger: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        body.login-page {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: var(--login-bg);
            color: var(--login-text-main);
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-panel {
            width: 100%;
            max-width: 430px;
        }

        .login-card {
            background: #fff;
            border: 1px solid var(--login-card-border);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }

        .login-header {
            text-align: center;
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--login-primary-dark), var(--login-primary));
            color: #fff;
        }

        .login-logo-box {
            width: 74px;
            height: 74px;
            margin: 0 auto 0.875rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.2);
        }

        .login-logo-box img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            display: block;
        }

        .login-title {
            margin: 0;
            font-size: 1.55rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .login-subtitle {
            margin: 0.5rem 0 0;
            color: #dbeafe;
            font-size: 0.875rem;
        }

        .login-body {
            padding: 1.5rem;
        }

        .login-form {
            display: grid;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            top: 50%;
            left: 0.8rem;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            color: #94a3b8;
        }

        .login-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.72rem 0.85rem 0.72rem 2.4rem;
            font-size: 0.92rem;
            color: #0f172a;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .login-input:focus {
            outline: none;
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }

        .login-input.input-error {
            border-color: var(--login-danger);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        .login-input.password-input {
            padding-right: 2.75rem;
        }

        .password-toggle-btn {
            position: absolute;
            top: 50%;
            right: 0.65rem;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            border: 0;
            border-radius: 0.5rem;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: #64748b;
            background: #f1f5f9;
        }

        .password-toggle-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.35);
        }

        .password-toggle-btn svg {
            width: 1.15rem;
            height: 1.15rem;
        }

        .password-toggle-btn .icon-eye-off {
            display: none;
        }

        .password-toggle-btn.is-visible .icon-eye {
            display: none;
        }

        .password-toggle-btn.is-visible .icon-eye-off {
            display: block;
        }

        .field-error {
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: var(--login-danger);
        }

        .login-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .remember-wrap {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.875rem;
            color: #334155;
        }

        .remember-wrap input {
            width: 0.95rem;
            height: 0.95rem;
            accent-color: var(--login-primary);
        }

        .login-link {
            text-decoration: none;
            font-size: 0.85rem;
            color: var(--login-primary);
            font-weight: 600;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .login-submit {
            width: 100%;
            border: 0;
            border-radius: 0.75rem;
            background: var(--login-primary);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 600;
            padding: 0.78rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .login-submit:hover {
            background: var(--login-primary-dark);
        }

        .login-submit:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
        }

        .login-alert {
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            padding: 0.85rem 1rem;
            font-size: 0.85rem;
        }

        .login-alert ul {
            margin: 0.35rem 0 0;
            padding-left: 1.1rem;
        }

        .login-footer {
            margin-top: 0.9rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--login-text-muted);
        }
    </style>
</head>

<body class="login-page">
    <div class="login-wrapper">
        <div class="login-panel">
            
            <!-- Login Card -->
            <div class="login-card">

                <!-- Header -->
                <div class="login-header">
                    
                    <div class="login-logo-box">
                        <img 
                            src="{{ asset('images/favicon.png') }}" 
                            alt="{{ config('app.name') }}"
                        >
                    </div>

                    <h1 class="login-title">
                        {{ config('app.name') }}
                    </h1>

                    <p class="login-subtitle">
                        Sistem Manajemen Aset & Inventory
                    </p>
                </div>

                <!-- Body -->
                <div class="login-body">

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="login-alert">
                            <strong>Terjadi kesalahan:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form -->
                    <form 
                        method="POST" 
                        action="{{ route('login') }}"
                        class="login-form"
                        data-confirm="off"
                    >
                        @csrf

                        <!-- Email -->
                        <div class="form-group">
                            <label 
                                for="email"
                            >
                                Email
                            </label>

                            <div class="input-wrap">

                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-16 11h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                </svg>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    value="{{ old('email') }}"
                                    placeholder="Masukkan email"
                                    class="login-input @error('email') input-error @enderror"
                                >
                            </div>

                            @error('email')
                                <p class="field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label 
                                for="password"
                            >
                                Password
                            </label>

                            <div class="input-wrap">

                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m6 4H6a2 2 0 01-2-2v-6a2 2 0 012-2h12a2 2 0 012 2v6a2 2 0 01-2 2zm-2-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                </svg>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    placeholder="Masukkan password"
                                    class="login-input password-input @error('password') input-error @enderror"
                                >

                                <button
                                    type="button"
                                    id="password-toggle"
                                    class="password-toggle-btn"
                                    aria-label="Tampilkan password"
                                    aria-pressed="false"
                                >
                                    <svg class="icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="icon-eye-off" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M6.223 6.223A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.043 5.197M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>

                            @error('password')
                                <p class="field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember -->
                        <div class="login-actions">

                            <label class="remember-wrap">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                >

                                <span>
                                    Ingat Saya
                                </span>
                            </label>

                            <a 
                                href="#"
                                class="login-link"
                            >
                                Lupa Password?
                            </a>
                        </div>

                        <!-- Submit -->
                        <div>
                            <button
                                type="submit"
                                class="login-submit"
                            >
                                Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                © {{ date('Y') }} {{ config('app.name') }}
            </div>
        </div>
    </div>

    <script>
        (function () {
            var input = document.getElementById('password');
            var toggle = document.getElementById('password-toggle');
            if (!input || !toggle) {
                return;
            }

            toggle.addEventListener('click', function () {
                var visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                toggle.classList.toggle('is-visible', !visible);
                toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
                toggle.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
                input.focus();
            });
        })();
    </script>
</body>
</html>