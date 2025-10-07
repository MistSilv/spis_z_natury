<x-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Faktury</h1>
            <a href="{{ route('faktury.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Dodaj fakturę</a>
        </div>
        <div class="overflow-x-auto rounded-lg shadow border border-neutral-700">
            <table class="min-w-full text-gray-300">
                <thead class="bg-neutral-900 text-sm text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Lp.</th>
                        <th class="px-4 py-2 text-left">Numer faktury</th>
                        <th class="px-4 py-2 text-left">Data sprzedaży</th>
                        <th class="px-4 py-2 text-left">Utworzono</th>
                        <th class="px-4 py-2 text-left">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse ($faktury as $faktura)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="px-4 py-2">{{ $faktura->id }}</td>
                            <td class="px-4 py-2">{{ $faktura->number }}</td>
                            <td class="px-4 py-2">{{ $faktura->data_sprzedazy->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $faktura->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="{{ route('faktury.show', $faktura) }}" class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">Pokaż</a>
                                <a href="{{ route('faktury.edit', $faktura) }}" class="bg-sky-800 hover:bg-emerald-600 text-slate-100 px-3 py-1 rounded shadow transition">Edytuj</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border p-2 text-center">Brak faktur</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            <!-- Paginator -->
        <div class="mt-4">
            {{ $faktury->links() }}
        </div>
    </div>
</x-layout>
