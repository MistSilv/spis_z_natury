<x-layout title="Import danych z CSV">
    <div class="flex justify-center bg-black p-4">
        <div class="inline-block bg-gradient-to-br from-black via-slate-950 to-slate-900 rounded-lg shadow-lg p-8 border border-cyan-700/50 text-center">


            <h2 class="text-2xl font-bold mb-6 text-sky-700 drop-shadow text-center">
                Import danych z pliku CSV
            </h2>

            {{-- Komunikaty o błędach --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-900/30 text-red-300 rounded-lg border border-red-700/50">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>❌ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Komunikat o sukcesie --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-900/30 text-green-300 rounded-lg border border-green-700/50">
                    ✅ {{ session('success') }}
                </div>
            @endif

            {{-- Formularz --}}
            <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="csv_file" class="block text-gray-300 font-semibold mb-2">
                        Wybierz plik CSV:
                    </label>
                    <input
                        type="file"
                        name="csv_file"
                        id="csv_file"
                        accept=".csv,.txt"
                        required
                        class="block w-full text-gray-200 bg-gray-800 border border-gray-700 rounded-lg shadow-sm
                               focus:ring-sky-500 focus:border-sky-500 p-2">
                    <p class="text-sm text-gray-400 mt-1">
                        Nazwa pliku powinna odpowiadać kodowi regionu — np. <code>garmaz.csv</code>, <code>magazyn.csv</code>.
                    </p>
                </div>

                <div class="text-center">
                    <button type="submit"
                            class="bg-sky-800 hover:bg-sky-600 text-slate-100 font-semibold py-3 px-6 rounded-2xl shadow-md border border-teal-800/50 transition">
                        Importuj dane
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-layout>
