<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link">
        <img src="{{ asset('images/logo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img class="user-img" src="{{ Storage::url(auth()->user()->image) }}" alt=""
                    {{-- style="width: 64px !important; height: 64px !important;"  --}} class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->getFullname() }}</a>
            </div>
        </div>
        {{-- @if (auth()->user()->type == 'admin') --}}
        {{-- <h2>{{ auth()->user()->type }} is here</h2> --}}
        {{-- @else --}}
        {{-- <h2>wtf</h2> --}}
        {{-- @endif --}}
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                + @if (auth()->user()->type == 'admin')
                    <li class="nav-item has-treeview">
                        <a href="{{ route('home') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>{{ __('dashboard.title') }}</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview">
                        <a href="{{ route('shops.index') }}" class="nav-link {{ activeSegment('shops') }}">
                            <i class="nav-icon fas fa-cart-plus"></i>
                            <p>{{ 'Shops' }}</p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview">
                        <a href="{{ route('products.index') }}" class="nav-link {{ activeSegment('products') }}">
                            <i class="nav-icon fas fa-th-large"></i>
                            <p>{{ __('product.title') }}</p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ activeSegment('categories') }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{ __('Categories') }}</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview">
                        <a href="{{ route('customers.index') }}" class="nav-link {{ activeSegment('customers') }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>{{ __('customer.title') }}</p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview">
                        <a href="{{ route('users.index') }}" class="nav-link {{ activeSegment('users') }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>{{ 'Users' }}</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview">
                        <a href="{{ route('expenses.index') }}" class="nav-link {{ activeSegment('expenses') }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{ __('Expenses') }}</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item has-treeview">
                    <a href="{{ route('cart.index') }}" class="nav-link {{ activeSegment('cart') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('cart.title') }}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('cart.indexTokens') }}" class="nav-link {{ activeSegment('cart') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('cart.title2') }}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('orders.index') }}" class="nav-link {{ activeSegment('orders') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('order.title') }}</p>
                    </a>
                </li>
                @if (auth()->user()->type == 'admin')
                    <li class="nav-item has-treeview">
                        <a href="{{ route('reports.dailySale') }}" class="nav-link {{ activeSegment('reports') }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{ __('Daily Sale') }}</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item has-treeview">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ activeSegment('settings') }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>{{ __('settings.title') }}</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="document.getElementById('logout-form').submit()">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>{{ __('common.Logout') }}</p>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                        </form>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link {{ activeSegment('inventory') }}" data-toggle="treeview">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>{{ __('inventory.title') }}</p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('inventory.issue') }}" class="nav-link {{ activeSegment('issue') }}">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>{{ __('inventory.issue') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.deposit') }}"
                                class="nav-link {{ activeSegment('deposit') }}">
                                <i class="nav-icon fas fa-sign-in-alt"></i>
                                <p>{{ __('inventory.deposit') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.stockTaking') }}"
                                class="nav-link {{ activeSegment('stockTaking') }}">
                                <i class="nav-icon fas fa-list"></i>
                                <p>{{ __('inventory.stockTaking') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.evaluation') }}"
                                class="nav-link {{ activeSegment('evaluation') }}">
                                <i class="nav-icon fas fa-poll"></i>
                                <p>{{ __('inventory.evaluation') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
