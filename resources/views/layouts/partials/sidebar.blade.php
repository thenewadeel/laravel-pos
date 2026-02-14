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
                <img class="user-img -mx-3 min-w-14 min-h-14 " src="{{ Storage::url(auth()->user()->image) }}"
                    alt="" {{-- style="width: 64px !important; height: 64px !important;"  --}} class="img-circle elevation-2" alt="User Image">
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
                @if (auth()->user()->type == 'admin')
                    <li class="nav-item has-treeview">
                        <a href="{{ route('home') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>{{ __('dashboard.title') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Management
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="display: none;">



                            <li class="nav-item has-treeview">
                                <a href="{{ route('shops.index') }}" class="nav-link {{ activeSegment('shops') }}">
                                    <i class="nav-icon fas fa-cart-plus"></i>
                                    <p>{{ 'Shops' }}</p>
                                </a>
                            </li>
                            <li class="nav-item has-treeview">
                                <a href="{{ route('products.index') }}"
                                    class="nav-link {{ activeSegment('products') }}">
                                    <i class="nav-icon fas fa-th-large"></i>
                                    <p>{{ __('product.title') }}</p>
                                </a>
                            </li>
                            <li class="nav-item has-treeview">
                                <a href="{{ route('categories.index') }}"
                                    class="nav-link {{ activeSegment('categories') }}">
                                    <i class="nav-icon fas fa-list"></i>
                                    <p>{{ __('Categories') }}</p>
                                </a>
                            </li>

                            <li class="nav-item has-treeview">
                                <a href="{{ route('customers.index') }}"
                                    class="nav-link {{ activeSegment('customers') }}">
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
                                <a href="{{ route('floor.restaurant') }}"
                                    class="nav-link {{ activeSegment('floor-restaurant') }}">
                                    <i class="nav-icon fas fa-utensils"></i>
                                    <p>{{ 'Floor & Restaurant' }}</p>
                                </a>
                            </li>
                            <li class="nav-item has-treeview">
                                <a href="{{ route('activities.index') }}"
                                    class="nav-link {{ activeSegment('activities') }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>{{ 'Activities' }}</p>
                                </a>
                            </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-wifi"></i>
                <p>
                    Offline Sync
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
                <li class="nav-item">
                    <a href="{{ route('tablet.order') }}" class="nav-link {{ activeSegment('tablet-order') }}">
                        <i class="nav-icon fas fa-tablet-alt"></i>
                        <p>Tablet Order</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sync.status') }}" class="nav-link {{ activeSegment('sync-status') }}">
                        <i class="nav-icon fas fa-sync"></i>
                        <p>Sync Status</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('conflict.resolution') }}" class="nav-link {{ activeSegment('conflict-resolution') }}">
                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                        <p>Conflicts</p>
                    </a>
                </li>
            </ul>
        </li>
                    {{-- <li class="nav-item has-treeview">
                        <a href="{{ route('expenses.index') }}" class="nav-link {{ activeSegment('expenses') }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{ __('Expenses') }}</p>
                        </a>
                    </li> --}}
                @endif
                <li class="nav-item has-treeview">
                    <a href="{{ route('tokenShop') }}" class="nav-link {{ activeSegment('tokenShop') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>Token Shop</p>
                    </a>
                </li>
                {{-- <li class="nav-item has-treeview">
                    <a href="{{ route('cart.indexTokens') }}" class="nav-link {{ activeSegment('cart') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('cart.title2') }}</p>
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a href="{{ route('orders.index') }}" class="nav-link {{ activeSegment('orders') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('order.title') }}</p>
                    </a>
                </li>
                @if (auth()->user()->type == 'admin' || auth()->user()->type == 'accountant')
                    {{-- <li class="nav-header">Reports</li> --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon far fa-envelope"></i>
                            <p>
                                Reports
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="display: none;">
                            <li class="nav-item">
                                <a href="{{ route('reports.dailySale') }}" class="nav-link">
                                    <i class="nav-icon fas fa-chart-pie"></i>
                                    <p>{{ __('common.Daily_Sale_Report') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.productsReport') }}"
                                    class="nav-link {{ activeSegment('reports') }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>{{ __('common.Products_Report') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.cashiersReport') }}"
                                    class="nav-link {{ activeSegment('reports') }}">
                                    <i class="nav-icon fas fa-cash-register"></i>
                                    <p>{{ __('common.Cashiers_Report') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.chitsReport') }}"
                                    class="nav-link {{ activeSegment('reports') }}">
                                    <i class="nav-icon fas fa-file-invoice"></i>
                                    <p>{{ __('common.Chits_Report') }}</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @elseif(auth()->user()->type == 'cashier')
                    <li class="nav-item">
                        <a href="{{ route('reports.cashiersReport') }}"
                            class="nav-link {{ activeSegment('reports') }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>{{ __('common.Cashiers_Report') }}</p>
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
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
