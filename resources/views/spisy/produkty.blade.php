<x-layout title="Produkty dla regionu {{ $spis->region->name }}">
<div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50"
     x-data="{ contextOpen: false, contextX: 0, contextY: 0, selectedId: null }"
     @click.window="contextOpen = false">

	@if(session('success'))
            <p class="mb-6 text-green-800 font-semibold">{{ session('success') }}</p>
        @endif

    <!-- Formularz filtrowania i zapisu do bufora -->
<div class="mb-6 flex gap-4 items-end flex-wrap">
    <!-- FORMULARZ FILTROWANIA -->
    <form id="filterForm"
          method="POST"
          action="{{ route('spisy.produkty.filter', $spis->id) }}"
          class="flex gap-4 items-end">
        @csrf

        <div>
            <label class="block text-sky-700 font-medium">Zakres dat</label>
            <div class="input-with-icon">
                <input type="text"
                       id="daterange"
                       data-server-from="{{ request('date_from') }}"
                       data-server-to="{{ request('date_to') }}"
                       class="p-2 rounded bg-slate-800 text-white border border-cyan-600 cursor-pointer"
                       autocomplete="off"
                       value="{{ request('date_from') && request('date_to')
                            ? \Carbon\Carbon::parse(request('date_from'))->format('m/d/Y').' - '. \Carbon\Carbon::parse(request('date_to'))->format('m/d/Y')
                            : '' }}">
            </div>

            <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to"   class="date-to"   value="{{ request('date_to') }}">
        </div>

        <button type="submit"
                class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
            Filtruj i zapisz tymczasowo
        </button>
    </form>

    <!-- ODDZIELNY FORMULARZ DO CZYSZCZENIA (DELETE) -->
    <form id="clearForm"
          method="POST"
          action="{{ route('spisy.produkty.clear', $spis->id) }}">
        @csrf
        @method('DELETE')
        <button type="submit"
                onclick="return confirm('Czy na pewno chcesz wyczyścić bufor tymczasowy?')"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-600 rounded text-white font-bold shadow-md">
            Wyczyść
        </button>
    </form>
</div>





    

       <!-- ================== TABELA: produkty z filtra ================== -->
<div class="mb-8">
    <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1 ">
        Produkty wyfiltrowane dla regionu {{ $spis->region->name }}
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
                @forelse($produkty as $produkt)
                    <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                        <td class="p-2">{{ $produkt->name ?? 'Brak nazwy' }}</td>
                        <td class="p-2">{{ number_format($produkt->price, 2, '.', '') }}</td>
                        <td class="p-2">{{ $produkt->unit ?? '-' }}</td>
                        <td class="p-2">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
                        <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                        <td class="p-2">{{ $produkt->scanned_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-2 text-center text-gray-400">
                            Brak produktów do wyświetlenia
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacja dla tabeli 1 -->
    <div class="mt-2">
        {{ $produkty->links() }}
    </div>
</div>

    
    <!-- Przycisk dodania wyfiltrowanych -->
        <form method="POST" action="{{ route('spisy.produkty.add', $spis->id) }}" class="mb-8" id="addForm">
            @csrf
            <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" class="date-to" value="{{ request('date_to') }}">
            <button type="submit"
                    class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
                ⊂(◉‿◉)つ Dodaj wyfiltrowane produkty do spisu
            </button>
        </form>

    <!-- ================== TABELA: tymczasowe ================== -->
    <div class="mb-8 mt-10">

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
                    @forelse($produktySpisu as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition cursor-context-menu"
                            @contextmenu.prevent="
                                contextOpen = true;
                                contextX = $event.pageX;
                                contextY = $event.pageY;
                                selectedId = {{ $produkt->id }}
                            ">
                            <td class="p-2 font-medium">{{ $produkt->name }}</td>
                            <td class="p-2">{{ number_format($produkt->price, 2) }}</td>
                            <td class="p-2">{{ $produkt->unit }}</td>
                            <td class="p-2">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-2 text-center text-gray-400">
                                Brak produktów w tabeli tymczasowej
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="mt-2">
            {{ $produktySpisu->links() }}
        </div>

        <!-- Menu kontekstowe -->
        <template x-if="contextOpen">
            <div class="absolute bg-neutral-800 border border-neutral-600 rounded-lg shadow-lg p-3 z-50"
                 :style="`top:${contextY}px; left:${contextX}px`"
                 @click.stop>
                <form method="POST"
                      :action="`/spisy/{{ $spis->id }}/produkty-temp/${selectedId}/update`"
                      class="flex items-center gap-2">
                    @csrf
                    <input type="number" name="price" step="0.01"
                           placeholder="Nowa cena"
                           class="w-24 px-2 py-1 rounded bg-slate-900 border border-cyan-600 text-white text-sm" />
                    <button type="submit"
                            class="px-2 py-1 bg-green-700 hover:bg-green-500 rounded text-white text-xs">
                        💾 Zapisz
                    </button>
                </form>
            </div>
        </template>
    </div>


    <form method="POST" action="{{ route('spisy.produkty.finalize', $spis->id) }}">
        @csrf
        <button type="submit"
            class="px-4 py-2 bg-green-700 hover:bg-green-500 rounded text-white font-bold shadow-md">
            Zatwierdź spis i przejdź do podsumowania
        </button>
    </form>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="{{ asset('css/daterangepicker-dark.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('js/daterangepicker-init.js') }}"></script>
</x-layout>