<x-layout title="Lista Skanów Produktów">
    <h1 class="text-2xl font-bold mb-6">Lista Skanów Produktów</h1>

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
</x-layout>
