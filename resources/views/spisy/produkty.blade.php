<x-layout title="Produkty dla regionu {{ $spis->region->name }}">
    <div class="max-w-4xl mx-auto p-6 bg-slate-900 rounded-xl shadow-lg border border-teal-700/50">
        <h1 class="text-2xl font-bold text-teal-300 mb-4">Produkty dla regionu {{ $spis->region->name }}</h1>

        @if(session('success'))
            <p class="mb-4 text-green-400">{{ session('success') }}</p>
        @endif

        <table class="w-full text-left text-white">
            <thead>
                <tr class="border-b border-teal-700">
                    <th class="p-2">Produkt</th>
                    <th class="p-2">Cena</th>
                    <th class="p-2">Jednostka</th>
                    <th class="p-2">Ilość</th>
                    <th class="p-2">Kod kreskowy</th>
                    <th class="p-2">Data skanu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produkty as $produkt)
                    <tr class="border-b border-teal-800">
                        <td class="p-2">{{ $produkt->product->name ?? 'Brak nazwy' }}</td>
                        <td class="p-2">{{ $produkt->product->price ?? '-' }}</td>
                        <td class="p-2">{{ $produkt->product->unit->name ?? '-' }}</td>
                        <td class="p-2">{{ $produkt->quantity }}</td>
                        <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                        <td class="p-2">{{ $produkt->scanned_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
