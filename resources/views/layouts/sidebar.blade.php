<aside class="fixed top-0 left-0 z-50 h-screen bg-mono-white border-r border-mono-100 transition-all duration-300 overflow-y-auto"
       :class="sidebarOpen ? 'w-60' : '-translate-x-full md:translate-x-0 md:w-16'"
       @resize.window="if (window.innerWidth < 768) { sidebarOpen = false }"
>
    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 h-16 border-b border-mono-100">
        <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-sm">JR</span>
        </div>
        <span class="text-lg font-bold text-mono-900 truncate" x-show="sidebarOpen" x-cloak>Sistema JR</span>
    </div>

    <!-- Navigation -->
    <nav class="p-3 space-y-1">
        @php
            $menuItems = [
                ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Inicio'],
                ['route' => 'financeiro.contas', 'icon' => 'account_balance', 'label' => 'Contas'],
                ['route' => 'financeiro.transacoes', 'icon' => 'swap_vert', 'label' => 'Transacoes'],
                ['route' => 'financeiro.categorias', 'icon' => 'label', 'label' => 'Categorias'],
                ['route' => 'financeiro.cartoes', 'icon' => 'credit_card', 'label' => 'Cartoes'],
                ['route' => 'financeiro.investimentos', 'icon' => 'trending_up', 'label' => 'Investimentos'],
                ['route' => 'financeiro.comparacao', 'icon' => 'compare_arrows', 'label' => 'Comparacao'],
                ['route' => 'agenda', 'icon' => 'calendar_today', 'label' => 'Agenda'],
                ['route' => 'tarefas', 'icon' => 'check_circle', 'label' => 'Tarefas'],
                ['route' => 'crm.pipeline', 'icon' => 'view_kanban', 'label' => 'Pipeline'],
                ['route' => 'crm.contatos', 'icon' => 'contacts', 'label' => 'Contatos'],
                ['route' => 'crm.produtos', 'icon' => 'inventory_2', 'label' => 'Produtos'],
                ['route' => 'whatsapp.instancias', 'icon' => 'smartphone', 'label' => 'WhatsApp'],
                ['route' => 'whatsapp.chat', 'icon' => 'chat', 'label' => 'Conversas'],
                ['route' => 'whatsapp.templates', 'icon' => 'description', 'label' => 'Templates'],
                ['route' => 'whatsapp.campanhas', 'icon' => 'campaign', 'label' => 'Campanhas'],
            ];
        @endphp

        <p class="px-3 pt-2 pb-1 text-[10px] font-semibold text-mono-300 uppercase tracking-wider" x-show="sidebarOpen" x-cloak>
            Menu
        </p>

        @foreach ($menuItems as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*');
            @endphp
            <a href="{{ route($item['route'] ?? 'dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ $isActive
                          ? 'text-primary-500 bg-primary-100'
                          : 'text-mono-900 hover:bg-mono-100' }}"
               title="{{ $item['label'] }}"
            >
                <span class="material-icons-outlined text-[20px] flex-shrink-0">{{ $item['icon'] }}</span>
                <span x-show="sidebarOpen" x-cloak class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach

        <!-- Divider -->
        <div class="border-t border-mono-100 my-3"></div>

        <p class="px-3 pt-2 pb-1 text-[10px] font-semibold text-mono-300 uppercase tracking-wider" x-show="sidebarOpen" x-cloak>
            Sistema
        </p>

        <a href="{{ route('configuracoes') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('configuracoes') ? 'text-primary-500 bg-primary-100' : 'text-mono-900 hover:bg-mono-100' }}"
           title="Configuracoes"
        >
            <span class="material-icons-outlined text-[20px] flex-shrink-0">settings</span>
            <span x-show="sidebarOpen" x-cloak class="truncate">Configuracoes</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors text-error hover:bg-down-bg w-full"
                    title="Sair"
            >
                <span class="material-icons-outlined text-[20px] flex-shrink-0">logout</span>
                <span x-show="sidebarOpen" x-cloak class="truncate">Sair</span>
            </button>
        </form>
    </nav>
</aside>
