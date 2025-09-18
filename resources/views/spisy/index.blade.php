<x-layout title="Spisy z natury">
    <div class="flex justify-center bg-black p-4">
        <div class="inline-block bg-gradient-to-br from-black via-slate-950 to-slate-900 rounded-lg shadow-lg p-8 border border-teal-700/50 text-center">

            <h1 class="text-2xl font-bold mb-6 text-teal-300 drop-shadow">
                Spisy z natury – Wybierz region
            </h1>

            <p class="text-gray-300 mb-6">
                Kliknij w region, aby utworzyć nowy spis i zobaczyć produkty przypisane do tego regionu.
            </p>

            <div class="flex justify-center flex-wrap gap-6">
                <a href="{{ route('spisy.create', ['region_id' => 1]) }}"
                   class="bg-teal-700 hover:bg-teal-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Magazyn
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 2]) }}"
                   class="bg-teal-700 hover:bg-teal-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Sklep
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 3]) }}"
                   class="bg-teal-700 hover:bg-teal-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Garmaż
                </a>
                <a href="{{ route('spisy.create', ['region_id' => 4]) }}"
                   class="bg-teal-700 hover:bg-teal-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                   Piekarnia
                </a>
            </div>

        </div>
    </div>
</x-layout>
