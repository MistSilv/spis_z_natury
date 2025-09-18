<!-- resources/views/spis_z_natury/list.blade.php -->

<x-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Spisy z natury</h1>

        <a href="{{ route('spisy.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Utwórz nowy spis
        </a>

        @if($spisy->isEmpty())
            <p>Brak spisów z natury.</p>
        @else
            <table class="w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1">Nazwa</th>
                        <th class="border px-2 py-1">Opis</th>
                        <th class="border px-2 py-1">Użytkownik</th>
                        <th class="border px-2 py-1">Region</th>
                        <th class="border px-2 py-1">Data utworzenia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($spisy as $spis)
                        <tr>
                            <td class="border px-2 py-1">{{ $spis->name }}</td>
                            <td class="border px-2 py-1">{{ $spis->description ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $spis->user->name }}</td>
                            <td class="border px-2 py-1">{{ $spis->region->name }}</td>
                            <td class="border px-2 py-1">{{ $spis->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layout>
