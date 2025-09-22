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
    <div class="w-full max-w-2xl bg-gradient-to-br from-black via-slate-950 to-slate-900 rounded-lg shadow-lg p-8 border border-cyan-700/50 text-center">
        
        <h1 class="text-2xl font-bold mb-6 text-sky-900 drop-shadow">
            Witaj, {{ Auth::user()->name }}
        </h1>

        <div class="flex justify-center gap-6">
            <button onclick="window.location='{{ route('produkt_skany.index') }}'"
                class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-2 px-6 rounded-2xl shadow-md border border-cyan-800/50 transition">
                Produkty 📝
            </button>

            <button onclick="window.location='{{ route('spisy.index') }}'"
                class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-2 px-6 rounded-2xl shadow-md border border-cyan-800/50 transition">
                (ㆆ _ ㆆ)
            </button>

            <button onclick="window.location='{{ route('products.index') }}'"
                class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-2 px-6 rounded-2xl shadow-md border border-cyan-800/50 transition">
                ( •̀ ω •́ )
            </button>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="bg-red-800 hover:bg-red-600 text-slate-100 font-semibold py-2 px-6 rounded-2xl shadow-md border border-red-800/50 transition">
                    Wyloguj
                </button>
            </form>
        </div>
    </div>
</body>
</html>
