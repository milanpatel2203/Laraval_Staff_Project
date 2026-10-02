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
        --brand-light: #f5f5f5;
        --border: #d1d5db;
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

    .link {
        color: #111111;
    }

    .link:hover {
        text-decoration: underline;
    }
</style>

</head>

<body class="min-h-screen bg-gray-100">

<div class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-sm bg-white rounded-xl shadow-sm border border-gray-200 p-7">

        <!-- Logo -->
        <div class="text-center mb-6">

            <img
                src="{{ asset('images/uest_logo.png') }}"
                alt="UEST Logo"
                class="h-14 w-auto mx-auto"
            >

            <h1 class="text-lg font-semibold text-gray-900 mt-3">
               Uest HRMS
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Login to your account
            </p>

        </div>


        <!-- Tabs -->
        <div class="flex mb-5 bg-gray-100 rounded-lg p-1">

            <button
                type="button"
                id="emailTab"
                onclick="showEmailLogin()"
                class="tab-active w-1/2 py-2 text-sm font-medium rounded-md">
                Email Login
            </button>

            <button
                type="button"
                id="mobileTab"
                onclick="showMobileLogin()"
                class="w-1/2 py-2 text-sm font-medium text-gray-600 rounded-md">
                Mobile Login
            </button>

        </div>

@if ($errors->any())
    <div class="mb-4 text-sm text-red-600">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
        <!-- Email Login -->
        <div id="emailLogin">

        <form method="POST" action="{{ route('login.email') }}">
    @csrf

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

    <div class="text-right mb-5">
       <a
    href="{{ route('password.request') }}"
    class="text-sm text-blue-600 hover:text-blue-700 hover:underline"
>
    Forgot Password?
</a>
    </div>

    <button
        type="submit"
        class="login-btn w-full py-2.5 rounded-lg text-sm font-medium transition"
    >
        Login
    </button>
</form>

        </div>


        <!-- Mobile Login -->
        <div id="mobileLogin" class="hidden">

            <form>

                <!-- Mobile -->
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        maxlength="10"
                        placeholder="Enter your mobile number"
                        class="input-focus w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg"
                    >

                </div>


                <!-- Send OTP -->
                <button
                    type="submit"
                    class="login-btn w-full py-2.5 rounded-lg text-sm font-medium transition">
                    Send OTP
                </button>

            </form>

        </div>

    </div>

</div>


<!-- Tab JavaScript -->
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
