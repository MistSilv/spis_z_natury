<x-layout title="Spisy z natury">
    <div class="flex justify-center bg-black p-4">
        <div class="inline-block bg-gradient-to-br from-black via-slate-950 to-slate-900 rounded-lg shadow-lg p-8 border border-cyan-700/50 text-center">

            <h1 class="text-2xl font-bold mb-6 text-sky-700 drop-shadow">
                Spisy z natury – Wybierz region
            </h1>

            <p class="text-gray-300 mb-6">
                Kliknij w region, aby utworzyć nowy spis i zobaczyć produkty przypisane do tego regionu.
            </p>

            <div class="flex justify-center flex-wrap gap-6 mb-4">
                <a href="{{ route('spisy.create', ['region_id' => 1]) }}"
                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Magazyn
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 2]) }}"
                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Sklep
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 3]) }}"
                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Garmaż
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 4]) }}"
                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Piekarnia
                </a>
            </div>

            <div class="border-t border-cyan-700/50 pt-6">
                <a href="{{ route('spisy.archiwum') }}"
                   class="inline-flex items-center bg-gray-800 hover:bg-gray-600 text-slate-200 font-semibold py-3 px-6 rounded-2xl shadow-md border border-slate-700/50 transition">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                   </svg>
                  Archiwum Spisów
                </a>
            </div>

        </div>
    </div>
</x-layout>
