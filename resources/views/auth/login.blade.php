<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-black flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gradient-to-br from-black via-slate-950 to-slate-900 rounded-lg shadow-lg p-8 border border-teal-700/50">
        <h1 class="text-2xl font-bold mb-6 text-center text-teal-300 drop-shadow">Logowanie</h1>
        
        @if($errors->any())
            <div class="mb-4 bg-teal-600/10 text-teal-400 border border-teal-700/50 rounded p-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-teal-400 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-teal-700/50 rounded focus:outline-none focus:ring-2 focus:ring-teal-600/50 placeholder:text-slate-400" />
            </div>
            <div>
                <label class="block text-teal-400 mb-1" for="password">Hasło</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-teal-700/50 rounded focus:outline-none focus:ring-2 focus:ring-teal-600/50 placeholder:text-slate-400" />
            </div>
            <button type="submit"
                class="w-full bg-teal-800 hover:bg-teal-600 text-slate-100 py-2 rounded font-semibold shadow-md border border-teal-800/50 transition">
                Zaloguj
            </button>
        </form>
    </div>
</body>
</html>
