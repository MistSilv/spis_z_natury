<x-layout title="Lista Skanów Produktów">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-teal-700/50">
    <h1 class="text-2xl font-bold mb-6 text-gray-200">Lista Skanów Produktów</h1>

    <div class="mb-6 flex gap-4 flex-wrap">
        <button type="button" id="start-scan"
            class="bg-emerald-800 hover:bg-emerald-600 text-gray-100 px-4 py-2 rounded-lg shadow transition">
            ▶ Start Scanning
        </button>

        <button type="button" id="stop-scan"
            class="bg-red-800 hover:bg-red-600 text-gray-100 px-4 py-2 rounded-lg shadow transition hidden">
            ■ Stop Scanning
        </button>

        <div id="reader" style="width: 300px; display:none;"></div>
        <p id="scan-result" class="mt-2 text-sm text-gray-400"></p>
    </div>

    <div class="overflow-x-auto rounded-lg shadow border border-slate-800">
        <table id="products-table" class="min-w-full bg-slate-900 text-gray-200">
            <thead class="bg-slate-800">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Produkt</th>
                    <th class="px-4 py-2 text-left">Ilość</th>
                    <th class="px-4 py-2 text-left">Kod EAN</th>
                    <th class="px-4 py-2 text-left">Data skanu</th>
                    <th class="px-4 py-2 text-left">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse ($produktSkany as $skan)
                <tr class="hover:bg-slate-800/70 transition">
                    <td class="px-4 py-2">{{ $skan->id }}</td>
                    <td class="px-4 py-2">{{ $skan->product->name }}</td>
                    <td class="px-4 py-2">{{ $skan->quantity }}</td>
                    <td class="px-4 py-2">{{ $skan->barcode ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $skan->scanned_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-2 flex gap-2">
                       <button 
                            onclick="editQuantity({{ $skan->id }}, {{ Js::from($skan->product->name) }}, {{ $skan->quantity }})"
                            class="bg-teal-800 hover:bg-teal-600 text-slate-100 px-3 py-1 rounded shadow transition">
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
                    <td class="px-4 py-2 text-center text-gray-500" colspan="6">Brak zeskanowanych produktów</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $produktSkany->links() }}
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="{{ asset('js/barcode-scanner.js') }}"></script>

    </div>
</x-layout>
