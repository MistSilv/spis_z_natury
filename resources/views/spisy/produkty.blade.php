<x-layout title="Produkty dla regionu {{ $spis->region->name }}">
    {{-- Daterangepicker CSS (CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

        @if(session('success'))
            <p class="mb-6 text-green-800 font-semibold">{{ session('success') }}</p>
        @endif

        <!-- Formularz filtrowania -->
        <form method="GET" action="{{ route('spisy.produkty', $spis->id) }}" class="mb-6 flex gap-4 items-end flex-wrap" id="filterForm">
            <div>
                <label class="block text-sky-700 font-medium">Zakres dat</label>

                <div class="input-with-icon">
                    <span class="icon">📅</span>

                    {{-- Widoczny input (daterangepicker wykryje go po #daterange) --}}
                    <input type="text" id="daterange"
                        data-server-from="{{ request('date_from') }}"
                        data-server-to="{{ request('date_to') }}"
                        class="p-2 rounded bg-slate-800 text-white border border-cyan-600 cursor-pointer"
                        autocomplete="off"
                        value="{{ request('date_from') && request('date_to') ? \Carbon\Carbon::parse(request('date_from'))->format('m/d/Y').' - '. \Carbon\Carbon::parse(request('date_to'))->format('m/d/Y') : '' }}">

                </div>

                {{-- Ukryte pola używane przez backend (format YYYY-MM-DD) --}}
                <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to"   class="date-to"   value="{{ request('date_to') }}">
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
            {{-- Ukryte pola w formularzu dodawania — te same klasy, JS zaktualizuje obydwa formularze --}}
            <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to"   class="date-to"   value="{{ request('date_to') }}">

            <button type="submit"
                    class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
                ⊂(◉‿◉)つ Dodaj wyfiltrowane produkty do spisu
            </button>
        </form>

        <!-- ================== TABELKA 1: produkty zeskanowane ================== -->
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

            <!-- Paginacja dla tabeli 1 -->
            <div class="mt-2">
                {{ $produkty->links() }}
            </div>
        </div>

        <!-- ================== TABELKA 2: produkty dodane do spisu ================== -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-sky-700 mb-2 border-b border-cyan-500 pb-1 ">
                Produkty w tym spisie
            </h2>

            <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-neutral-700 rounded-lg shadow-inner">
                <table class="min-w-full text-left text-gray-300border-collapse">
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
                        @foreach($produktySpisu as $produkt)
                            <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                                <td class="p-2">{{ $produkt->name }}</td>
                                <td class="p-2">{{ $produkt->price }}</td>
                                <td class="p-2">{{ $produkt->unit }}</td>
                                <td class="p-2">{{ $produkt->quantity }}</td>
                                <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                                <td class="p-2">{{ $produkt->user->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginacja dla tabeli 2 -->
            <div class="mt-2">
                {{ $produktySpisu->links() }}
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('spisy.podsumowanie', $spis->id) }}"
               class="px-6 py-3 bg-sky-800 hover:bg-sky-600 text-white font-bold rounded shadow-md transition-colors">
                ヽ༼ ຈل͜ຈ༼ ▀̿̿Ĺ̯̿̿▀̿ ̿༽Ɵ͆ل͜Ɵ͆ ༽ﾉ
            </a>
        </div>

    </div>

<link rel="stylesheet" href="{{ asset('css/daterangepicker-dark.css') }}">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('js/daterangepicker-init.js') }}"></script>

</x-layout>
