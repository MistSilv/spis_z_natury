<x-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Dodaj nową fakturę</h1>

        <form id="fakturaForm" action="{{ route('faktury.store') }}" method="POST">
            @csrf

            <!-- Numer faktury -->
            <div class="mb-4">
                <label for="number" class="block font-medium mb-1">Numer faktury</label>
                <input id="number" type="text" name="number" required
                    class="block mt-1 w-full border rounded p-2 text-black">
            </div>

            <!-- Data wystawienia -->
            <div class="mb-4">
                <label for="data_wystawienia" class="block font-medium mb-1">Data wystawienia</label>
                <input id="data_wystawienia" type="date" name="data_wystawienia"
                    class="block mt-1 w-full border rounded p-2 text-black">
                <p class="text-sm text-gray-500 mt-1">
                    Jeśli nie podasz, zostanie automatycznie użyta dzisiejsza data.
                </p>
            </div>

            <!-- Data sprzedaży (opcjonalna, domyślnie równa dacie wystawienia) -->
            <div class="mb-4">
                <label for="data_sprzedazy" class="block font-medium mb-1">Data sprzedaży (opcjonalnie)</label>
                <input id="data_sprzedazy" type="date" name="data_sprzedazy"
                    class="block mt-1 w-full border rounded p-2 text-black">
                <p class="text-sm text-gray-500 mt-1">
                    Jeśli nie podasz, zostanie użyta data wystawienia.
                </p>
            </div>

            <!-- Notatki -->
            <div class="mb-4">
                <label for="notes" class="block font-medium mb-1">Notatki</label>
                <textarea id="notes" name="notes" rows="3"
                    class="block mt-1 w-full border rounded p-2 text-black"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Zapisz fakturę</button>
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

                // jeśli data wystawienia nie została podana → ustaw na dziś
                if (!dataWystawienia.value) {
                    dataWystawienia.value = today;
                }

                // jeśli data sprzedaży pusta → ustaw ją na datę wystawienia
                if (!dataSprzedazy.value) {
                    dataSprzedazy.value = dataWystawienia.value;
                }
            });
        });
    </script>
</x-layout>
