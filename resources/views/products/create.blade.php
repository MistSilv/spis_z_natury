{{-- resources/views/products/create.blade.php --}}
<x-layout title="Dodaj Produkt">
    <div class="max-w-3xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
        <h1 class="text-2xl font-bold mb-6 text-sky-700">Dodaj Produkt</h1>

        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            {{-- Nazwa produktu --}}
            <div class="mb-4">
                <label for="name" class="block mb-1 text-gray-200 font-medium">Nazwa</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                @error('name') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Cena --}}
            <div class="mb-4">
                <label for="price" class="block mb-1 text-gray-200 font-medium">Cena</label>
                <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}"
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                @error('price') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Jednostka --}}
            <div class="mb-4">
                <label for="unit_id" class="block mb-1 text-gray-200 font-medium">Jednostka</label>
                <select name="unit_id" id="unit_id" required
                        class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                    <option value="">-- wybierz jednostkę --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ $unit->code }})
                        </option>
                    @endforeach
                </select>
                @error('unit_id') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- ID Abaco (opcjonalne) --}}
            <div class="mb-4">
                <label for="id_abaco" class="block mb-1 text-gray-200 font-medium">ID Abaco (opcjonalne)</label>
                <input type="text" name="id_abaco" id="id_abaco" value="{{ old('id_abaco', $product->id_abaco ?? '') }}"
                    class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                <small class="text-gray-400">Pole opcjonalne – możesz zostawić puste</small>
                @error('id_abaco') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Kody EAN (dynamiczne dodawanie) --}}
            <div class="mb-4">
                <label class="block mb-1 text-gray-200 font-medium">Kody EAN (opcjonalnie)</label>
                <div id="ean-container">
                    <input type="text" name="barcodes[]" placeholder="EAN"
                        maxlength="13" pattern="\d*"
                        class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <button type="button" onclick="addEan()"
                        class="mt-2 px-3 py-1 bg-blue-800 hover:bg-blue-600 text-gray-100 rounded transition">
                    Dodaj EAN
                </button>
            </div>


            {{-- Zapisz --}}
            <div class="mt-6 flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-green-800 hover:bg-green-600 text-gray-100 rounded shadow transition">
                    Zapisz produkt
                </button>
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2 bg-gray-800 hover:bg-gray-600 text-gray-100 rounded shadow transition">
                   Anuluj
                </a>
            </div>
        </form>
    </div>

    <script>
        function addEan() {
            const container = document.getElementById('ean-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'barcodes[]';
            input.placeholder = 'EAN';
            input.maxLength = 13;
            input.pattern = "\\d*";
            input.className = 'w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded';
            input.oninput = function() { this.value = this.value.replace(/[^0-9]/g, ''); }
            container.appendChild(input);
        }
    </script>
</x-layout>
