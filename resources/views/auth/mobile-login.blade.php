<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mobile Login - HRMS</title>

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">
                HRMS Login
            </h1>

            <p class="text-gray-500 mt-2">
                Login using your mobile number
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('mobile.otp.send') }}">

            @csrf

            <label class="block mb-2 font-medium">
                Mobile Number
            </label>

            <input
                type="text"
                name="mobile"
                maxlength="10"
                placeholder="Enter 10 digit mobile number"
                value="{{ old('mobile') }}"
                class="w-full border rounded-lg px-4 py-3 mb-4"
                required
            >

            <button
                type="submit"
                class="w-full bg-black text-white py-3 rounded-lg"
            >
                Send OTP
            </button>

        </form>

    </div>

</body>
</html>