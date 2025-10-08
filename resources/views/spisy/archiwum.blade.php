<x-layout title="Archiwum Spisów">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
        <h1 class="text-2xl font-bold text-sky-700 mb-6">Archiwum Spisów</h1>

        <form method="GET" action="{{ route('spisy.archiwum') }}" class="mb-6 flex flex-wrap gap-4 items-end">
    <div>
        <label class="block text-sm text-gray-400">Zakres dat:</label>

        <input type="text" id="daterange"
            data-server-from="{{ request('date_from') }}"
            data-server-to="{{ request('date_to') }}"
            class="p-2 rounded bg-slate-800 text-white border border-cyan-600 cursor-pointer"
            autocomplete="off"
            value="{{ request('date_from') && request('date_to') ? \Carbon\Carbon::parse(request('date_from'))->format('m/d/Y').' - '.\Carbon\Carbon::parse(request('date_to'))->format('m/d/Y') : '' }}">

        <input type="hidden" name="date_from" class="date-from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" class="date-to" value="{{ request('date_to') }}">
    </div>

    <div>
        <label class="block text-sm text-gray-400">Region:</label>
        <select name="region_id" class="rounded p-2 bg-slate-800 text-white">
            <option value="">-- Wszystkie --</option>
            @foreach($regions as $region)
                <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex items-end gap-2">
        <button type="submit" class="bg-sky-800 hover:bg-sky-600 px-4 py-2 rounded text-white">Filtruj</button>
        <a href="{{ route('spisy.archiwum') }}" class="bg-slate-800 hover:bg-slate-600 px-4 py-2 rounded text-white">Wyczyść</a>
    </div>
</form>


        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($spisy as $index => $spis)
                <div class="rounded-xl shadow-lg p-4 hover:shadow-xl transition text-gray-200
                    @if($index % 2 === 0)
                        bg-[radial-gradient(circle_at_70%_90%,_rgba(16,185,129,0.35)_0%,_rgba(6,78,59,0.1)_45%,_rgba(24,24,27,1)_90%)]
                    @else
                        bg-[radial-gradient(circle_at_70%_90%,_rgba(244,63,94,0.35)_0%,_rgba(136,19,55,0.1)_45%,_rgba(24,24,27,1)_90%)]
                    @endif">
                    <h2 class="text-[20px] font-bold text-sky-700 mb-2">{{ $spis->name ?? '-' }}</h2>
                    <p><strong>Region:</strong> {{ $spis->region->name ?? '-' }}</p>
                    <p><strong>Dodany przez:</strong> {{ $spis->user->name ?? '-' }}</p>
                    <p><strong>Data:</strong> {{ $spis->created_at ? $spis->created_at->format('d.m.Y H:i') : '-' }}</p>
                    <div class="mt-3">
                        @if($spis->id)
                            <a href="{{ route('spisy.podsumowanie', $spis->id) }}"
                               class="border-2 border-gray-950 hover:bg-gray-950/60 text-white px-3 py-1 rounded">
                               Podsumowanie
                            </a>
                        @else
                            <span class="text-gray-400">Brak danych</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $spisy->links() }}
        </div>
    </div>
</x-layout>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="{{ asset('css/daterangepicker-dark.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('js/daterangepicker-init.js') }}"></script>

