{{-- resources/views/products/edit.blade.php --}}
<x-layout title="Edytuj Produkt">
    <div class="max-w-3xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-teal-700/50">
        <h1 class="text-2xl font-bold mb-6 text-gray-200">Edytuj Produkt</h1>

        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nazwa --}}
            <div class="mb-4">
                <label for="name" class="block font-medium mb-1 text-gray-200">Nazwa</label>
                <input type="text" name="name" id="name" 
                       value="{{ old('name', $product->name) }}" 
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                @error('name') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Cena --}}
            <div class="mb-4">
                <label for="price" class="block font-medium mb-1 text-gray-200">Cena</label>
                <input type="number" name="price" id="price" step="0.01" 
                       value="{{ old('price', $product->price) }}" 
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                @error('price') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Jednostka --}}
            <div class="mb-4">
                <label for="unit_id" class="block font-medium mb-1 text-gray-200">Jednostka</label>
                <select name="unit_id" id="unit_id" 
                        class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" 
                            {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Id Abaco --}}
            <div class="mb-4">
                <label for="id_abaco" class="block font-medium mb-1 text-gray-200">ID Abaco (opcjonalne)</label>
                <input type="text" name="id_abaco" id="id_abaco" 
                       value="{{ old('id_abaco', $product->id_abaco) }}" 
                       class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 rounded">
                @error('id_abaco') <p class="text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Kody EAN --}}
            <div class="mb-4">
                <label class="block font-medium mb-1 text-gray-200">Kody EAN (opcjonalnie)</label>
                <div id="barcodes-container">
                    @if(old('barcodes'))
                        @foreach(old('barcodes') as $barcode)
                            <input type="text" name="barcodes[]" 
                                   value="{{ $barcode }}" 
                                   class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded">
                        @endforeach
                    @else
                        @foreach($product->barcodes as $barcode)
                            <input type="text" name="barcodes[]" 
                                   value="{{ $barcode->barcode }}" 
                                   class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded">
                        @endforeach
                    @endif
                    <input type="text" name="barcodes[]" placeholder="Nowy EAN" 
                           class="w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded">
                </div>
                <button type="button" id="add-barcode" 
                        class="mb-4 px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-100 rounded transition">
                    Dodaj kolejny EAN
                </button>
            </div>

            {{-- Submit --}}
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-gray-100 rounded shadow transition">
                    Zapisz zmiany
                </button>
                <a href="{{ route('products.index') }}" 
                   class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 rounded shadow transition">
                   Anuluj
                </a>
            </div>
        </form>
    </div>

    {{-- JS do dodawania kolejnych pól EAN --}}
    <script>
        document.getElementById('add-barcode').addEventListener('click', function() {
            const container = document.getElementById('barcodes-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'barcodes[]';
            input.placeholder = 'Nowy EAN';
            input.className = 'w-full border border-slate-700 bg-slate-800 text-gray-100 p-2 mb-2 rounded';
            container.appendChild(input);
        });
    </script>
</x-layout>
