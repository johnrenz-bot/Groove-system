<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send SMS - Groove</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="icon" href="/image/wc/logo.png" type="image/png" sizes="512x512">
<link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">📩 Send SMS</h1>

        {{-- Success Message --}}
        @if(session('status'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SMS Form --}}
        <form method="POST" action="{{ route('sms.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Recipient Number</label>
                <input type="text" name="to" placeholder="e.g. 639159793812" value="{{ old('to') }}"
                       class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-indigo-300" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="4" maxlength="160"
                          placeholder="Type your message..."
                          class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-indigo-300" required>{{ old('message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Max 160 characters</p>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                Send SMS
            </button>
        </form>
    </div>

</body>
</html>
