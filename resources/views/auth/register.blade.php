<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejestracja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" action="{{ route('register') }}" class="bg-white p-6 rounded shadow-md w-full max-w-md">
        @csrf
        <h2 class="text-2xl font-bold mb-4">Rejestracja</h2>

        <input type="text" name="name" placeholder="Imię" required
               class="w-full mb-3 p-2 border rounded">
        
        <input type="email" name="email" placeholder="Email" required
               class="w-full mb-3 p-2 border rounded">
        @error('email')
            <div class="text-red-500 mb-2">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Hasło" required
               class="w-full mb-3 p-2 border rounded">

        <input type="password" name="password_confirmation" placeholder="Potwierdź hasło" required
               class="w-full mb-3 p-2 border rounded">

        <select name="role" required class="w-full mb-3 p-2 border rounded">
            @foreach($roles as $role)
                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
            @endforeach
        </select>

        <select name="region_id" class="w-full mb-3 p-2 border rounded">
            <option value="">Brak przypisanego regionu</option>
            @foreach($regions as $region)
                <option value="{{ $region->id }}">{{ $region->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
            Zarejestruj się
        </button>
    </form>
</body>
</html>
