<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-black flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gradient-to-br from-black via-slate-950 to-emerald-950 rounded-lg shadow-lg p-8 border border-emerald-600/50">
        <h1 class="text-2xl font-bold mb-6 text-center text-emerald-400 drop-shadow">Logowanie</h1>
        @if($errors->any())
            <div class="mb-4 bg-emerald-600/15 text-emerald-300 border border-emerald-600/50 rounded p-3">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-emerald-300 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
            </div>
            <div>
                <label class="block text-emerald-300 mb-1" for="password">Hasło</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
            </div>
            <button type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-800 text-slate-950 py-2 rounded transition font-semibold shadow-lg">Zaloguj</button>
        </form>
    </div>
</body>
</html>