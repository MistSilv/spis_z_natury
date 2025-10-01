<x-layout title="Lista Skanów Produktów">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
    <h1 class="text-2xl font-bold mb-6 text-sky-700">Lista Skanów Produktów</h1>

    <div class="mb-6 flex gap-4 flex-wrap">
        <button type="button" id="start-scan"
            class="bg-sky-800 hover:bg-sky-600 text-gray-100 px-4 py-2 rounded-lg shadow transition">
            ▶ Start Scanning
        </button>

        <button type="button" id="stop-scan"
            class="bg-red-800 hover:bg-red-600 text-gray-100 px-4 py-2 rounded-lg shadow transition hidden">
            ■ Stop Scanning
        </button>

        <div id="reader" style="width: 300px; display:none;"></div>
        <p id="scan-result" class="mt-2 text-sm text-gray-400"></p>
    </div>

     <form method="GET" class="flex items-center gap-2 mb-4">
        <label for="perPage" class="text-gray-200">Rekordów na stronę:</label>
        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border rounded px-2 py-1 bg-neutral-800 text-gray-200 border-neutral-700">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
    </form>

    <div class="overflow-hidden w-full overflow-x-auto rounded-sm border border-neutral-700">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-neutral-900 text-sm text-white">
                <tr>
                    <th scope="col" class="p-4">ID</th>
                    <th scope="col" class="p-4">Produkt</th>
                    <th scope="col" class="p-4">Ilość</th>
                    <th scope="col" class="p-4">Kod EAN</th>
                    <th scope="col" class="p-4">Data skanu</th>
                    <th scope="col" class="p-4">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-700">
                @forelse ($produktSkany as $skan)
                <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                    <td class="p-4">{{ $skan->id }}</td>
                    <td class="p-4">{{ $skan->product->name }}</td>
                    <td class="p-4">{{ number_format($skan->quantity, 2, '.', '') }}</td>
                    <td class="p-4">{{ $skan->barcode ?? '-' }}</td>
                    <td class="p-4">{{ $skan->scanned_at->format('Y-m-d H:i') }}</td>
                    <td class="p-4 flex gap-2">
                       <button 
                            onclick="editQuantity({{ $skan->id }}, {{ Js::from($skan->product->name) }}, {{ $skan->quantity }})"
                            class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">
                            Edytuj
                        </button>

                        <form method="POST" action="{{ route('produkt_skany.destroy', $skan) }}" onsubmit="return confirm('Na pewno usunąć?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="bg-red-800 hover:bg-red-600 text-slate-100 px-3 py-1 rounded shadow transition">
                                Usuń
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="p-4 text-center text-gray-500" colspan="6">Brak zeskanowanych produktów</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $produktSkany->links() }}
    </div>

    <script>
        window.loggedInUserId = {{ auth()->id() }};
        window.currentRegionId = {{ session('region_id') ?? 1 }};
    </script>


    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="{{ asset('js/barcode-scanner.js') }}"></script>

    </div>
</x-layout>