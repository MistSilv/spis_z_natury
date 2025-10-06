<x-layout title="Lista Skanów Produktów">
<div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
    <h1 class="text-2xl font-bold mb-6 text-sky-700">Lista Skanów Produktów</h1>

    <!-- Skaner -->
    <div class="mb-6 flex gap-4 flex-wrap">
        <button type="button" id="start-scan" class="bg-sky-800 hover:bg-sky-600 text-gray-100 px-4 py-2 rounded-lg shadow transition">▶ Start Scanning</button>
        <button type="button" id="stop-scan" class="bg-red-800 hover:bg-red-600 text-gray-100 px-4 py-2 rounded-lg shadow transition hidden">■ Stop Scanning</button>
        <div id="reader" style="width: 300px; display:none;"></div>
        <p id="scan-result" class="mt-2 text-sm text-gray-400"></p>
    </div>

    <!-- Wyszukiwarka + ilość -->
    <div x-data="productScan()" class="mb-6">
        <input type="text" x-model="query" @input.debounce.300="searchProducts" placeholder="Szukaj produktu po nazwie lub EAN..." class="w-full px-3 py-2 rounded bg-neutral-800 text-gray-200 border border-neutral-700" />

        <!-- Lista wyników -->
        <ul class="bg-neutral-900 border border-neutral-700 rounded mt-2 max-h-60 overflow-y-auto" x-show="results.length > 0" x-cloak>
            <template x-for="product in results" :key="product.id">
                <li class="px-4 py-2 flex justify-between items-center hover:bg-neutral-700">
                    <div>
                        <span x-text="product.name"></span>
                        <span class="text-gray-400 text-sm ml-2" x-text="product.barcode ?? ''"></span>
                    </div>
                    <button @click="selectProduct(product)" class="bg-sky-700 hover:bg-sky-500 text-white text-xs px-2 py-1 rounded">Wybierz</button>
                </li>
            </template>
        </ul>

        <!-- Ilość i dodaj -->
        <div x-show="selectedProduct" class="mt-2 flex items-center gap-2" x-cloak>
            <div>
                <label class="text-gray-200">Produkt:</label>
                <span class="ml-1 font-bold" x-text="selectedProduct?.name"></span>
            </div>

            <div>
                <label class="text-gray-200">Ilość:</label>
                <input type="number" 
                    x-model.number="quantity" 
                    :step="selectedProduct?.is_integer ? 1 : 0.01" 
                    :min="selectedProduct?.is_integer ? 1 : 0.01"
                    @input="quantity = selectedProduct?.is_integer ? Math.floor(quantity) : quantity"
                    class="w-32 px-2 py-1 rounded bg-neutral-800 text-gray-200 border border-neutral-700" 
                />
            </div>

            <button @click="addProduct()" class="bg-green-700 hover:bg-green-500 text-white px-3 py-1 rounded">Dodaj</button>
        </div>
    </div>

    <!-- Formularz perPage -->
    <form method="GET" class="flex items-center gap-2 mb-4">
        <label for="perPage" class="text-gray-200">Rekordów na stronę:</label>
        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border rounded px-2 py-1 bg-neutral-800 text-gray-200 border-neutral-700">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
    </form>

    <!-- Tabela -->
    <div class="overflow-hidden w-full overflow-x-auto rounded-sm border border-neutral-700">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-neutral-900 text-sm text-white">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Produkt</th>
                    <th class="p-4">Ilość</th>
                    <th class="p-4">Kod EAN</th>
                    <th class="p-4">Data skanu</th>
                    <th class="p-4">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-700" id="scans-table-body">
                @forelse ($produktSkany as $skan)
                    <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                        <td class="p-4">{{ $skan->id }}</td>
                        <td class="p-4">{{ $skan->product->name }}</td>
                        <td class="p-4">{{ number_format($skan->quantity, 2, '.', '') }}</td>
                        <td class="p-4">{{ $skan->barcode ?? '-' }}</td>
                        <td class="p-4">{{ $skan->scanned_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4 flex gap-2">
                            <button onclick="editQuantity({{ $skan->id }}, {{ Js::from($skan->product->name) }}, {{ $skan->quantity }})" class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">Edytuj</button>
                            <form method="POST" action="{{ route('produkt_skany.destroy', $skan) }}" onsubmit="return confirm('Na pewno usunąć?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-800 hover:bg-red-600 text-slate-100 px-3 py-1 rounded shadow transition">Usuń</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Brak zeskanowanych produktów</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $produktSkany->links() }}</div>
</div>

<script>
    window.loggedInUserId = {{ auth()->id() }};
    window.currentRegionId = {{ session('region_id') ?? 1 }};
</script>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="{{ asset('js/barcode-scanner.js') }}"></script>

<script>
function productScan() {
    return {
        query: '',
        results: [],
        selectedProduct: null,
        quantity: 1,

        searchProducts() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }


            fetch(`/products/search?q=${encodeURIComponent(this.query)}`)
                .then(res => res.json())
                .then(data => {
                    this.results = data.map(p => ({
                        ...p,
                        is_integer: ['szt','opak','tab','kart'].includes(p.unit?.code || '')
                    }));
                })
                .catch(err => console.error("❌ Błąd fetch /products/search:", err));
        },

        selectProduct(product) {
            this.selectedProduct = product;
            this.quantity = product.is_integer ? 1 : 0.01;
            this.query = product.name;
            this.results = [];
        },

        addProduct() {
            if (!this.quantity || this.quantity <= 0) { 
                alert('Podaj poprawną ilość!'); 
                return; 
            }

            let qtyToSend = this.selectedProduct.is_integer ? Math.floor(this.quantity) : parseFloat(this.quantity);

            fetch("{{ route('produkt_skany.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    product_id: this.selectedProduct.id,
                    user_id: window.loggedInUserId,
                    region_id: window.currentRegionId,
                    quantity: qtyToSend,
                    barcode: this.selectedProduct.barcode
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    this.selectedProduct = null;
                    this.query = '';
                    this.quantity = 1;

                    // Dodaj nowy wiersz dynamicznie
                    let tbody = document.getElementById("scans-table-body");
                    let skan = data.newScan;
                    let row = document.createElement("tr");
                    row.className = "even:bg-black hover:bg-neutral-800/70 transition";

                    let qtyDisplay = ['szt','opak','tab','kart'].includes(skan.product.unit?.code) 
                                      ? Math.floor(skan.quantity) 
                                      : parseFloat(skan.quantity).toFixed(2);

                    let scannedDate = new Date(skan.scanned_at);
                    let formattedDate = scannedDate.getFullYear() + '-' +
                        String(scannedDate.getMonth() + 1).padStart(2,'0') + '-' +
                        String(scannedDate.getDate()).padStart(2,'0') + ' ' +
                        String(scannedDate.getHours()).padStart(2,'0') + ':' +
                        String(scannedDate.getMinutes()).padStart(2,'0');

                    row.innerHTML = `
                        <td class="p-4">${skan.id}</td>
                        <td class="p-4">${skan.product.name}</td>
                        <td class="p-4">${qtyDisplay}</td>
                        <td class="p-4">${skan.barcode ?? '-'}</td>
                        <td class="p-4">${formattedDate}</td>
                        <td class="p-4 flex gap-2">
                            <button onclick="editQuantity(${skan.id}, '${skan.product.name}', ${skan.quantity})" 
                                class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">Edytuj</button>
                            <form method="POST" action="/produkt-skany/${skan.id}" onsubmit="return confirm('Na pewno usunąć?');">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="bg-red-800 hover:bg-red-600 text-slate-100 px-3 py-1 rounded shadow transition">Usuń</button>
                            </form>
                        </td>
                    `;
                    tbody.prepend(row); // dodaje wiersz na górę
                }
            })
            .catch(err => console.error("❌ Błąd API przy dodawaniu produktu:", err));
        }
    }
}
</script>

<!-- Tailwind Modals (place somewhere in your HTML, e.g., bottom of body) -->
<div id="modal-overlay" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
    <div id="modal" class="bg-gray-800 text-white rounded-lg shadow-lg max-w-lg w-full p-6">
        <h2 id="modal-title" class="text-xl font-bold mb-4"></h2>
        <p id="modal-message" class="mb-4"></p>
        <input id="modal-input" type="number" min="0.01" class="w-full p-2 mb-4 rounded bg-gray-700 border border-gray-600 hidden" />
        <div class="flex justify-end gap-2">
            <button id="modal-cancel" class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded">Anuluj</button>
            <button id="modal-confirm" class="bg-sky-600 hover:bg-sky-500 px-4 py-2 rounded">OK</button>
        </div>
    </div>
</div>


</x-layout>
