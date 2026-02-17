<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img class="user-img -mx-3 min-w-14 min-h-14" src="{{ Storage::url(auth()->user()->image) }}"
                    class="img-circle elevation-2" alt="User">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->getFullname() }}</a>
                <small class="text-muted text-uppercase">{{ auth()->user()->type }}</small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                
                <!-- OPERATIONS -->
                <li class="nav-header text-uppercase text-xs text-muted mt-2">Operations</li>
                
                <!-- Floor Management - Main view for ALL users -->
                <li class="nav-item">
                    <a href="{{ route('floor.management') }}" class="nav-link {{ activeSegment('floor-management') }}">
                        <i class="nav-icon fas fa-th-large"></i>
                        <p>Floor & Tables</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('orders.index') }}" class="nav-link {{ activeSegment('orders') }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Orders</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('orders.workspace') }}" class="nav-link {{ activeSegment('workspace') }}">
                        <i class="nav-icon fas fa-window-restore"></i>
                        <p>Orders Workspace</p>
                    </a>
                </li>

                <!-- MANAGEMENT (Admin Only) -->
                @if (auth()->user()->type == 'admin')
                    <li class="nav-header text-uppercase text-xs text-muted mt-3">Management</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ activeSegment('users') }}">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('shops.index') }}" class="nav-link {{ activeSegment('shops') }}">
                            <i class="nav-icon fas fa-store"></i>
                            <p>Shops</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ activeSegment('categories') }}">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Categories</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link {{ activeSegment('products') }}">
                            <i class="nav-icon fas fa-box-open"></i>
                            <p>Products</p>
                        </a>
                    </li>
                @endif

                <!-- Logout -->
                <li class="nav-item mt-3">
                    <a href="#" class="nav-link" onclick="document.getElementById('logout-form').submit()">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                        </form>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
