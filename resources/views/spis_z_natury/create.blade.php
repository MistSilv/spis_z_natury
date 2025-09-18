<x-layout>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Nowy Spis z Natury</h1>

        <!-- Formularz filtrów -->
        <form method="GET" action="{{ route('spisy.create') }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="region" class="block font-medium">Region</label>
                <select name="region_id" id="region" class="border rounded px-2 py-1">
                    <option value="">Wybierz region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="start_date" class="block font-medium">Od daty</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded px-2 py-1">
            </div>

            <div>
                <label for="end_date" class="block font-medium">Do daty</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded px-2 py-1">
            </div>

            <div>
                <label for="per_page" class="block font-medium">Ilość na stronie</label>
                <select name="per_page" id="per_page" class="border rounded px-2 py-1">
                    <option value="50" {{ request('per_page', $perPage ?? 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', $perPage ?? 50) == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ request('per_page', $perPage ?? 50) == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Załaduj
            </button>
        </form>

        @if(isset($scans) && $scans->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Nazwa produktu</th>
                            <th class="border p-2">Ilość</th>
                            <th class="border p-2">Jednostka</th>
                            <th class="border p-2">Cena</th>
                            <th class="border p-2">Barcode</th>
                            <th class="border p-2">Data skanu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scans as $scan)
                            <tr>
                                <td class="border p-2">{{ $scan->product->name }}</td>
                                <td class="border p-2">{{ $scan->quantity }}</td>
                                <td class="border p-2">{{ $scan->product->unit->code }}</td>
                                <td class="border p-2">{{ $scan->product->price ?? '-' }}</td>
                                <td class="border p-2">{{ $scan->barcode ?? '-' }}</td>
                                <td class="border p-2">{{ $scan->scanned_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginacja -->
            <div class="mt-4">
                {{ $scans->links() }}
            </div>
        @else
            <p class="text-gray-500">Brak produktów do wyświetlenia.</p>
        @endif
    </div>
</x-layout>
