<x-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Dodaj produkty do faktury {{ $faktura->number }}</h1>

        <form action="{{ route('faktury.products.store', $faktura) }}" method="POST">
            @csrf

            <div id="products-container">
                <div class="product-row mb-4 flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block font-medium mb-1">Nazwa produktu</label>
                        <input type="text" name="products[name][]" class="w-full border rounded p-2" required>
                    </div>
                    <div class="w-32">
                        <label class="block font-medium mb-1">Cena netto</label>
                        <input type="number" step="0.01" name="products[price][]" class="w-full border rounded p-2" required>
                    </div>
                    <div class="w-24">
                        <label class="block font-medium mb-1">Ilość</label>
                        <input type="number" step="0.01" name="products[quantity][]" value="1" class="w-full border rounded p-2" required>
                    </div>
                    <div class="w-24">
                        <label class="block font-medium mb-1">Jednostka</label>
                        <input type="text" name="products[unit][]" class="w-full border rounded p-2">
                    </div>
                    <div class="w-24">
                        <label class="block font-medium mb-1">VAT (%)</label>
                        <input type="number" step="0.01" name="products[vat][]" class="w-full border rounded p-2" placeholder="opcjonalne">
                    </div>
                    <div class="w-32">
                        <label class="block font-medium mb-1">Kod kreskowy</label>
                        <input type="text" name="products[barcode][]" class="w-full border rounded p-2" maxlength="13">
                    </div>
                    <button type="button" onclick="removeRow(this)" class="text-red-600 font-bold ml-2">✕</button>
                </div>
            </div>

            <button type="button" onclick="addRow()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4">Dodaj produkt</button>

            <div class="flex justify-end">
                <x-button type="submit">Zapisz produkty</x-button>
            </div>
        </form>
    </div>

    <script>
        function addRow() {
            const container = document.getElementById('products-container');
            const row = document.createElement('div');
            row.classList.add('product-row', 'mb-4', 'flex', 'gap-2', 'items-end');
            row.innerHTML = `
                <div class="flex-1">
                    <label class="block font-medium mb-1">Nazwa produktu</label>
                    <input type="text" name="products[name][]" class="w-full border rounded p-2" required>
                </div>
                <div class="w-32">
                    <label class="block font-medium mb-1">Cena netto</label>
                    <input type="number" step="0.01" name="products[price][]" class="w-full border rounded p-2" required>
                </div>
                <div class="w-24">
                    <label class="block font-medium mb-1">Ilość</label>
                    <input type="number" step="0.01" name="products[quantity][]" value="1" class="w-full border rounded p-2" required>
                </div>
                <div class="w-24">
                    <label class="block font-medium mb-1">Jednostka</label>
                    <input type="text" name="products[unit][]" class="w-full border rounded p-2">
                </div>
                <div class="w-24">
                    <label class="block font-medium mb-1">VAT (%)</label>
                    <input type="number" step="0.01" name="products[vat][]" class="w-full border rounded p-2" placeholder="opcjonalne">
                </div>
                <div class="w-32">
                    <label class="block font-medium mb-1">Kod kreskowy</label>
                    <input type="text" name="products[barcode][]" class="w-full border rounded p-2" maxlength="13">
                </div>
                <button type="button" onclick="removeRow(this)" class="text-red-600 font-bold ml-2">✕</button>
            `;
            container.appendChild(row);
        }

        function removeRow(button) {
            button.closest('.product-row').remove();
        }
    </script>
</x-layout>
