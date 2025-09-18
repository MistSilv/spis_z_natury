<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejestracja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gradient-to-br from-black via-slate-950 to-emerald-950 rounded-lg shadow-lg p-8 border border-emerald-600/50">
        <h2 class="text-2xl font-bold mb-6 text-center text-emerald-400 drop-shadow">Rejestracja</h2>
        @if($errors->any())
            <div class="mb-4 bg-emerald-600/15 text-emerald-300 border border-emerald-600/50 rounded p-3">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-emerald-300 mb-1" for="name">Imię</label>
                <input type="text" name="name" id="name" required 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
            </div>

            <div>
                <label class="block text-emerald-300 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" required 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
                @error('email')
                    <div class="text-red-500 mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-emerald-300 mb-1" for="password">Hasło</label>
                <input type="password" name="password" id="password" required 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
            </div>

            <div>
                <label class="block text-emerald-300 mb-1" for="password_confirmation">Potwierdź hasło</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60 placeholder:text-slate-400" />
            </div>

            <div>
                <label class="block text-emerald-300 mb-1" for="role">Rola</label>
                <select name="role" id="role" required 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60">
                    @foreach($roles as $role)
                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-emerald-300 mb-1" for="region_id">Region</label>
                <select name="region_id" id="region_id" 
                    class="w-full px-4 py-2 bg-slate-900 text-slate-100 border border-emerald-600/50 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500/60">
                    <option value="">Brak przypisanego regionu</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" 
                class="w-full bg-emerald-500 hover:bg-emerald-800 text-slate-950 py-2 rounded transition font-semibold shadow-lg">
                Zarejestruj się
            </button>
        </form>
    </div>
</body>
</html>
