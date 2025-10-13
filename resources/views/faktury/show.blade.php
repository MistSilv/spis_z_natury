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
                <p><span class="font-semibold text-sky-600">Region:</span> {{ $faktura->region?->name ?? '-' }}</p>
                <p><span class="font-semibold text-sky-600">Notatki:</span> {{ $faktura->notes ?? '-' }}</p>
            </div>
        </div>

        <!-- Produkty -->
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-sky-700">Produkty</h2>
            <button @click="openAddModal" class="px-4 py-2 bg-cyan-800 hover:bg-cyan-600 text-white rounded-lg font-semibold transition">
                Dodaj produkt
            </button>
        </div>

        <div class="overflow-x-auto border border-neutral-700 rounded-lg shadow-inner mb-4">
            <table class="min-w-full text-left text-gray-300 border-collapse">
                <thead class="bg-neutral-900 text-sm text-white sticky top-0">
                    <tr>
                        <th class="p-2">Nazwa</th>
                        <th class="p-2">Cena netto</th>
                        <th class="p-2">VAT %</th>
                        <th class="p-2">Ilość</th>
                        <th class="p-2">Jednostka</th>
                        <th class="p-2">Kod kreskowy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse ($produkty as $produkt)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition cursor-pointer"
                            @contextmenu.prevent='openContextMenu($event, @json($produkt))'>
                            <td class="p-2">{{ $produkt->name }}</td>
                            <td class="p-2 text-teal-500 font-semibold">{{ number_format($produkt->price_net, 2) }}</td>
                            <td class="p-2 text-yellow-400">{{ $produkt->vat ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->quantity }}</td>
                            <td class="p-2">{{ $produkt->unit ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                        </tr>

                    @empty
                        <tr><td colspan="6" class="p-4 text-center text-gray-500">Brak produktów</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $produkty->links() }}</div>

        <!-- MODAL DODAWANIA PRODUKTÓW -->
        <div x-show="addModalOpen" x-cloak
             class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
            <div class="bg-neutral-900 border border-cyan-700/50 rounded-xl shadow-lg w-full max-w-5xl p-6 relative text-gray-200">
                <button @click="closeAddModal" 
                        class="absolute top-3 right-3 text-red-500 font-bold text-2xl leading-none hover:text-red-400">&times;</button>

                <h3 class="text-xl font-bold text-sky-700 mb-4 text-center">Dodaj produkt do faktury</h3>

                <input type="text" placeholder="Wyszukaj produkt..." x-model="searchQuery"
                       @input.debounce.300ms="searchProducts"
                       class="w-full mb-2 p-2 rounded bg-neutral-800 border border-neutral-700 text-gray-100">

                <div class="max-h-60 overflow-y-auto mb-4" x-show="searchResults.length">
                    <template x-for="product in searchResults" :key="product.id">
                        <div @click="addProductFromSearch(product)"
                             class="p-2 border-b border-neutral-700 cursor-pointer hover:bg-neutral-700/30">
                            <span x-text="product.name"></span> — 
                            <span x-text="formatPrice(product.price_net || product.price)"></span> 
                            <span x-text="product.unit_name"></span>
                        </div>
                    </template>
                </div>

                <form action="{{ route('faktury.products.store', $faktura) }}" method="POST" id="product-form"
                    class="space-y-4" x-ref="productForm" @submit.prevent="handleSubmit($event)">
                    @csrf

                    <div class="space-y-2 text-sm max-h-[400px] overflow-y-auto border-t border-neutral-700 pt-2">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="flex flex-wrap gap-2 items-center bg-neutral-800/40 p-2 rounded-lg">
                                <input type="hidden" :name="`products[${index}][product_id]`" x-model="row.product_id">

                                <input type="text"
                                    :name="`products[${index}][name]`"
                                    x-model="row.name"
                                    placeholder="Nazwa"
                                    class="flex-1 min-w-[120px] border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100"
                                    required>

                                <!-- NETTO -->
                                <input type="number" step="0.01"
                                    :name="`products[${index}][price_net]`"
                                    x-model="row.price_net"
                                    @input="onNetInput(index, $event.target.value)"
                                    :readonly="row.readonlyNet"
                                    class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100"
                                    placeholder="Cena netto">

                                <!-- VAT -->
                                <input type="number" step="0.01"
                                    :name="`products[${index}][vat]`"
                                    x-model="row.vat"
                                    @input="onVatInput(index, $event.target.value)"
                                    placeholder="VAT %"
                                    class="w-20 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100">

                                <!-- BRUTTO -->
                                <input type="number" step="0.01"
                                    :name="`products[${index}][price_gross]`"
                                    x-model="row.price_gross"
                                    @input="onGrossInput(index, $event.target.value)"
                                    :readonly="row.readonlyGross"
                                    class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100"
                                    placeholder="Cena brutto">

                                <input type="number" step="0.01"
                                    :name="`products[${index}][quantity]`"
                                    x-model.number="row.quantity"
                                    placeholder="Ilość"
                                    class="w-20 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" required>

                                <select :name="`products[${index}][unit]`" 
                                        x-model="row.unit"
                                        class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100" 
                                        required>
                                    <option value="">-- wybierz jednostkę --</option>
                                    <template x-for="u in units" :key="u.code">
                                        <option :value="u.code" x-text="`${u.name} (${u.code})`" :selected="u.code === row.unit"></option>
                                    </template>
                                </select>

                                <input type="text"
                                    :name="`products[${index}][barcode]`"
                                    x-model="row.barcode"
                                    placeholder="EAN" maxlength="13"
                                    class="w-28 border border-neutral-700 rounded bg-neutral-900 p-1 text-gray-100">

                                <button type="button" @click="removeRow(index)"
                                        class="bg-red-600 hover:bg-red-500 text-white text-xs px-2 py-0.5 rounded transition">Usuń</button>
                            </div>
                        </template>
                    </div>

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
                addModalOpen: false,
                rows: [],
                units: @json($units),
                searchQuery: '',
                searchResults: [],

                openAddModal() { 
                    this.addModalOpen = true; 
                },
                closeAddModal() { 
                    this.addModalOpen = false; 
                    this.rows = [];
                },

                addEmptyRow() {
                    this.rows.push({
                        product_id: '',
                        name: '',
                        price_net: '',
                        vat: '',
                        price_gross: '',
                        quantity: 1,
                        unit: '',
                        barcode: '',
                        readonlyNet: false,
                        readonlyGross: false
                    });
                },

                removeRow(index) { this.rows.splice(index, 1); },

                searchProducts() {
                    if (!this.searchQuery) {
                        this.searchResults = [];
                        return;
                    }

                    fetch(`/faktury/products/live-search?q=${encodeURIComponent(this.searchQuery)}&date={{ $faktura->data_sprzedazy?->format('Y-m-d') }}`)
                        .then(res => res.json())
                        .then(data => this.searchResults = data);
                },

                formatPrice(price) {
                    if (price === null || price === undefined || price === '') {
                        return '0.00';
                    }
                    const num = parseFloat(price);
                    return isNaN(num) ? '0.00' : num.toFixed(2);
                },

                addProductFromSearch(product) {
                    let matchedUnit = '';
                    if (product.unit && this.units.length) {
                        const foundUnit = this.units.find(u => u.code === product.unit);
                        matchedUnit = foundUnit ? foundUnit.code : '';
                    }

                    const priceNet = product.price_net || product.price || '';
                    const safePriceNet = priceNet ? parseFloat(priceNet) : '';

                    const tempRows = [...this.rows];
                    tempRows.push({
                        product_id: product.id,
                        name: product.name,
                        price_net: safePriceNet,
                        vat: '',
                        price_gross: '',
                        quantity: 1,
                        unit: matchedUnit,
                        barcode: product.barcode || product.ean || '',
                        readonlyNet: false,
                        readonlyGross: false
                    });

                    this.rows = tempRows;
                    this.searchResults = [];
                    this.searchQuery = '';
                },

                isNumeric(v) { return v !== '' && v !== null && !isNaN(parseFloat(v)); },

                computeGross(net, vat) { return parseFloat(net) * (1 + (parseFloat(vat) || 0) / 100); },
                computeNet(gross, vat) { return parseFloat(gross) / (1 + (parseFloat(vat) || 0) / 100); },

                onNetInput(index, value) {
                    const row = this.rows[index];
                    row.price_net = value;

                    if (this.isNumeric(value)) {
                        row.readonlyGross = true;
                        row.readonlyNet = false;

                        if (this.isNumeric(row.vat))
                            row.price_gross = this.computeGross(row.price_net, row.vat).toFixed(2);
                    } else {
                        row.readonlyGross = false;
                        row.readonlyNet = false;
                        row.price_gross = '';
                    }
                },

                onGrossInput(index, value) {
                    const row = this.rows[index];
                    row.price_gross = value;

                    if (this.isNumeric(value)) {
                        row.readonlyNet = true;
                        row.readonlyGross = false;

                        if (this.isNumeric(row.vat))
                            row.price_net = this.computeNet(row.price_gross, row.vat).toFixed(2);
                    } else {
                        row.readonlyNet = false;
                        row.readonlyGross = false;
                        row.price_net = '';
                    }
                },

                onVatInput(index, value) {
                    const row = this.rows[index];
                    row.vat = value;

                    if (this.isNumeric(row.vat)) {
                        if (row.readonlyGross && this.isNumeric(row.price_net))
                            row.price_gross = this.computeGross(row.price_net, row.vat).toFixed(2);
                        else if (row.readonlyNet && this.isNumeric(row.price_gross))
                            row.price_net = this.computeNet(row.price_gross, row.vat).toFixed(2);
                    }
                },

                handleSubmit(event) { event.target.submit(); }
            }
        }
    </script>
</x-layout>
