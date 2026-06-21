<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="mb-2 text-xl font-bold text-gray-900">Verifikasi Two-Factor</h1>
            <p class="mb-6 text-sm text-gray-600">Masukkan kode 6 digit dari aplikasi authenticator Anda.</p>

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-gray-700">Kode OTP</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required autofocus
                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Verifikasi
                </button>
            </form>
        </div>
    </div>
</body>
</html>
