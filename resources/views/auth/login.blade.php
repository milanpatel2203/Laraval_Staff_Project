<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HRMS Login</title>

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --brand: #111111;
        }

        .tab-active {
            background: var(--brand);
            color: #ffffff;
        }

        .login-btn {
            background: #111111;
            color: #ffffff;
        }

        .login-btn:hover {
            background: #000000;
        }

        .input-focus:focus {
            outline: none;
            border-color: #111111;
            box-shadow: 0 0 0 2px #e5e5e5;
        }
    </style>
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
                    UEST HRMS
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Login to your account
                </p>

            </div>


            {{-- Success Message --}}
            @if(session('success'))

                <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">
                    {{ session('success') }}
                </div>

            @endif


            {{-- General Errors --}}
            @if ($errors->any())

                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">

                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach

                </div>

            @endif


            {{-- Tabs --}}
            <div class="flex mb-5 bg-gray-100 rounded-lg p-1">

                <button
                    type="button"
                    id="emailTab"
                    onclick="showEmailLogin()"
                    class="tab-active w-1/2 py-2 text-sm font-medium rounded-md"
                >
                    Email Login
                </button>

                <button
                    type="button"
                    id="mobileTab"
                    onclick="showMobileLogin()"
                    class="w-1/2 py-2 text-sm font-medium text-gray-600 rounded-md"
                >
                    Mobile Login
                </button>

            </div>


            {{-- Email Login --}}
            <div id="emailLogin">

                <form
                    method="POST"
                    action="{{ route('login.email') }}"
                >

                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">

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


                    {{-- Password --}}
                    <div class="mb-2">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            class="input-focus w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg"
                            required
                        >

                    </div>


                    {{-- Forgot Password --}}
                    <div class="text-right mb-5">

                        <a
                            href="{{ route('password.request') }}"
                            class="text-sm text-blue-600 hover:text-blue-700 hover:underline"
                        >
                            Forgot Password?
                        </a>

                    </div>


                    {{-- Login --}}
                    <button
                        type="submit"
                        class="login-btn w-full py-2.5 rounded-lg text-sm font-medium transition"
                    >
                        Login
                    </button>

                </form>

            </div>


            {{-- Mobile Login --}}
            <div id="mobileLogin" class="hidden">

                {{-- Mobile Error --}}
                @if($errors->has('mobile'))

                    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
                        {{ $errors->first('mobile') }}
                    </div>

                @endif


                <form
                    method="POST"
                    action="{{ route('login.sendOtp') }}"
                >

                    @csrf

                    {{-- Mobile --}}
                    <div class="mb-5">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mobile Number
                        </label>

                        <input
                            type="tel"
                            name="mobile"
                            maxlength="10"
                            value="{{ old('mobile') }}"
                            placeholder="Enter your mobile number"
                            class="input-focus w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg"
                            required
                        >

                    </div>


                    {{-- Send OTP --}}
                    <button
                        type="submit"
                        class="login-btn w-full py-2.5 rounded-lg text-sm font-medium transition"
                    >
                        Send OTP
                    </button>

                </form>

            </div>


            {{-- Register Company --}}
            <div class="text-center mt-6 pt-5 border-t border-gray-200">

                <p class="text-sm text-gray-500">
                    Don't have a company account?
                </p>

                <a
                    href="{{ route('company.register.form') }}"
                    class="text-sm font-medium text-blue-600 hover:underline"
                >
                    Register Company
                </a>

            </div>

        </div>

    </div>


    <script>

        function showEmailLogin() {

            document.getElementById('emailLogin')
                .classList.remove('hidden');

            document.getElementById('mobileLogin')
                .classList.add('hidden');

            document.getElementById('emailTab')
                .classList.add('tab-active');

            document.getElementById('emailTab')
                .classList.remove('text-gray-600');

            document.getElementById('mobileTab')
                .classList.remove('tab-active');

            document.getElementById('mobileTab')
                .classList.add('text-gray-600');
        }


        function showMobileLogin() {

            document.getElementById('emailLogin')
                .classList.add('hidden');

            document.getElementById('mobileLogin')
                .classList.remove('hidden');

            document.getElementById('mobileTab')
                .classList.add('tab-active');

            document.getElementById('mobileTab')
                .classList.remove('text-gray-600');

            document.getElementById('emailTab')
                .classList.remove('tab-active');

            document.getElementById('emailTab')
                .classList.add('text-gray-600');
        }

    </script>

</body>

</html>