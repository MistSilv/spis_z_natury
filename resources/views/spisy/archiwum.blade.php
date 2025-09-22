<x-layout title="Archiwum Spisów">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-teal-700/50">
        <h1 class="text-2xl font-bold text-sky-700 mb-6">Archiwum Spisów</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($spisy as $index => $spis)
                <div class="
                    rounded-xl shadow-lg p-4 hover:shadow-xl transition
                    @if($index % 2 === 0) bg-gradient-to-r from-zinc-800 via-zinc-700 to-zinc-800
                    @else bg-gradient-to-r from-slate-900 via-slate-700 to-slate-900
                    @endif
                    text-gray-200">
                    <h2 class="text-lg font-bold text-sky-700 mb-2">{{ $spis->name ?? '-' }}</h2>
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
