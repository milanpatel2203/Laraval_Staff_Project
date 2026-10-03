<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verify OTP - HRMS</title>

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">

        <h1 class="text-2xl font-semibold text-center text-gray-800">
            Verify OTP
        </h1>

        <p class="text-center text-gray-500 mt-2">
            Enter the OTP sent to your mobile number
        </p>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mt-5 p-3 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Message --}}
        @if($errors->any())
            <div class="mt-5 p-3 bg-red-100 text-red-700 rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Local Development OTP --}}
        @if(session('login_otp'))
            <div class="mt-5 p-4 bg-yellow-100 border border-yellow-300 rounded-lg text-center">
                <p class="text-sm text-gray-600">
                    Development OTP
                </p>

                <p class="text-2xl font-bold text-gray-800 mt-1">
                    {{ session('login_otp') }}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('mobile.otp.verify.submit') }}" class="mt-6">
            @csrf

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Enter OTP
            </label>

            <input
                type="text"
                name="otp"
                maxlength="6"
                inputmode="numeric"
                placeholder="Enter 6 digit OTP"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-400"
                required
            >

            <button
                type="submit"
                class="w-full mt-5 bg-gray-800 text-white py-3 rounded-lg hover:bg-gray-700"
            >
                Verify OTP
            </button>
        </form>

    </div>

</body>
</html>