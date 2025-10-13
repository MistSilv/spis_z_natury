<x-layout title="Dodaj nową fakturę">
    <div class="max-w-4xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

        <h1 class="text-2xl font-bold text-sky-700 mb-6">Dodaj nową fakturę</h1>

        <form id="fakturaForm" action="{{ route('faktury.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="number" class="block text-sm font-semibold text-sky-600 mb-1">Numer faktury</label>
                <input 
                    id="number" 
                    type="text" 
                    name="number" 
                    required
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
            </div>

            <div>
                <label for="region_id" class="block text-sm font-semibold text-sky-600 mb-1">Region</label>
                <select 
                    id="region_id" 
                    name="region_id" 
                    required
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
                    <option value="">-- Wybierz region --</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="data_wystawienia" class="block text-sm font-semibold text-sky-600 mb-1">Data wystawienia</label>
                <input 
                    id="data_wystawienia" 
                    type="date" 
                    name="data_wystawienia"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
                <p class="text-sm text-gray-500 mt-1">
                    Jeśli nie podasz, zostanie automatycznie użyta dzisiejsza data.
                </p>
            </div>

            <div>
                <label for="data_sprzedazy" class="block text-sm font-semibold text-sky-600 mb-1">Data sprzedaży (opcjonalnie)</label>
                <input 
                    id="data_sprzedazy" 
                    type="date" 
                    name="data_sprzedazy"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
                <p class="text-sm text-gray-500 mt-1">
                    Jeśli nie podasz, zostanie użyta data wystawienia.
                </p>
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-sky-600 mb-1">Notatki</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="3"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('faktury.index') }}" 
                   class="px-4 py-2 bg-neutral-800 text-gray-300 rounded-lg hover:bg-neutral-700 transition">
                   Anuluj
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-600 font-bold transition">
                    Zapisz fakturę
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('fakturaForm');
            const dataWystawienia = document.getElementById('data_wystawienia');
            const dataSprzedazy = document.getElementById('data_sprzedazy');

            form.addEventListener('submit', function() {
                const today = new Date().toISOString().split('T')[0];

                if (!dataWystawienia.value) {
                    dataWystawienia.value = today;
                }

                if (!dataSprzedazy.value) {
                    dataSprzedazy.value = dataWystawienia.value;
                }
            });
        });
    </script>
</x-layout>
