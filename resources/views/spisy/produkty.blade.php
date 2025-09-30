<x-layout title="Produkty dla regionu {{ $spis->region->name }}">
<div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50"
     x-data="{ contextOpen: false, contextX: 0, contextY: 0, selectedId: null }"
     @click.window="contextOpen = false">

	@if(session('success'))
            <p class="mb-6 text-green-800 font-semibold">{{ session('success') }}</p>
        @endif

    <!-- Formularz filtrowania -->
    <form method="GET" action="{{ route('spisy.produkty', $spis->id) }}" class="mb-6 flex gap-4 items-end flex-wrap" id="filterForm">
        @php
            $dateFromVal = isset($dateFrom) ? $dateFrom->format('Y-m-d') : '';
            $dateToVal   = isset($dateTo) ? $dateTo->format('Y-m-d') : '';
        @endphp
        <div>
            <label class="block text-sky-700 font-medium">Zakres dat</label>
            <div class="input-with-icon">
                <span class="icon">📅</span>
                <input type="text" id="daterange"
                    data-server-from="{{ $dateFromVal }}"
                    data-server-to="{{ $dateToVal }}"
                    class="p-2 rounded bg-slate-800 text-white border border-cyan-600 cursor-pointer"
                    autocomplete="off"
                    value="{{ $dateFromVal && $dateToVal ? \Carbon\Carbon::parse($dateFromVal)->format('m/d/Y').' - '.\Carbon\Carbon::parse($dateToVal)->format('m/d/Y') : '' }}">
            </div>
            <input type="hidden" name="date_from" class="date-from" value="{{ $dateFromVal }}">
            <input type="hidden" name="date_to" class="date-to" value="{{ $dateToVal }}">
        </div>

        <button type="submit"
                class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
            Filtruj
        </button>

        <a href="{{ route('spisy.produkty', $spis->id) }}"
           class="px-4 py-2 bg-slate-800 hover:bg-slate-600 rounded text-white font-bold shadow-md">
            Wyczyść
        </a>
    </form>

    <!-- Przycisk dodania wyfiltrowanych -->
    <form method="POST" action="{{ route('spisy.produkty.add', $spis->id) }}" class="mb-8" id="addForm">
        @csrf
        <input type="hidden" name="date_from" class="date-from" value="{{ $dateFromVal }}">
        <input type="hidden" name="date_to" class="date-to" value="{{ $dateToVal }}">
        <button type="submit"
                class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
            ⊂(◉‿◉)つ Dodaj wyfiltrowane produkty do spisu
        </button>
    </form>

        <!-- ================== TABELA: zeskanowane ================== -->
<div class="mb-8">
            <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1 ">
                Produkty zeskanowane dla regionu {{ $spis->region->name }}
            </h2>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-neutral-700 rounded-lg shadow-inner">
            <table class="min-w-full text-left text-gray-300 border-collapse">
                <thead class="sticky top-0 bg-neutral-900 text-sm text-white">
                    <tr>
                        <th class="p-2">Produkt</th>
                        <th class="p-2">Cena</th>
                        <th class="p-2">Jednostka</th>
                        <th class="p-2">Ilość</th>
                        <th class="p-2">Kod kreskowy</th>
                        <th class="p-2">Data skanu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @foreach($produkty as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="p-2">{{ $produkt->product->name ?? 'Brak nazwy' }}</td>
                            <td class="p-2">{{ optional($produkt->product->latestPrice)->price ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->product->unit->name ?? '-' }}</td>
                            <td class="p-2">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->scanned_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

            <!-- Paginacja dla tabeli 1 -->
            <div class="mt-2">
                {{ $produkty->links() }}
            </div>
        </div>


      
    
</x-layout>
