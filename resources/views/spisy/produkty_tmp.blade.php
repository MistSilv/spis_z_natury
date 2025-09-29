<x-layout title="Produkty tymczasowe dla spisu {{ $spis->name }}">
<div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

    @if(session('success'))
        <p class="mb-6 text-green-800 font-semibold">{{ session('success') }}</p>
    @endif

    @if(session('warning'))
        <p class="mb-6 text-yellow-500 font-semibold">{{ session('warning') }}</p>
    @endif

    <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1">
        Produkty tymczasowe (do edycji)
    </h2>
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-neutral-700 rounded-lg shadow-inner">
        <table class="min-w-full text-left text-gray-300 border-collapse">
            <thead class="sticky top-0 bg-neutral-900 text-sm text-white z-10">
                <tr>
                    <th class="p-2">Produkt</th>
                    <th class="p-2">Cena</th>
                    <th class="p-2">Jednostka</th>
                    <th class="p-2">Ilość</th>
                    <th class="p-2">Kod kreskowy</th>
                    <th class="p-2">Dodane przez</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-700">
                @forelse($produktyTmp as $produkt)
                    <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                        <td class="p-2 font-medium">{{ $produkt->name }}</td>
                        <td class="p-2">{{ number_format($produkt->price, 2) }}</td>
                        <td class="p-2">{{ $produkt->unit }}</td>
                        <td class="p-2">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
                        <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                        <td class="p-2">{{ $produkt->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-2 text-center text-gray-400">Brak produktów w tabeli tymczasowej</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-2">
        <a href="{{ route('spisy.produkty', $spis->id) }}"
           class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
            Wróć do listy produktów zeskanowanych
        </a>
    </div>

</div>
</x-layout>
