<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Inwentaryzacyjna' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-black text-slate-100 min-h-screen">

<header>
    <nav class="bg-black border-b border-emerald-600/50">
        <div class="container mx-auto flex items-center justify-between px-4 py-3">
            @auth
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-emerald-400">Zalogowany jako:</span>
                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                </div>
            @endauth

            <div class="sm:hidden">
                <button id="menu-toggle"
                        class="text-slate-100 hover:text-emerald-400 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <div class="hidden sm:flex space-x-4">
                <a href="{{ route('welcome') }}" class="px-4 py-2 rounded bg-green-900 hover:bg-green-700 text-white font-semibold transition">
                    Strona Główna
                </a>
        
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded bg-red-800 hover:bg-red-600 text-white font-semibold transition">
                        Wyloguj
                    </button>
                </form>
            </div>
        </div>

        <div id="mobile-menu" class="hidden sm:hidden px-4 pb-4 space-y-2">
            <a href="{{ route('welcome') }}" class="block px-4 py-2 rounded bg-emerald-800 hover:bg-emerald-600 text-white font-semibold transition">
                Strona Główna
            </a>
       
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 rounded bg-red-800 hover:bg-red-600 text-white font-semibold transition">
                    Wyloguj
                </button>
            </form>
        </div>
    </nav>
</header>

<main class="container mx-auto p-4 sm:p-6">
    {{ $slot }}
</main>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>

</body>
</html>