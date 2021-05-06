<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
    <img src="{{asset('img/logo.png')}}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">{{config("app.name")}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
        <img src="{{asset('img/user2-160x160.jpg')}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
        <a href="#" class="d-block">Alexander Pierce</a>
        </div>
    </div>

    <!-- SidebarSearch Form -->
    <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
            <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
            </button>
        </div>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
            with font-awesome or any other icon font library -->
            @foreach($menus as $menu)
            <li class="nav-item">
                <a href="#" class="nav-link">
                <i class="nav-icon {{$menu->icon}}"></i>
                <p>
                    {{$menu->title}}
                    <i class="right"></i>
                </p>
                </a>
                @if(count($menu->childs))
                @foreach($menu->childs as $child)
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{route($child->url)}}" class="nav-link {{ setActive($child->url) }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{$child->title}}</p>
                            </a>
                        </li>
                    </ul>
                @endforeach
                @endif
            </li>
            @endforeach
            
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>