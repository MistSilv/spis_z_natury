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
            <table class="min-w-full text-gray-300">
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
                            <td class="px-4 py-2">
                                {{ $product->latestPrice ? number_format($product->latestPrice->price, 2, '.', '') : '-' }}
                            </td>
                            <td class="px-4 py-2">{{ $product->unit->name ?? '-' }}</td>
                            <td class="px-4 py-2">
                                {{ $product->barcodes->pluck('barcode')->implode(', ') ?: '-' }}
                            </td>
                            <td class="px-4 py-2 flex gap-2 items-center">
                                <a href="{{ route('products.edit', $product) }}" 
                                   class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">
                                   Edytuj
                                </a>

                                @if($product->canBeDeleted)
                                    <form method="POST" action="{{ route('products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-700 hover:bg-red-500 text-white px-3 py-1 rounded shadow transition">
                                            Usuń
                                        </button>
                                    </form>
                                @else
                                    <div class="relative inline-block group">
                                        <button type="button" disabled
                                                class="bg-gray-700 text-gray-400 px-3 py-1 rounded shadow cursor-not-allowed">
                                            Usuń
                                        </button>

                                        <!-- Tooltip fixed na górze wszystkiego -->
                                        <div class="fixed bottom-auto left-auto w-max max-w-xs
                                                    bg-black text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100
                                                    pointer-events-none transition-opacity z-50"
                                            style="top: 0; left: 0;" 
                                            x-data
                                            x-init="
                                                const btn = $el.previousElementSibling;
                                                btn.addEventListener('mousemove', e => {
                                                    $el.style.top = (e.clientY - $el.offsetHeight - 8) + 'px';
                                                    $el.style.left = (e.clientX - $el.offsetWidth / 2) + 'px';
                                                });
                                            ">
                                                    Nie można usunąć produktu – jest używany w systemie.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-2 h-2 bg-black rotate-45"></div>
                                        </div>
                                    </div>
                                    @endif



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
