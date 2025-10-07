<x-layout title="Faktura {{ $faktura->number }}">
    <div x-data="productModal()" class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50 text-gray-200">

        <!-- Nagłówek -->
        <h1 class="text-2xl font-bold text-sky-700 mb-6 text-center drop-shadow">
            Faktura {{ $faktura->number }}
        </h1>

        <!-- Szczegóły faktury -->
        <div class="bg-neutral-900/60 border border-neutral-700 rounded-lg p-4 mb-6 shadow-inner">
            <div class="grid md:grid-cols-2 gap-2 text-sm">
                <p><span class="font-semibold text-sky-600">Data wystawienia:</span> {{ $faktura->data_wystawienia?->format('Y-m-d') ?? '-' }}</p>
                <p><span class="font-semibold text-sky-600">Data sprzedaży:</span> {{ $faktura->data_sprzedazy?->format('Y-m-d') ?? '-' }}</p>
                <p><span class="font-semibold text-sky-600">Utworzono:</span> {{ $faktura->created_at->format('Y-m-d H:i') }}</p>
                <p><span class="font-semibold text-sky-600">Notatki:</span> {{ $faktura->notes ?? '-' }}</p>
            </div>
        </div>

        <!-- Produkty -->
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-sky-700">Produkty</h2>
            <button @click="open = true" class="px-4 py-2 bg-cyan-800 hover:bg-cyan-600 text-white rounded-lg font-semibold transition">
                Dodaj produkt
            </button>
        </div>

        <div class="overflow-x-auto border border-neutral-700 rounded-lg shadow-inner mb-4">
            <table class="min-w-full text-left text-gray-300 border-collapse">
                <thead class="bg-neutral-900 text-sm text-white sticky top-0">
                    <tr>
                        <th class="p-2">Nazwa</th>
                        <th class="p-2">Cena Brutto</th>
                        <th class="p-2">Ilość</th>
                        <th class="p-2">Jednostka</th>
                        <th class="p-2">Kod kreskowy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse ($produkty as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="p-2">{{ $produkt->name }}</td>
                            <td class="p-2 text-teal-500 font-semibold">{{ number_format($produkt->price, 2) }}</td>
                            <td class="p-2">{{ $produkt->quantity }}</td>
                            <td class="p-2">{{ $produkt->unit ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Brak produktów</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="mt-4">
            {{ $produkty->links() }}
        </div>

        <!-- Modal: Dodawanie produktu -->
        <div 
            x-show="open" 
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-neutral-900 border border-cyan-700/50 rounded-xl shadow-lg w-full max-w-5xl p-6 relative text-gray-200">
                <button @click="open = false" 
                        class="absolute top-3 right-3 text-red-500 font-bold text-2xl leading-none hover:text-red-400">&times;</button>

                <h3 class="text-xl font-bold text-sky-700 mb-4 text-center">Dodaj produkt do faktury</h3>

                <form action="{{ route('faktury.products.store', $faktura) }}" method="POST" id="product-form" class="space-y-4">
                    @csrf

                    <!-- Wyszukiwarka -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-sky-600 mb-1">Wyszukaj produkt (nazwa lub EAN)</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="query" 
                                @input.debounce.300ms="search"
                                class="w-full border border-neutral-700 rounded-lg bg-neutral-800 text-gray-100 p-2 pr-10 focus:ring-2 focus:ring-sky-700 focus:outline-none"
                                placeholder="np. mleko lub 5901234567890">
                            <button 
                                type="button" 
                                @click="query=''; resultsVisible=false"
                                class="absolute right-3 top-2 text-red-500 hover:text-white font-bold">
                                ✕
                            </button>
                        </div>

                        <!-- Lista wyników -->
                        <ul class="absolute bg-neutral-900 border border-neutral-700 rounded mt-2 max-h-60 overflow-y-auto w-full z-10 text-sm"
                            x-show="resultsVisible">
                            <template x-for="product in results" :key="product.id">
                                <li class="px-2 py-1 flex justify-between items-center hover:bg-neutral-700 transition">
                                    <div>
                                        <span x-text="product.name"></span>
                                        <span class="text-gray-400 text-xs ml-1" x-text="product.ean ?? ''"></span>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="addProduct(product)"
                                        class="bg-sky-700 hover:bg-sky-500 text-white text-xs px-2 py-0.5 rounded transition">
                                        Wybierz
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <!-- Lista dodanych produktów -->
                    <div class="space-y-2 text-sm max-h-[400px] overflow-y-auto border-t border-neutral-700 pt-2">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="flex flex-wrap gap-2 items-center bg-neutral-800/40 p-2 rounded-lg">
                                <input type="hidden" :name="`products[${index}][product_id]`" x-model="row.product_id">
                                <input type="text" :name="`products[${index}][name]`" x-model="row.name"
                                       placeholder="Nazwa" class="flex-1 min-w-[120px] border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" required>
                                <input type="number" step="0.01" :name="`products[${index}][price]`" x-model="row.price"
                                       placeholder="Cena" class="w-24 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" required>
                                <input type="number" step="0.01" :name="`products[${index}][vat]`" x-model="row.vat"
                                       placeholder="VAT %" class="w-20 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100">
                                <input type="number" step="0.01" :name="`products[${index}][quantity]`" x-model="row.quantity"
                                       placeholder="Ilość" class="w-20 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" required>
                                <select :name="`products[${index}][unit]`" x-model="row.unit"
                                        class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" required>
                                    <option value="">-- jednostka --</option>
                                    <template x-for="u in units" :key="u.code">
                                        <option :value="u.code" x-text="u.name" :selected="u.code === row.unit"></option>
                                    </template>
                                </select>
                                <input type="text" :name="`products[${index}][barcode]`" x-model="row.barcode"
                                       placeholder="EAN" maxlength="13"
                                       @input="row.barcode = row.barcode.replace(/\D/g,'')"
                                       class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100">
                                <button type="button" @click="removeRow(index)"
                                        class="bg-red-600 hover:bg-red-500 text-white text-xs px-2 py-0.5 rounded transition">
                                    Usuń
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Akcje -->
                    <div class="flex justify-between items-center mt-4">
                        <button type="button" @click="addEmptyRow" class="text-sky-500 hover:underline text-sm">
                            + Dodaj ręcznie nowy produkt
                        </button>
                        <button type="submit" class="px-4 py-2 bg-sky-800 hover:bg-sky-600 rounded-lg text-white font-semibold text-sm">
                            Zapisz produkty
                        </button>
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
                        unit_name: product.unit_name ?? '',
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
