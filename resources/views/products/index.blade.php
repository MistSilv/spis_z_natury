<x-layout title="Lista Skanów Produktów">
    <h1 class="text-2xl font-bold mb-6">Lista Skanów Produktów</h1>

    <div class="mb-6 flex gap-4 flex-wrap">
        <button type="button" id="start-scan"
                class="bg-green-900 text-white px-3 py-1 rounded shadow hover:bg-green-600 transition">
            Start Scanning
        </button>
        <button type="button" id="stop-scan"
                class="bg-red-900 text-white px-3 py-1 rounded shadow hover:bg-red-600 transition hidden">
            Stop Scanning
        </button>
        <div id="reader" style="width: 300px; display:none;"></div>
        <p id="scan-result" class="mt-2 text-sm text-gray-400"></p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-700">ID</th>
                    <th class="px-4 py-2 text-left text-gray-700">Produkt</th>
                    <th class="hidden">Użytkownik</th>
                    <th class="hidden">Region</th>
                    <th class="px-4 py-2 text-left text-gray-700">Ilość</th>
                    <th class="px-4 py-2 text-left text-gray-700">Kod EAN</th>
                    <th class="px-4 py-2 text-left text-gray-700">Data skanu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($produktSkany as $skan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $skan->id }}</td>
                        <td class="px-4 py-2">{{ $skan->product->name }}</td>
                        <td class="hidden">{{ $skan->user->name }}</td>
                        <td class="hidden">{{ $skan->region->name }}</td>
                        <td class="px-4 py-2">{{ $skan->quantity }}</td>
                        <td class="px-4 py-2">{{ $skan->barcode ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $skan->scanned_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2 text-center text-gray-500">Brak zeskanowanych produktów</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="{{ asset('js/barcode-scanner.js') }}"></script>
</x-layout>
