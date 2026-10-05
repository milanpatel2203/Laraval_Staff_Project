<!DOCTYPE html>
@php
    $headerUser = auth()->user();
@endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - UEST HRMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('hrms_theme') || '{{ $headerUser && $headerUser->theme ? $headerUser->theme : "charcoal" }}';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @stack('styles')
</head>

<body class="bg-[#F5F5F5] text-[#2D2D2D] font-sans antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside id="sidebar" class="w-60 bg-[#2D2D2D] text-[#F5F5F5] flex flex-col fixed top-0 left-0 bottom-0 z-50 transition-all duration-150">
            <div class="p-4 border-b border-[#F5F5F5]/10 flex items-center justify-between">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 text-base font-bold tracking-wide text-white hover:opacity-95 transition-opacity">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center p-1 shrink-0 shadow-xs">
                        <img src="{{ asset('images/uest_logo.png') }}" alt="UEST Logo" class="w-full h-full object-contain">
                    </div>
                    <span id="logoText" class="truncate tracking-wide">UEST HRMS</span>
                </a>
            </div>

            <nav class="flex-1 py-4 overflow-y-auto">

                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/dashboard') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('/') || request()->is('dashboard') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Dashboard">
                            <i class="fas fa-th-large w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    @if(auth()->check() && auth()->user()->hasPermission('employees.view'))
                        <li>
                            <a href="{{ url('/employees') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('employees*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Employees">
                                <i class="fas fa-users w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Employees</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('departments.view'))
                        <li>
                            <a href="{{ url('/departments') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('departments*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Departments">
                                <i class="fas fa-sitemap w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Departments</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('attendance.view'))
                        <li>
                            <a href="{{ url('/attendance') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('attendance*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Attendance">
                                <i class="fas fa-clock w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Attendance</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('leaves.view'))
                        <li>
                            <a href="{{ url('/leaves') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('leaves*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Leaves">
                                <i class="fas fa-calendar-minus w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text flex-1">Leaves</span>
                                @if(isset($headerPendingLeavesCount) && $headerPendingLeavesCount > 0)
                                    <span class="nav-text sidebar-badge px-1.5 py-0.5 rounded text-[10px] font-bold bg-white text-[#2D2D2D]">{{ $headerPendingLeavesCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && (auth()->user()->hasPermission('payroll.view') || auth()->user()->hasPermission('payroll.payslip')))
                        <li>
                            <a href="{{ url('/payroll') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('payroll*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Payroll">
                                <i class="fas fa-money-bill-wave w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Payroll</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('holidays.view'))
                        <li>
                            <a href="{{ url('/holidays') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('holidays*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Holidays">
                                <i class="fas fa-umbrella-beach w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Holidays</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('roles.manage'))
                        <li>
                            <a href="{{ url('/roles') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('roles*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Roles & Permissions">
                                <i class="fas fa-user-shield w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Roles & Permissions</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('settings.manage'))
                        <li>
                            <a href="{{ url('/settings') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('settings*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Settings">
                                <i class="fas fa-cog w-5 mr-3 text-center text-sm shrink-0"></i>
                                <span class="nav-text">Settings</span>
                            </a>
                        </li>
                    @endif
                </ul>

            </nav>
        </aside>


        {{-- Main Content --}}
        <div id="mainContent"
            class="flex-1 ml-60 flex flex-col min-h-screen transition-all duration-150">


            {{-- Topbar --}}
            <header
                class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-40">

                {{-- Left Side --}}
                <div class="flex items-center gap-4">

                    <button id="sidebarToggle"
                        class="text-base text-[#2D2D2D] p-1.5 rounded hover:bg-gray-100 focus:outline-none"
                        title="Toggle Sidebar">

                        <i class="fas fa-bars"></i>

                    </button>

                    <div class="flex items-center gap-2">

                        <h1 class="text-base font-bold text-[#2D2D2D]">
                            @yield('page-title', 'Dashboard')
                        </h1>

                    </div>

                </div>


                {{-- Search --}}
                @if(auth()->user()->hasPermission('employees.view'))

                    <form action="{{ route('employees.index') }}"
                        method="GET"
                        class="hidden md:flex items-center relative">

                        <i class="fas fa-search absolute left-3 text-xs text-gray-400"></i>

                        <input type="text"
                            name="search"
                            placeholder="Search employee, ID, role..."
                            class="pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded text-[#2D2D2D] w-52 focus:outline-none focus:border-[#2D2D2D] focus:bg-white focus:w-64 transition-all">

                    </form>

                @endif


                {{-- Right Side --}}
                <div class="flex items-center gap-4">


                    {{-- Date & Clock --}}
                    <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500 font-medium">

                        <i class="fas fa-calendar-alt text-xs"></i>

                        <span>{{ now()->format('D, d M') }}</span>

                        <span>&middot;</span>
                        <span id="liveClock" class="font-bold text-[#2D2D2D]">{{ now()->format('h:i A') }}</span>
                    </div>

                    {{-- Theme Color Combos Switcher --}}
                    <div class="relative">
                        <button id="themeSwitcherBtn" class="text-[#2D2D2D] p-2 rounded hover:bg-gray-100 focus:outline-none flex items-center gap-1.5" title="Theme Color Combos">
                            <i class="fas fa-palette text-base"></i>
                        </button>

                        {{-- Theme Popover Dropdown --}}
                        <div id="themeDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white border border-gray-300 rounded shadow-lg z-50 text-xs overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                                <span class="font-bold text-[#2D2D2D] flex items-center gap-1.5">
                                    <i class="fas fa-swatchbook text-gray-500"></i> Theme Color Combos
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium">Instant Switch</span>
                            </div>

                            <div class="p-2 space-y-1.5 max-h-80 overflow-y-auto">
                                {{-- 1. Charcoal --}}
                                <button type="button" onclick="applyTheme('charcoal')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="charcoal">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #2D2D2D;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #F5F5F5;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Monochrome Charcoal</p>
                                            <p class="text-[10px] text-gray-400">Minimalist &middot; #2D2D2D</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>

                                {{-- 2. Executive Navy --}}
                                <button type="button" onclick="applyTheme('navy')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="navy">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #0F172A;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #F8FAFC;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Executive Navy</p>
                                            <p class="text-[10px] text-gray-400">Corporate &middot; #0F172A</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>

                                {{-- 3. Royal Indigo --}}
                                <button type="button" onclick="applyTheme('indigo')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="indigo">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #1E1B4B;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #F5F3FF;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Royal Indigo</p>
                                            <p class="text-[10px] text-gray-400">Modern SaaS &middot; #1E1B4B</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>

                                {{-- 4. Forest Emerald --}}
                                <button type="button" onclick="applyTheme('emerald')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="emerald">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #143626;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #F4F6F4;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Forest Emerald</p>
                                            <p class="text-[10px] text-gray-400">Sophisticated &middot; #143626</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>

                                {{-- 5. Warm Walnut --}}
                                <button type="button" onclick="applyTheme('walnut')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="walnut">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #292524;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #F5F5F4;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Warm Walnut</p>
                                            <p class="text-[10px] text-gray-400">Warm Luxury &middot; #292524</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>

                                {{-- 6. Deep Burgundy --}}
                                <button type="button" onclick="applyTheme('burgundy')" class="theme-option-btn w-full p-2.5 rounded border border-gray-200 hover:border-gray-400 flex items-center justify-between transition-none text-left" data-theme-target="burgundy">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex items-center -space-x-1">
                                            <span class="w-5 h-5 rounded-full border border-white shadow-sm" style="background-color: #4C0519;"></span>
                                            <span class="w-5 h-5 rounded-full border border-gray-300 shadow-sm" style="background-color: #FFF1F2;"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2D2D] leading-tight">Deep Burgundy</p>
                                            <p class="text-[10px] text-gray-400">Bold Executive &middot; #4C0519</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-check text-xs text-[#2D2D2D] theme-check opacity-0"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Workable Notification Bell & Dropdown --}}
                    <div class="relative">
                        <button id="notificationBtn" class="relative text-[#2D2D2D] p-2 rounded hover:bg-gray-100 focus:outline-none" title="Notifications">
                            <i class="fas fa-bell text-base"></i>
                            @if(isset($headerPendingLeavesCount) && $headerPendingLeavesCount > 0)
                                <span class="absolute top-1 right-1 w-4 h-4 rounded-full bg-[#2D2D2D] text-[#F5F5F5] text-[9px] font-bold flex items-center justify-center">
                                    {{ $headerPendingLeavesCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Notification Dropdown Popover --}}
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-300 rounded shadow-lg z-50 text-xs overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                                <span class="font-bold text-[#2D2D2D]">System Notifications</span>
                                @if(isset($headerPendingLeavesCount) && $headerPendingLeavesCount > 0)
                                    <span class="px-2 py-0.5 bg-[#2D2D2D] text-white rounded text-[10px] font-bold">{{ $headerPendingLeavesCount }} Pending</span>
                                @endif
                            </div>

                            <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                                @if(isset($headerPendingLeavesCount) && $headerPendingLeavesCount > 0)
                                    <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="p-3 block bg-gray-50/70 hover:bg-gray-100 transition-none">
                                        <div class="flex items-start gap-2.5">
                                            <i class="fas fa-clock text-[#2D2D2D] mt-0.5"></i>
                                            <div>
                                                <p class="font-bold text-[#2D2D2D]">{{ $headerPendingLeavesCount }} Pending Leave {{ Str::plural('Request', $headerPendingLeavesCount) }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Requires manager approval or decline.</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif

                                @forelse($headerNotifications ?? [] as $act)
                                    <div class="p-3 flex items-start gap-2.5 hover:bg-gray-50">
                                        <i class="fas fa-{{ $act->icon ?: 'info-circle' }} text-gray-500 mt-0.5 shrink-0"></i>
                                        <div class="overflow-hidden">
                                            <p class="font-medium text-[#2D2D2D] truncate">{{ $act->title }}</p>
                                            @if($act->description)
                                                <p class="text-[11px] text-gray-500 truncate">{{ $act->description }}</p>
                                            @endif
                                            <span class="text-[10px] text-gray-400 mt-0.5 block">{{ $act->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-gray-400">No new notifications.</div>
                                @endforelse
                            </div>

                                <div class="p-2.5 bg-gray-50 border-t border-gray-200 text-center">
                                    <a href="{{ route('leaves.index') }}"
                                        class="text-xs font-semibold text-[#2D2D2D] hover:underline">
                                        Review All Leaves &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                    {{-- User Menu --}}
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center gap-2 p-1.5 rounded hover:bg-gray-100 focus:outline-none" title="User Menu">
                            <div class="w-7 h-7 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                                @if($headerUser && $headerUser->avatar_url)
                                    <img src="{{ $headerUser->avatar_url }}" alt="{{ $headerUser->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ $headerUser ? $headerUser->initials : 'AD' }}
                                @endif
                            </div>

                            <span class="hidden sm:inline text-xs font-semibold text-[#2D2D2D]">
                                {{ $headerUser ? explode(' ', $headerUser->name)[0] : 'Admin' }}
                            </span>

                            <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>

                        </button>

                        {{-- User Dropdown Menu --}}
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-300 rounded shadow-lg z-50 text-xs overflow-hidden">
                            <a href="{{ route('profile.edit') }}" class="p-3.5 border-b border-gray-200 bg-gray-50 flex items-center gap-3 hover:bg-gray-100 transition-none">
                                <div class="w-9 h-9 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                                    @if($headerUser && $headerUser->avatar_url)
                                        <img src="{{ $headerUser->avatar_url }}" alt="{{ $headerUser->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $headerUser ? $headerUser->initials : 'AD' }}
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-[#2D2D2D] truncate">{{ $headerUser ? $headerUser->name : 'Administrator' }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ $headerUser ? $headerUser->email : 'admin@uesthrms.com' }}</p>
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#2D2D2D] text-white">{{ $headerUser ? $headerUser->role_title : 'HR Manager' }}</span>
                                </div>
                            </a>

                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 font-medium text-[#2D2D2D] hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user-circle w-4 text-[#2D2D2D]"></i>
                                    <span>Manage Profile</span>
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-left text-red-600 hover:bg-red-50 font-medium transition-colors">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>

                        </div>

                    </div>

                </div>

            </header>


            {{-- Page Content --}}
            <main class="p-3 sm:p-4 flex-1">
                @yield('content')

            </main>

        </div>

    </div>


    @stack('scripts')

    {{-- SweetAlert2 Notification & Confirmation Engine --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        window.Toast = Toast;

        // Auto-fire session notifications
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: @json(session('success'))
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: @json(session('error'))
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: @json(session('warning'))
            });
        @endif

        @if(session('info') || session('status'))
            Toast.fire({
                icon: 'info',
                title: @json(session('info') ?: session('status'))
            });
        @endif

        @if(session('password_success'))
            Toast.fire({
                icon: 'success',
                title: @json(session('password_success'))
            });
        @endif

        @if(isset($errors) && $errors->any())
            Toast.fire({
                icon: 'error',
                title: @json($errors->first())
            });
        @endif

        // Global SweetAlert Interceptor for Delete & Confirmation Forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const onsubmitAttr = form.getAttribute('onsubmit');
            if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                const confirmMsg = match ? match[1] : 'Are you sure you want to proceed?';

                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, proceed',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.removeAttribute('onsubmit');
                        form.submit();
                    }
                });
                return false;
            }
        }, true);

        // Global Handler for Excel / CSV Export with SweetAlert Feedback
        document.addEventListener('click', function(e) {
            const exportBtn = e.target.closest('a[href*="/export"]');
            if (!exportBtn) return;

            e.preventDefault();

            const originalHtml = exportBtn.innerHTML;
            exportBtn.style.pointerEvents = 'none';
            exportBtn.classList.add('opacity-75');

            Toast.fire({
                icon: 'info',
                title: 'Preparing Excel export...',
                timer: 2000
            });

            fetch(exportBtn.href, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Download failed with status ' + response.status);

                let filename = 'export.csv';
                const disposition = response.headers.get('content-disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }

                return response.blob().then(blob => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();

                Toast.fire({
                    icon: 'success',
                    title: 'Excel file downloaded successfully!',
                    timer: 3500
                });
            })
            .catch(error => {
                console.error('Export error:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to download export file.'
                });
            })
            .finally(() => {
                exportBtn.style.pointerEvents = '';
                exportBtn.classList.remove('opacity-75');
                exportBtn.innerHTML = originalHtml;
            });
        });
    </script>
</body>

</html>