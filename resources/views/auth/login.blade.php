<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logowanie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form method="POST" action="{{ route('login') }}" class="bg-white p-6 rounded shadow-md w-full max-w-sm">
        @csrf
        <h2 class="text-2xl font-bold mb-4">Logowanie</h2>

        <input type="email" name="email" placeholder="Email" required
               class="w-full mb-3 p-2 border rounded @error('email') border-red-500 @enderror">
        @error('email')
            <div class="text-red-500 mb-2">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Hasło" required
               class="w-full mb-3 p-2 border rounded">
        
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
            Zaloguj się
        </button>
    </form>
</body>
</html>
