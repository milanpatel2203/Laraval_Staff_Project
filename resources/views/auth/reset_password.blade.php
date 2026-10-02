
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password - HRMS</title>

    @vite(['resources/css/app.css'])

</head>

<body class="min-h-screen bg-gray-100">

    <div class="min-h-screen flex items-center justify-center px-4">

        <div class="w-full max-w-sm bg-white rounded-xl shadow-sm border border-gray-200 p-7">

            {{-- Logo --}}
            <div class="text-center mb-6">

                <img
                    src="{{ asset('images/uest_logo.png') }}"
                    alt="UEST Logo"
                    class="h-14 w-auto mx-auto"
                >

            </div>


            {{-- Success Message --}}
            @if (session('success'))

                <div class="text-center">

                    {{-- Success Icon --}}
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-50">

                        <svg
                            class="h-6 w-6 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                    </div>


                    {{-- Success Title --}}
                    <h1 class="text-lg font-semibold text-green-700">
                        Password Reset Successfully
                    </h1>


                    {{-- Success Text --}}
                    <p class="text-sm text-gray-500 mt-2">
                        Your password has been updated successfully.
                    </p>


                    {{-- Back to Login --}}
               <a
    href="{{ route('login') }}"
    class="flex items-center justify-center w-full mt-3 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition"
>
    ← Back to Login
</a>

                </div>


            @else

                {{-- Error Messages --}}
                @if ($errors->any())

                    <div class="mb-4 text-sm text-red-600">

                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach

                    </div>

                @endif


                {{-- Heading --}}
                <div class="text-center mb-6">

                    <h1 class="text-lg font-semibold text-gray-900">
                        Reset Password
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Create a new password for your account
                    </p>

                </div>


                {{-- Reset Form --}}
                <form method="POST" action="{{ route('password.update') }}">

                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >

                    <input
                        type="hidden"
                        name="email"
                        value="{{ $email }}"
                    >


                    {{-- New Password --}}
                    <div class="mb-4">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            New Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter new password"
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-black"
                            required
                        >

                    </div>


                    {{-- Confirm Password --}}
                    <div class="mb-5">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-black"
                            required
                        >

                    </div>


                    {{-- Reset Button --}}
                    <button
                        type="submit"
                        class="w-full py-2.5 rounded-lg text-sm font-medium bg-black text-white hover:bg-gray-800 transition"
                    >
                        Reset Password
                    </button>

                </form>


                {{-- Back to Login --}}
                <a
    href="{{ route('login') }}"
    class="flex items-center justify-center w-full mt-3 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition"
>
    ← Back to Login
</a>

            @endif

        </div>

    </div>

</body>

</html>
```
