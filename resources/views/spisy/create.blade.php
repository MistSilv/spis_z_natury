<x-layout title="Tworzenie spisu">
    <div class="max-w-xl mx-auto bg-slate-900 p-6 rounded-xl shadow-lg border border-teal-700/50">
        <h1 class="text-2xl font-bold text-teal-400 mb-4">Nowy spis</h1>

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

            <button type="submit" class="bg-teal-800 hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded">
                Utwórz spis
            </button>
        </form
    </div>
</x-layout>
