<x-layout title="Edytuj fakturę {{ $faktura->number }}">
    <div class="max-w-4xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

        <h1 class="text-2xl font-bold text-sky-700 mb-6">
            Edytuj fakturę: {{ $faktura->number }}
        </h1>

        <form action="{{ route('faktury.update', $faktura) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Numer faktury -->
            <div>
                <label for="number" class="block text-sm font-semibold text-sky-600 mb-1">Numer faktury</label>
                <input 
                    id="number" 
                    type="text" 
                    name="number" 
                    value="{{ old('number', $faktura->number) }}"
                    required
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
            </div>

            <!-- Data wystawienia -->
            <div>
                <label for="data_wystawienia" class="block text-sm font-semibold text-sky-600 mb-1">Data wystawienia</label>
                <input 
                    id="data_wystawienia" 
                    type="date" 
                    name="data_wystawienia"
                    value="{{ old('data_wystawienia', $faktura->data_wystawienia?->format('Y-m-d')) }}"
                    required
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
            </div>

            <!-- Data sprzedaży -->
            <div>
                <label for="data_sprzedazy" class="block text-sm font-semibold text-sky-600 mb-1">Data sprzedaży</label>
                <input 
                    id="data_sprzedazy" 
                    type="date" 
                    name="data_sprzedazy"
                    value="{{ old('data_sprzedazy', $faktura->data_sprzedazy?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >
            </div>

            <!-- Notatki -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-sky-600 mb-1">Notatki</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="3"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-900 text-gray-100 p-2 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                >{{ old('notes', $faktura->notes) }}</textarea>
            </div>

            <!-- Przyciski -->
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('faktury.show', $faktura) }}" 
                   class="px-4 py-2 bg-neutral-800 text-gray-300 rounded-lg hover:bg-neutral-700 transition">
                   Anuluj
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-600 font-bold transition">
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</x-layout>
