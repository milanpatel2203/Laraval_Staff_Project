<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Forgot Password - HRMS</title>

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

            <h1 class="text-lg font-semibold text-gray-900 mt-3">
                Forgot Password?
            </h1>

            @if (!session('success'))
                <p class="text-sm text-gray-500 mt-1">
                    Enter your email to reset your password
                </p>
            @endif

        </div>


        {{-- Success Message --}}
        @if (session('success'))

            <div class="text-center">

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

                <h2 class="text-base font-semibold text-gray-900">
                    Reset link sent
                </h2>

                <p class="text-sm text-gray-500 mt-2 leading-5">
                    We've sent a password reset link to your email address.
                </p>

                <p class="text-xs text-gray-500 mt-2 leading-5">
                    Please check your inbox and follow the link to create a new password.
                </p>

           <a
    href="{{ route('login') }}"
    class="flex items-center justify-center w-full mt-3 py-2 rounded-lg text-sm font-medium bg-black text-white hover:bg-gray-800 transition"
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


            {{-- Email Form --}}
            <form method="POST" action="{{ route('password.email') }}">

                @csrf

                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        class="input-focus w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-lg text-sm font-medium bg-black text-white hover:bg-gray-800 transition"
                >
                    Send Reset Link
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
```

</body>

</html>
