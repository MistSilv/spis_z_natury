<x-layout title="Lista faktur">
    <div class="max-w-7xl mx-auto p-6 bg-zinc-900/50 rounded-xl shadow-lg border border-cyan-700/50">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-sky-700">Lista faktur</h1>
            <a href="{{ route('faktury.create') }}" class="inline-block px-4 py-2 bg-sky-800 text-white rounded hover:bg-sky-600 font-bold shadow">
                Dodaj fakturę
            </a>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] border border-neutral-700 rounded-lg shadow-inner mb-4">
            <table class="min-w-full text-left text-gray-300 border-collapse">
                <thead class="sticky top-0 bg-neutral-900 text-sm text-white z-10">
                    <tr>
                        <th class="p-2">Lp.</th>
                        <th class="p-2">Numer faktury</th>
                        <th class="p-2">Data sprzedaży</th>
                        <th class="p-2">Utworzono</th>
                        <th class="p-2">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse ($faktury as $faktura)
                        <tr class="even:bg-black hover:bg-neutral-800/70 transition">
                            <td class="p-2 font-medium">{{ $faktura->id }}</td>
                            <td class="p-2 font-semibold text-sky-700">{{ $faktura->number }}</td>
                            <td class="p-2">{{ $faktura->data_sprzedazy?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-2">{{ $faktura->created_at->format('Y-m-d') }}</td>
                            <td class="p-2 flex flex-wrap gap-2">
                                <a href="{{ route('faktury.show', $faktura) }}" 
                                   class="px-3 py-1 bg-sky-800 hover:bg-sky-600 text-slate-100 rounded shadow transition">
                                   Pokaż
                                </a>
                                <a href="{{ route('faktury.edit', $faktura) }}" 
                                   class="px-3 py-1 bg-emerald-700 hover:bg-emerald-600 text-slate-100 rounded shadow transition">
                                   Edytuj
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-400">Brak faktur</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $faktury->links() }}
        </div>

    </div>
</x-layout>
