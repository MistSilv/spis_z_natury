<x-layout title="Produkty dla regionu {{ $spis->region->name }}">
<div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

    @if(session('success'))
        <p class="mb-6 text-green-800 font-semibold">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <div class="text-red-500">{{ session('error') }}</div>
    @endif


    <!-- Formularz filtrowania i zapisu do bufora -->
    <div class="mb-6 flex gap-4 items-end flex-wrap">
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
    <div class="mb-8 mt-10"
         x-data="{ contextOpenFilter:false, contextXFilter:0, contextYFilter:0, selectedIdFilter:null }"
         @click.window="contextOpenFilter = false">

        <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1">
            Produkty wyfiltrowane dla regionu {{ $spis->region->name }}
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
                        <th class="p-2">Data skanu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse($produkty as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition cursor-context-menu"
                            @contextmenu.prevent="
                                contextOpenFilter = true;
                                contextXFilter = $event.pageX;
                                contextYFilter = $event.pageY;
                                selectedIdFilter = {{ $produkt->id }}
                            ">
                            <td class="p-2">{{ $produkt->name ?? 'Brak nazwy' }}</td>
                            <td class="p-2 font-semibold text-teal-600">{{ number_format($produkt->price, 2, '.', '') }}</td>
                            <td class="p-2">{{ $produkt->unit ?? '-' }}</td>
                            <td class="p-2 font-semibold text-emerald-600">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
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

        <div class="mt-2">
            {{ $produkty->links() }}
        </div>

       <!-- KONTEKSTOWE MENU dla produktów wyfiltrowanych -->
<template x-if="contextOpenFilter">
    <div class="absolute z-50"
         :style="`top:${contextYFilter}px; left:${contextXFilter}px`"
         @click.stop
         x-data="{ showEdit:false, showAddByEan:false }">
        <!-- Menu główne -->
        <div class="flex flex-col gap-1 bg-gray-800 border border-gray-600 p-2 w-44 shadow-lg">
            <button @click="showEdit = true"
                    class="w-full px-3 py-1 text-sm font-medium text-gray-100 bg-gray-700 hover:bg-cyan-600 hover:text-white transition duration-200">
                Zmień ilość
            </button>

            <button @click="showAddByEan = true"
                    class="w-full px-3 py-1 text-sm font-medium text-gray-100 bg-gray-700 hover:bg-blue-600 hover:text-white transition duration-200">
                Dodaj po EAN
            </button>

            <form method="POST"
                  :action="`/spisy/{{ $spis->id }}/produkty-filtr/${selectedIdFilter}`"
                  @click.stop>
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full px-3 py-1 text-sm font-medium text-gray-100 bg-gray-700 hover:bg-red-600 hover:text-white transition duration-200">
                    Usuń produkt
                </button>
            </form>
        </div>

        <div x-show="showEdit"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform translate-x-1"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-1"
             class="absolute left-full top-0 ml-2 bg-gray-700 border border-gray-600 shadow-lg p-2 w-44 flex flex-col gap-1">

            <form method="POST"
                  :action="`/spisy/{{ $spis->id }}/produkty-filtr/${selectedIdFilter}/quantity`"
                  class="flex items-center gap-1"
                  @click.stop>
                @csrf
                @method('PATCH')
                <input type="number" name="quantity" step="0.01"
                       placeholder="Ilość"
                       class="w-16 px-2 py-1 border border-gray-600 bg-gray-600 text-white text-sm focus:outline-none focus:ring-1 focus:ring-cyan-400" />
                <button type="submit"
                        class="px-2 py-1 bg-green-600 hover:bg-green-500 text-white text-sm transition duration-200">
                    Zapisz
                </button>
            </form>

            <button @click="showEdit = false"
                    class="text-sm text-red-400 hover:text-red-200 transition duration-200">
                Zamknij
            </button>
        </div>

        <div x-show="showAddByEan"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform translate-x-1"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-1"
             class="absolute left-full top-0 ml-2 bg-gray-700 border border-gray-600 shadow-lg p-2 w-56 flex flex-col gap-2">

            <form method="POST"
                  action="{{ route('produkty-filtr.storeByEan', $spis->id) }}"
                  class="flex flex-col gap-2"
                  @click.stop>
                @csrf
                <input type="text" name="ean" maxlength="50"
                       placeholder="Kod EAN"
                       class="w-full px-2 py-1 border border-gray-600 bg-gray-600 text-white text-sm focus:outline-none focus:ring-1 focus:ring-cyan-400" />

                <input type="number" name="quantity" step="0.01" min="0.01"
                       placeholder="Ilość"
                       class="w-full px-2 py-1 border border-gray-600 bg-gray-600 text-white text-sm focus:outline-none focus:ring-1 focus:ring-cyan-400" />

                <button type="submit"
                        class="px-2 py-1 bg-green-600 hover:bg-green-500 text-white text-sm transition duration-200">
                    Dodaj
                </button>
            </form>

            <button @click="showAddByEan = false"
                    class="text-sm text-red-400 hover:text-red-200 transition duration-200">
                Zamknij
            </button>
        </div>
    </div>
</template>






    <!-- Przycisk dodania wyfiltrowanych -->
    <form method="POST" action="{{ route('spisy.produkty.add', $spis->id) }}" class="mb-8" id="addForm">
        @csrf
        <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" class="date-to" value="{{ request('date_to') }}">
        <button type="submit"
                class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
            Dodaj wyfiltrowane produkty
        </button>
    </form>


    <!-- ================== TABELA: tymczasowe ================== -->
    <div class="mb-8 mt-10"
         x-data="{ contextOpenTemp:false, contextXTemp:0, contextYTemp:0, selectedIdTemp:null }"
         @click.window="contextOpenTemp = false">

        <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1">
            Produkty tymczasowe 
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
                                contextOpenTemp = true;
                                contextXTemp = $event.pageX;
                                contextYTemp = $event.pageY;
                                selectedIdTemp = {{ $produkt->id }}
                            ">
                            <td class="p-2 font-medium">{{ $produkt->name }}</td>
                            <td class="p-2 font-semibold text-teal-600">{{ number_format($produkt->price, 2) }}</td>
                            <td class="p-2">{{ $produkt->unit }}</td>
                            <td class="p-2 font-semibold text-emerald-600">{{ number_format($produkt->quantity, 2, '.', '') }}</td>
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

        <div class="mt-2">
            {{ $produktySpisu->links() }}
        </div>

        <!-- KONTEKSTOWE MENU dla produktów tymczasowych -->
<template x-if="contextOpenTemp">
    <div class="absolute bg-gray-900 border border-gray-700 shadow-2xl p-3 z-50 w-60"
         :style="`top:${contextYTemp}px; left:${contextXTemp}px`"
         @click.stop>
        <form method="POST"
              :action="`/spisy/{{ $spis->id }}/produkty-temp/${selectedIdTemp}/update`"
              class="flex items-center gap-2">
            @csrf
            <input type="number" name="price" step="0.01"
                   placeholder="Nowa cena"
                   class="w-24 px-3 py-1 bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500" />
            <button type="submit"
                    class="px-3 py-1 bg-green-700 hover:bg-green-500 text-white text-sm transition">
                Zapisz
            </button>
        </form>
    </div>
</template>



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
