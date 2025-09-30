<x-layout title="Podsumowanie spisu {{ $spis->name }}">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

        <h1 class="text-2xl font-bold text-sky-700 mb-6">
            Podsumowanie spisu: {{ $spis->name }}
        </h1>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-neutral-700 rounded-lg shadow-inner mb-4">
            <table class="min-w-full text-left text-gray-300 border-collapse ">
                <thead class="sticky top-0 bg-neutral-900 text-sm text-white z-10">
                    <tr>
                        <th class="p-2">Produkt</th>
                        <th class="p-2">Cena</th>
                        <th class="p-2">Jednostka</th>
                        <th class="p-2">Ilość</th>
                        <th class="p-2">Kod kreskowy</th>
                        <th class="p-2">Dodane przez</th>
                        <th class="p-2">Wartość</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @foreach($produktySpisu as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="p-2 font-medium">{{ $produkt->name }}</td>
                            <td class="p-2">{{ number_format($produkt->price, 2) }}</td>
                            <td class="p-2">{{ $produkt->unit }}</td>
                            <td class="p-2">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->user->name ?? '-' }}</td>
                            <td class="p-2 font-semibold text-sky-700">
                                {{ number_format($produkt->price * $produkt->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="mt-4">
            {{ $produktySpisu->links() }}
        </div>

        <!-- Podsumowanie -->
        <div class="mt-6 text-left">
            <p class="text-lg text-sky-700 font-medium">
                Łącznie pozycji w spisie: <span class="text-white font-bold">{{ $totalItems }}</span>
            </p>
            <p class="text-lg font-bold text-sky-700 mt-1">
                Łączna wartość spisu: <span class="text-white">{{ number_format($totalValue, 2) }}</span>
            </p>
        </div>

    </div>
</x-layout>
