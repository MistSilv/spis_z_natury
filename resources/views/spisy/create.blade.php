<x-layout title="Tworzenie spisu">
    <div class="max-w-xl mx-auto bg-slate-900 p-6 rounded-xl shadow-lg border border-cyan-700/50">

        @php
            $unfinished = \App\Models\SpisProduktyTmp::where('user_id', auth()->id())
                ->where('region_id', $selectedRegion)
                ->first();
        @endphp

        @if($unfinished)
            <div class="mb-6 p-4 bg-slate-800 border border-sky-600 rounded-lg shadow-md">
                <p class="text-sky-300 font-semibold mb-3">
                    ༼ つ ◕_◕ ༽つ Masz niedokończony spis dla regionu 
                    <b>{{ $unfinished->region->name ?? '---' }}</b>.  
                    Chcesz przywrócić dane czy zacząć od zera? 
                </p>
                <div class="flex gap-4">
                    <a href="{{ route('spisy.produkty', $unfinished->spis_id) }}"
                       id="restore-link"
                       class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded text-white font-bold shadow-md">
                        Przywróć dane
                    </a>

                    <form method="POST" action="{{ route('spisy.reset', $unfinished->spis_id) }}">
                        @csrf
                        <button type="submit"
                                id="reset-btn"
                                class="px-4 py-2 bg-red-800 hover:bg-red-600 rounded text-white font-bold shadow-md">
                            Zacznij od zera
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <h1 class="text-2xl font-bold text-sky-700 mb-4">Nowy spis</h1>

        <form action="{{ route('spisy.store') }}" method="POST">
            @csrf

            <label class="block mb-2 text-gray-300">Nazwa spisu:</label>
            <input 
                type="text" 
                name="name" 
                id="spis-name" 
                class="w-full mb-4 p-2 rounded bg-slate-800 text-white" 
                value="{{ $defaultName ?? '' }}" 
                required
            >

            <label class="block mb-2 text-gray-300">Opis:</label>
            <textarea 
                name="description" 
                class="w-full mb-4 p-2 rounded bg-slate-800 text-white"
            ></textarea>

            <label class="block mb-2 text-gray-300">Region:</label>
            <select 
                name="region_id" 
                id="region-select" 
                class="w-full mb-4 p-2 rounded bg-slate-800 text-white" 
                required
            >
                <option value="">-- wybierz region --</option>
                @foreach($regions as $region)
                    <option 
                        value="{{ $region->id }}" 
                        {{ ($selectedRegion == $region->id) ? 'selected' : '' }}
                    >
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>

            <button 
                type="submit" 
                class="bg-sky-800 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded"
            >
                Utwórz spis
            </button>
        </form>
    </div>

   <script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const restoreLink = document.getElementById("restore-link");
    const resetBtn = document.getElementById("reset-btn");
    const spisName = document.getElementById("spis-name");
    const regionSelect = document.getElementById("region-select");

    function checkName(e) {
        if (!spisName.value.trim()) {
            e.preventDefault();
            alert("Podaj nazwę spisu, zanim wykonasz tę akcję!");
            spisName.focus();
        }
    }

    if (restoreLink) restoreLink.addEventListener("click", checkName);
    if (resetBtn) resetBtn.addEventListener("click", checkName);

    const spisyCount = @json($spisyCount);
    const currentYear = "{{ $currentYear }}";
    const suffix = "{{ $suffix }}";

    function updateName() {
        const regionId = regionSelect.value;
        if (!regionId) {
            spisName.value = '';
            return;
        }

        const count = spisyCount[regionId] ?? 0;
        const nextNumber = count + 1;
        // 👇 Zmienione — bez zer wiodących
        spisName.value = nextNumber + '/' + currentYear + '/' + suffix;
    }

    regionSelect.addEventListener('change', updateName);

    if (regionSelect.value) {
        updateName();
    }
});
</script>


</x-layout>
