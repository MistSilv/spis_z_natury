<x-layout>
    <div class="max-w-3xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
        <h1 class="text-2xl font-bold mb-6 text-sky-700">Edytuj fakturę {{ $faktura->number }}</h1>

        <form action="{{ route('faktury.update', $faktura) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="number" class="block font-medium mb-1 text-gray-200">Numer faktury</label>
                <input id="number" type="text" name="number" value="{{ old('number', $faktura->number) }}"
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded" required>

            <div class="mb-4">
                <label for="data_wystawienia" class="block font-medium mb-1 text-gray-200">Data wystawienia</label>
                <input id="data_wystawienia" type="date" name="data_wystawienia"
                       value="{{ old('data_wystawienia', $faktura->data_wystawienia?->format('Y-m-d')) }}"
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label for="data_sprzedazy" class="block font-medium mb-1 text-gray-200">Data sprzedaży</label>
                <input id="data_sprzedazy" type="date" name="data_sprzedazy"
                       value="{{ old('data_sprzedazy', $faktura->data_sprzedazy?->format('Y-m-d')) }}"
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
            </div>

            <div class="mb-4">
                <label for="notes" class="block font-medium mb-1 text-gray-200">Notatki</label>
                <textarea id="notes" name="notes"
                        class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded" rows="3">{{ old('notes', $faktura->notes) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</x-layout>
