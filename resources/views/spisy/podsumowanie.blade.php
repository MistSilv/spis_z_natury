<x-layout title="Podsumowanie spisu {{ $spis->name }}">
    <div x-data="{
            menuOpen: false,
            menuX: 0,
            menuY: 0,
            currentRow: null,
            showPriceInput: false,
            showQtyInput: false,
            newPrice: 0,
            newQty: 0,
            produktId: null
        }"
         class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-teal-700/50">

        <h1 class="text-2xl font-bold text-teal-400 mb-6">
            Podsumowanie spisu: {{ $spis->name }}
        </h1>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-slate-700 rounded-lg shadow-inner mb-4">
            <table class="min-w-full text-left text-white border-collapse bg-slate-900">
                <thead class="sticky top-0 bg-slate-800 z-10">
                    <tr class="border-b border-teal-600">
                        <th class="p-2">Produkt</th>
                        <th class="p-2">Cena</th>
                        <th class="p-2">Jednostka</th>
                        <th class="p-2">Ilość</th>
                        <th class="p-2">Kod kreskowy</th>
                        <th class="p-2">Dodane przez</th>
                        <th class="p-2">Wartość</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produktySpisu as $index => $produkt)
                        <tr 
                            @contextmenu.prevent="
                                menuOpen = true;
                                menuX = $event.pageX;
                                menuY = $event.pageY;
                                currentRow = {{ $index }};
                                showPriceInput = false;
                                showQtyInput = false;
                                newPrice = {{ $produkt->price }};
                                newQty = {{ $produkt->quantity }};
                                produktId = {{ $produkt->id }};
                            "
                            class="border-b border-teal-700 hover:bg-slate-800/50 cursor-pointer transition-colors">

                            <td class="p-2 font-medium">{{ $produkt->name }}</td>

                            <!-- Cena -->
                            <td class="p-2 relative">
                                <span x-show="!showPriceInput || currentRow != {{ $index }}">{{ number_format($produkt->price, 2) }}</span>
                                <form x-show="showPriceInput && currentRow == {{ $index }}" method="POST" action="{{ route('spisy.produkty.update', [$spis->id, $produkt->id]) }}" class="flex gap-1 mt-1">
                                    @csrf
                                    <input type="number" step="0.01" name="price" x-model="newPrice" class="w-20 p-1 rounded bg-slate-900 text-white border border-teal-600">
                                    <input type="hidden" name="quantity" value="{{ $produkt->quantity }}">
                                    <button type="submit" class="px-2 py-1 bg-teal-600 hover:bg-teal-700 rounded text-white text-sm">Zapisz</button>
                                </form>
                            </td>

                            <!-- Jednostka -->
                            <td class="p-2">{{ $produkt->unit }}</td>

                            <!-- Ilość -->
                            <td class="p-2 relative">
                                <span x-show="!showQtyInput || currentRow != {{ $index }}">{{ $produkt->quantity }}</span>
                                <form x-show="showQtyInput && currentRow == {{ $index }}" method="POST" action="{{ route('spisy.produkty.update', [$spis->id, $produkt->id]) }}" class="flex gap-1 mt-1">
                                    @csrf
                                    <input type="number" step="0.01" name="quantity" x-model="newQty" class="w-20 p-1 rounded bg-slate-900 text-white border border-teal-600">
                                    <input type="hidden" name="price" value="{{ $produkt->price }}">
                                    <button type="submit" class="px-2 py-1 bg-teal-600 hover:bg-teal-700 rounded text-white text-sm">Zapisz</button>
                                </form>
                            </td>

                            <td class="p-2">{{ $produkt->barcode ?? '-' }}</td>
                            <td class="p-2">{{ $produkt->user->name ?? '-' }}</td>
                            <td class="p-2 font-semibold text-emerald-400">{{ number_format($produkt->price * $produkt->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Globalne menu kontekstowe -->
        <div 
            x-show="menuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            @click.away="menuOpen = false"
            :style="'position: fixed; top: ' + menuY + 'px; left: ' + menuX + 'px;'"
            class="bg-black border border-gray-700 rounded-lg shadow-2xl text-white text-sm w-48 z-50 overflow-hidden">
            
            <button @click.prevent="showPriceInput = true; showQtyInput = false; menuOpen = false"
                    class="block w-full px-4 py-3 hover:bg-gray-800 focus:outline-none focus:bg-gray-800 text-left font-semibold">
                ✏️ Edytuj cenę
            </button>
            
            <button @click.prevent="showQtyInput = true; showPriceInput = false; menuOpen = false"
                    class="block w-full px-4 py-3 hover:bg-gray-800 focus:outline-none focus:bg-gray-800 text-left font-semibold">
                📊 Edytuj ilość
            </button>
            
            <button @click.prevent="
                if(confirm('Czy na pewno chcesz usunąć ten produkt?')) {
                    $event.preventDefault();
                    document.getElementById('delete-form-' + produktId).submit();
                }
                menuOpen = false;
            " class="block w-full px-4 py-3 hover:bg-red-800 focus:outline-none focus:bg-red-800 text-left text-red-400 font-semibold">
                🗑️ Usuń produkt
            </button>
        </div>

        <!-- Ukryte formularze usuwania dla każdego produktu -->
        @foreach($produktySpisu as $produkt)
            <form id="delete-form-{{ $produkt->id }}" method="POST" action="{{ route('spisy.produkty.delete', [$spis->id, $produkt->id]) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <!-- Paginacja -->
        <div class="mt-4">
            {{ $produktySpisu->links() }}
        </div>

        <!-- Podsumowanie -->
        <div class="mt-6 text-left">
            <p class="text-lg text-teal-400 font-medium">
                Łącznie pozycji w spisie: <span class="text-white font-bold">{{ $totalItems }}</span>
            </p>
            <p class="text-lg font-bold text-teal-400 mt-1">
                Łączna wartość spisu: <span class="text-white">{{ number_format($totalValue, 2) }}</span>
            </p>
        </div>

    </div>
</x-layout>
