<x-layout>
    <div x-data="productModal()" class="max-w-4xl mx-auto p-6">

        <div class="overflow-x-auto rounded-lg shadow border border-neutral-700">

            <h1 class="text-2xl font-bold mb-6 text-sky-700 drop-shadow text-center">
                Faktura {{ $faktura->number }}
            </h1>

            <div class="mb-4 space-y-1">
                <p><strong>Data wystawienia:</strong> {{ $faktura->data_wystawienia ? $faktura->data_wystawienia->format('Y-m-d') : '-' }}</p>
                <p><strong>Data sprzedaży:</strong> {{ $faktura->data_sprzedazy ? $faktura->data_sprzedazy->format('Y-m-d') : '-' }}</p>
                <p><strong>Utworzono:</strong> {{ $faktura->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Notatki:</strong> {{ $faktura->notes ?? '-' }}</p>
            </div>

        </div>

        <div class="flex justify-between items-center mb-2">
            <h2 class="text-xl font-semibold">Produkty</h2>
            <button @click="open = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Dodaj produkt</button>
        </div>
        <div class="overflow-x-auto rounded-lg shadow border border-neutral-700">
            <table class="min-w-full text-gray-300">
                <thead class="bg-neutral-900 text-sm text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Nazwa</th>
                        <th class="px-4 py-2 text-left">Cena Brutto</th>
                        <th class="px-4 py-2 text-left">Ilość</th>
                        <th class="px-4 py-2 text-left">Jednostka</th>
                        <th class="px-4 py-2 text-left">Kod kreskowy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse ($produkty as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="px-4 py-2">{{ $produkt->name }}</td>
                            <td class="px-4 py-2">{{ number_format($produkt->price, 2) }}</td>
                            <td class="px-4 py-2">{{ $produkt->quantity }}</td>
                            <td class="px-4 py-2">{{ $produkt->unit ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $produkt->barcode ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">Brak produktów</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            <!-- Paginator -->
        <div class="mt-4">
            {{ $produkty->links() }}
        </div>
        

        <!-- Modal Alpine.js -->
        <div x-show="open" x-cloak
             class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl p-6 relative text-white">

                <h3 class="text-xl font-bold mb-4">Dodaj produkt do faktury</h3>
                <button @click="open = false" class="absolute top-3 right-3 text-red-500 font-bold text-xl">&times;</button>

                <form action="{{ route('faktury.products.store', $faktura) }}" method="POST" id="product-form">
                    @csrf

                    <!-- Wyszukiwarka produktu -->
                    <div class="mb-4 relative">
                        <label class="block font-medium mb-1">Wyszukaj produkt (nazwa lub EAN)</label>
                        <input type="text" x-model="query" @input.debounce.300ms="search"
                               class="w-full border rounded p-1 text-sm text-black pr-8"
                               placeholder="np. mleko lub 5901234567890">

                        <button type="button" @click="query=''; resultsVisible=false"
                                class="absolute ml-2 text-red-600 hover:text-white font-bold">X</button>

                        <ul class="bg-neutral-900 border border-neutral-700 rounded mt-2 max-h-60 overflow-y-auto absolute w-full z-10 text-sm"
                            x-show="resultsVisible">
                            <template x-for="product in results" :key="product.id">
                                <li class="px-2 py-1 flex justify-between items-center hover:bg-neutral-700">
                                    <div>
                                        <span x-text="product.name"></span>
                                        <span class="text-gray-400 text-xs ml-1" x-text="product.ean ?? ''"></span>
                                    </div>
                                    <button type="button" @click="addProduct(product)"
                                            class="bg-sky-700 hover:bg-sky-500 text-white text-xs px-2 py-0.5 rounded">
                                        Wybierz
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <!-- Kontener na wszystkie wiersze produktów -->
                    <div class="space-y-2 text-sm max-h-[400px] overflow-y-auto">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="flex gap-1 items-center border-b pb-1">
                                <input type="hidden" :name="`products[${index}][product_id]`" x-model="row.product_id">
                                <input type="text" :name="`products[${index}][name]`" x-model="row.name"
                                       placeholder="Nazwa" class="w-1/4 border rounded p-1 text-black text-sm" required>
                                <input type="number" step="0.01" :name="`products[${index}][price]`" x-model="row.price"
                                       placeholder="Cena" class="w-1/6 border rounded p-1 text-black text-sm" required>
                                <input type="number" step="0.01" :name="`products[${index}][vat]`" x-model="row.vat"
                                       placeholder="VAT %" class="w-1/12 border rounded p-1 text-black text-sm">
                                <input type="number" step="0.01" :name="`products[${index}][quantity]`" x-model="row.quantity"
                                       placeholder="Ilość" class="w-1/12 border rounded p-1 text-black text-sm" required>
                                <select :name="`products[${index}][unit]`" x-model="row.unit"
                                        class="w-1/6 border rounded p-1 text-black text-sm" required>
                                    <option value="">-- wybierz jednostkę --</option>
                                    <template x-for="u in units" :key="u.code">
                                        <option :value="u.code" x-text="u.name"></option>
                                    </template>
                                </select>
                                <input type="text" :name="`products[${index}][barcode]`" x-model="row.barcode"
                                       placeholder="EAN" maxlength="13"
                                       @input="row.barcode = row.barcode.replace(/\D/g,'')"
                                       class="w-1/6 border rounded p-1 text-black text-sm">
                                <button type="button" @click="removeRow(index)"
                                        class="bg-red-600 hover:bg-red-500 text-white text-xs px-2 py-0.5 rounded">Usuń</button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button type="button" @click="addEmptyRow" class="text-blue-400 hover:underline text-sm">Dodaj ręcznie nowy produkt</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Zapisz</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function productModal() {
            return {
                open: false,
                query: '',
                results: [],
                resultsVisible: false,
                rows: [],
                units: @json($units),
                fakturaDate: '{{ $faktura->data_sprzedazy->format("Y-m-d") }}',

                search() {
                    if (this.query.length < 2) {
                        this.resultsVisible = false;
                        this.results = [];
                        return;
                    }

                    fetch(`/faktury/products/live-search?q=${encodeURIComponent(this.query)}&date=${this.fakturaDate}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data;
                            this.resultsVisible = true;
                        });
                },

                addProduct(product) {
                    this.rows.push({
                        product_id: product.id,
                        name: product.name,
                        price: product.price ?? '',
                        vat: '',
                        quantity: 1,
                        unit: product.unit ?? '',
                        barcode: product.ean ?? ''
                    });
                    this.resultsVisible = false;
                    this.query = '';
                },

                addEmptyRow() {
                    this.rows.push({
                        product_id: '',
                        name: '',
                        price: '',
                        vat: '',
                        quantity: 1,
                        unit: '',
                        barcode: ''
                    });
                },

                removeRow(index) {
                    this.rows.splice(index, 1);
                }
            }
        }
    </script>
</x-layout>
