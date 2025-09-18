<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel startowy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-black flex items-center justify-center min-h-screen">
    <div class="w-full max-w-2xl bg-gradient-to-br from-black via-slate-950 to-emerald-950 rounded-lg shadow-lg p-8 border border-emerald-600/50 text-center">
        
        <h1 class="text-2xl font-bold mb-6 text-emerald-400 drop-shadow">
            Witaj, {{ Auth::user()->name }}
        </h1>

        <div class="flex justify-center gap-6">
            <a href="{{ route('produkt_skany.index') }}" 
                class="bg-emerald-500 hover:bg-emerald-800 text-slate-950 font-semibold py-2 px-6 rounded-2xl shadow-lg transition">
                Produkty
            </a>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="bg-emerald-500 hover:bg-emerald-800 text-slate-950 font-semibold py-2 px-6 rounded-2xl shadow-lg transition">
                    Wyloguj
                </button>
            </form>
        </div>
    </div>
</body>
</html>
