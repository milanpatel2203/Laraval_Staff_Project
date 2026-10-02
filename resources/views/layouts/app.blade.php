<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - UEST HRMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#F5F5F5] text-[#2D2D2D] font-sans antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="sidebar" class="w-60 bg-[#2D2D2D] text-[#F5F5F5] flex flex-col fixed top-0 left-0 bottom-0 z-50 transition-all duration-150">
            <div class="p-5 border-b border-[#F5F5F5]/10 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-lg font-bold tracking-wide">
                    <i class="fas fa-building text-xl shrink-0"></i>
                    <span id="logoText">UEST HRMS</span>
                </div>
            </div>

            <nav class="flex-1 py-4 overflow-y-auto">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/dashboard') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('/') || request()->is('dashboard') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Dashboard">
                            <i class="fas fa-th-large w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/employees') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('employees*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Employees">
                            <i class="fas fa-users w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Employees</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/departments') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('departments*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Departments">
                            <i class="fas fa-sitemap w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Departments</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/attendance') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('attendance*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Attendance">
                            <i class="fas fa-clock w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Attendance</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/leaves') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('leaves*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Leaves">
                            <i class="fas fa-calendar-minus w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text flex-1">Leaves</span>
                            @if(isset($headerPendingLeavesCount) && $headerPendingLeavesCount > 0)
                            <span class="nav-text px-1.5 py-0.5 rounded text-[10px] font-bold bg-white text-[#2D2D2D]">{{ $headerPendingLeavesCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/payroll') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('payroll*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Payroll">
                            <i class="fas fa-money-bill-wave w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Payroll</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/holidays') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('holidays*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Holidays">
                            <i class="fas fa-umbrella-beach w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Holidays</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/roles') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('roles*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Roles & Permissions">
                            <i class="fas fa-user-shield w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Roles & Permissions</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/settings') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('settings*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="Settings">
                            <i class="fas fa-cog w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-5 py-2.5 text-[13px] font-medium transition-none {{ request()->is('profile*') ? 'bg-white/10 text-white border-l-4 border-white' : 'text-[#F5F5F5]/65 hover:text-white hover:bg-white/5 border-l-4 border-transparent' }}" title="My Profile">
                            <i class="fas fa-user-circle w-5 mr-3 text-center text-sm shrink-0"></i>
                            <span class="nav-text">My Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-[#F5F5F5]/10">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 hover:opacity-90 transition-none group" title="Manage Profile">
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-sm shrink-0 font-bold text-white">
                        {{ $headerUser ? $headerUser->initials : 'AD' }}
                    </div>
                    <div id="userRoleFooter" class="flex flex-col overflow-hidden">
                        <span class="text-[13px] font-semibold truncate text-white">{{ $headerUser ? $headerUser->name : 'Administrator' }}</span>
                        <span class="text-[11px] text-[#F5F5F5]/50 truncate">{{ $headerUser ? $headerUser->role_title : 'HR Manager' }}</span>
                    </div>
                </a>
            </div>
        </aside>

        {{-- Main Content --}}
        <div id="mainContent" class="flex-1 ml-60 flex flex-col min-h-screen transition-all duration-150">
            {{-- Workable Header / Topbar --}}
            <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-40">
                {{-- Left Side: Toggle & Page Title --}}
                <div class="flex items-center gap-4">
                    <button id="sidebarToggle" class="text-base text-[#2D2D2D] p-1.5 rounded hover:bg-gray-100 focus:outline-none" title="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-bold text-[#2D2D2D]">@yield('page-title', 'Dashboard')</h1>
                    </div>
                </div>

                {{-- Center / Quick Search Bar --}}
                <form action="{{ route('employees.index') }}" method="GET" class="hidden md:flex items-center relative">
                    <i class="fas fa-search absolute left-3 text-xs text-gray-400"></i>
                    <input type="text" name="search" placeholder="Search employee, ID, role..." class="pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded text-[#2D2D2D] w-52 focus:outline-none focus:border-[#2D2D2D] focus:bg-white focus:w-64 transition-all">
                </form>

                {{-- Right Side Actions --}}
                <div class="flex items-center gap-4">
                    {{-- Live Date & Clock --}}
                    <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500 font-medium">
                        <i class="fas fa-calendar-alt text-xs"></i>
                        <span>{{ now()->format('D, d M') }}</span>
                        <span>&middot;</span>
                        <span id="liveClock" class="font-bold text-[#2D2D2D]">{{ now()->format('h:i A') }}</span>
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
                                <a href="{{ route('leaves.index') }}" class="text-xs font-semibold text-[#2D2D2D] hover:underline">
                                    Review All Leaves &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Workable User Profile & Dropdown --}}
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center gap-2 p-1.5 rounded hover:bg-gray-100 focus:outline-none" title="User Menu">
                            <div class="w-7 h-7 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $headerUser ? $headerUser->initials : 'AD' }}
                            </div>
                            <span class="hidden sm:inline text-xs font-semibold text-[#2D2D2D]">{{ $headerUser ? explode(' ', $headerUser->name)[0] : 'Admin' }}</span>
                            <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                        </button>

                        {{-- User Dropdown Menu --}}
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-300 rounded shadow-lg z-50 text-xs overflow-hidden">
                            <a href="{{ route('profile.edit') }}" class="p-4 border-b border-gray-200 bg-gray-50 block hover:bg-gray-100 transition-none">
                                <p class="font-bold text-[#2D2D2D]">{{ $headerUser ? $headerUser->name : 'Administrator' }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $headerUser ? $headerUser->email : 'admin@uesthrms.com' }}</p>
                                <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-[#2D2D2D] text-white">{{ $headerUser ? $headerUser->role_title : 'HR Manager' }}</span>
                            </a>

                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-[#2D2D2D] hover:bg-gray-100 border-b border-gray-100">
                                    <i class="fas fa-user-circle w-4 text-[#2D2D2D]"></i>
                                    <span>Manage Profile</span>
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-[#2D2D2D] hover:bg-gray-100">
                                    <i class="fas fa-cog w-4 text-gray-500"></i>
                                    <span>System Settings</span>
                                </a>
                                <a href="{{ route('roles.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-[#2D2D2D] hover:bg-gray-100">
                                    <i class="fas fa-user-shield w-4 text-gray-500"></i>
                                    <span>Roles & Permissions</span>
                                </a>
                                <a href="{{ route('employees.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-[#2D2D2D] hover:bg-gray-100">
                                    <i class="fas fa-users w-4 text-gray-500"></i>
                                    <span>Employee Directory</span>
                                </a>
                                <a href="{{ route('payroll.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-[#2D2D2D] hover:bg-gray-100">
                                    <i class="fas fa-money-bill-wave w-4 text-gray-500"></i>
                                    <span>Payroll Records</span>
                                </a>
                                <a href="{{ route('holidays.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-[#2D2D2D] hover:bg-gray-100">
                                    <i class="fas fa-umbrella-beach w-4 text-gray-500"></i>
                                    <span>Holiday Schedule</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="p-6 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
