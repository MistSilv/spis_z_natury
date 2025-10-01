{{-- resources/views/products/list.blade.php --}}
<x-layout title="Lista Produktów">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">
        <h1 class="text-2xl font-bold mb-6 text-sky-700">Lista Produktów</h1>

        <div class="mb-6 flex gap-4 flex-wrap">
            <a href="{{ route('products.create') }}"
               class="bg-sky-800 hover:bg-sky-600 text-gray-100 px-4 py-2 rounded-lg shadow transition">
               + Dodaj produkt
            </a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow border border-neutral-700">
            <table class="min-w-full  text-gray-300">
                <thead class="bg-neutral-900 text-sm text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Nazwa</th>
                        <th class="px-4 py-2 text-left">Cena</th>
                        <th class="px-4 py-2 text-left">Jednostka</th>
                        <th class="px-4 py-2 text-left">Kody EAN</th>
                        <th class="px-4 py-2 text-left">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse($products as $product)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2">{{ $product->latestPrice ? number_format($product->latestPrice->price, 2, '.', '') : '-' }}</td>
                            <td class="px-4 py-2">{{ $product->unit->name ?? '-' }}</td>
                            <td class="px-4 py-2">
                                {{ $product->barcodes->pluck('barcode')->implode(', ') ?: '-' }}
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="{{ route('products.edit', $product) }}" 
                                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">
                                   Edytuj
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">Brak produktów.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-layout>
