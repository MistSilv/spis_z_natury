<x-layout title="Tworzenie spisu">
    <div class="max-w-xl mx-auto bg-slate-900 p-6 rounded-xl shadow-lg border border-cyan-700/50">

        <!-- pomidor -->
      @php
    $unfinished = \App\Models\SpisProduktyTmp::where('user_id', auth()->id())
        ->where('region_id', $selectedRegion)
        ->first();
@endphp

@if($unfinished)
    <div class="mb-6 p-4 bg-slate-800 border border-sky-600 rounded-lg shadow-md">
        <p class="text-sky-300 font-semibold mb-3">
            ༼ つ ◕_◕ ༽つ Masz niedokończony spis dla regionu <b>{{ $unfinished->region->name ?? '---' }}</b>.  
            Chcesz przywrócić dane czy zacząć od zera? 
        </p>
        <div class="flex gap-4">
            <!-- Przywrócenie = wejście do spisu -->
            <a href="{{ route('spisy.produkty', $unfinished->spis_id) }}"
               class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
                🔄 Przywróć dane
            </a>

            <!-- Reset = czyszczenie TMP -->
            <form method="POST" action="{{ route('spisy.reset', $unfinished->spis_id) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-red-800 hover:bg-red-600 rounded text-white font-bold shadow-md">
                    ❌ Zacznij od zera
                </button>
            </form>
        </div>
    </div>
@endif



        <h1 class="text-2xl font-bold text-sky-700 mb-4">Nowy spis</h1>

        <form action="{{ route('spisy.store') }}" method="POST">
            @csrf

            <label class="block mb-2 text-gray-300">Nazwa spisu:</label>
            <input type="text" name="name" class="w-full mb-4 p-2 rounded bg-slate-800 text-white" required>

            <label class="block mb-2 text-gray-300">Opis:</label>
            <textarea name="description" class="w-full mb-4 p-2 rounded bg-slate-800 text-white"></textarea>

            <label class="block mb-2 text-gray-300">Region:</label>
            <select name="region_id" class="w-full mb-4 p-2 rounded bg-slate-800 text-white" required>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ ($selectedRegion == $region->id) ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-sky-800 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded">
                Utwórz spis
            </button>
        </form
    </div>
</x-layout>
