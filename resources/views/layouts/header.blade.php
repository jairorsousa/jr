<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-6 bg-mono-white border-b border-mono-100">
    <!-- Left: Hamburger + Page Title -->
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen"
                class="p-1.5 rounded-lg text-mono-600 hover:bg-mono-50 transition-colors"
        >
            <span class="material-icons-outlined text-[22px]">menu</span>
        </button>

        @isset($header)
            <h1 class="text-xl font-bold text-mono-900">{{ $header }}</h1>
        @endisset
    </div>

    <!-- Right: User Menu -->
    <div class="flex items-center gap-3" x-data="{ userMenuOpen: false }">
        <!-- Dark Mode Toggle -->
        <button @click="toggleDark()"
                class="relative p-2 rounded-full text-mono-600 hover:bg-mono-50 transition-colors"
                title="Alternar tema"
        >
            <span x-show="!darkMode" class="material-icons-outlined text-[22px]">dark_mode</span>
            <span x-show="darkMode" x-cloak class="material-icons-outlined text-[22px]">light_mode</span>
        </button>

        <!-- Notifications -->
        <button class="relative p-2 rounded-full text-mono-600 hover:bg-mono-50 transition-colors">
            <span class="material-icons-outlined text-[22px]">notifications</span>
        </button>

        <!-- User Button -->
        <div class="relative">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-pill text-sm font-medium text-mono-900 hover:bg-mono-50 transition-colors"
            >
                <div class="w-8 h-8 rounded-full bg-mono-50 flex items-center justify-center">
                    <span class="material-icons-outlined text-[18px] text-mono-600">person</span>
                </div>
                <span class="hidden sm:inline capitalize">{{ Auth::user()->name ?? 'Jairo' }}</span>
                <span class="material-icons-outlined text-[18px] text-mono-300">expand_more</span>
            </button>

            <!-- Dropdown -->
            <div x-show="userMenuOpen"
                 x-cloak
                 @click.away="userMenuOpen = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-2 z-dropdown"
            >
                <div class="px-4 py-2 border-b border-mono-100">
                    <p class="text-sm font-semibold text-mono-900 capitalize">{{ Auth::user()->name ?? 'Jairo' }}</p>
                    <p class="text-xs text-mono-600">{{ Auth::user()->email ?? '' }}</p>
                </div>

                <a href="{{ route('configuracoes') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-mono-900 hover:bg-mono-50 transition-colors">
                    <span class="material-icons-outlined text-[18px] text-mono-300">settings</span>
                    Configuracoes
                </a>

                <div class="border-t border-mono-100 mt-1 pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-4 py-2 text-sm text-error hover:bg-down-bg transition-colors w-full">
                            <span class="material-icons-outlined text-[18px]">logout</span>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
