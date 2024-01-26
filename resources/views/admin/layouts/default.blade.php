<!DOCTYPE html>
<html lang="en">

<head>
    @inject('setting', 'App\Models\Cms\Setting')
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | {{ $setting::getValue('app', 'name', 'Starter CMS') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <!-- Select2 plugin style -->
    <link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/select2/css/select2.min.css') }}">
    <!-- Custom style -->
    <link rel="stylesheet" href="{{ asset('/css/admin/style.css') }}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"/>
    <!-- Additional style sheets -->
    @stack ('style')
</head>

<body>
    @php $appName = ($setting::getValue('app', 'name')) ? $setting::getValue('app', 'name') : config('app.name', 'Starter CMS'); @endphp
    @php $routeName = request()->route()->getName(); @endphp
    <div class="wrapper">
        <aside id="sidebar" class="js-sidebar sidebar-disabled">
            <!-- Content For Sidebar -->
            <div class="h-100">
                <div class="sidebar-logo">
                    <a href="#">{{ $appName }}</a>
                </div>
                <ul class="sidebar-nav">
                    @php $active = (request()->is('admin')) ? 'active' : '' @endphp
                    <li class="sidebar-main-item dashboard {{ $active }}">
                        <a href="{{ route('admin') }}" class="sidebar-link">
                            <i class="fa-solid fa-chart-line pe-2"></i>
                            @lang ('labels.title.dashboard')
                        </a>
                    </li>

                    @allowto('create-users')
                    @php $active = (request()->is('admin/users*')) ? 'active' : '' @endphp
                    <li class="sidebar-item">
                        <div class="sidebar-main-item {{ $active }}">
                            <a href="#" class="sidebar-link collapsed" data-bs-target="#auth" data-bs-toggle="collapse"
                                aria-expanded="false"><i class="fa-solid fa-users pe-2"></i>
                                @lang ('labels.title.user_management')
                            </a>
                        </div>
                        @php $show = ($active) ? 'show' : '' @endphp
                        <ul id="auth" class="sidebar-dropdown list-group list-group-flush list-unstyled collapse {{ $show }}" data-bs-parent="#sidebar">
                        @php $active = ($routeName == 'admin.users.index' || $routeName == 'admin.users.create' || $routeName == 'admin.users.edit') ? 'active' : '' @endphp
                            <li class="list-group-item list-group-item-action {{ $active }}">
                                <a href="{{ route('admin.users.index') }}" class="sidebar-link">@lang ('labels.title.users')</a>
                            </li>
                            @allowto('create-user-groups')
                                @php $active = (request()->is('admin/users/groups*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.users.groups.index') }}" class="sidebar-link">@lang ('labels.title.groups')</a>
                                </li>
                            @endallowto
                            @allowto('create-user-roles')
                                @php $active = (request()->is('admin/users/roles*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.users.roles.index') }}" class="sidebar-link">@lang ('labels.title.roles')</a>
                                </li>
                            @endallowto
                            @if (auth()->user()->hasRole('super-admin'))
                                @php $active = (request()->is('admin/users/permissions*')) ? 'active ': '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.users.permissions.index') }}" class="sidebar-link">@lang ('labels.title.permissions')</a>
                                </li>
                            @endallowto
                        </ul>
                    </li>
                    @endallowto

                    @allowto('create-posts')
                    @php $active = (request()->is('admin/posts*')) ? 'active' : '' @endphp
                    <li class="sidebar-item">
                        <div class="sidebar-main-item {{ $active }}">
                            <a href="#" class="sidebar-link collapsed" data-bs-target="#blog" data-bs-toggle="collapse"
                                aria-expanded="false"><i class="fa-solid fa-pen pe-2"></i>
                                @lang ('labels.title.blog')
                            </a>
                        </div>
                        @php $show = ($active) ? 'show' : '' @endphp
                        <ul id="blog" class="sidebar-dropdown list-group list-group-flush list-unstyled collapse {{ $show }}" data-bs-parent="#sidebar">
                            @php $active = ($routeName == 'admin.posts.index' || $routeName == 'admin.posts.create' || $routeName == 'admin.posts.edit') ? 'active': '' @endphp
                            <li class="list-group-item list-group-item-action {{ $active }}">
                                <a href="{{ route('admin.posts.index') }}" class="sidebar-link">@lang ('labels.title.posts')</a>
                            </li>
                            @allowto('create-post-categories')
                                @php $active = (request()->is('admin/posts/categories*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.posts.categories.index') }}" class="sidebar-link">@lang ('labels.title.categories')</a>
                                </li>
                            @endallowto
                            @allowto('post-settings')
                                @php $active = (request()->is('admin/posts/settings*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.posts.settings.index') }}" class="sidebar-link">@lang ('labels.title.settings')</a>
                                </li>
                            @endallowto
                        </ul>
                    </li>
                    @endallowto

                    @allowto('create-menus')
                    @php $active = (request()->is('admin/menus*') || request()->is('admin/*/menus*')) ? 'active' : '' @endphp
                    <li class="sidebar-item">
                        <div class="sidebar-main-item {{ $active }}">
                            <a href="#" class="sidebar-link collapsed" data-bs-target="#menus" data-bs-toggle="collapse"
                                aria-expanded="false"><i class="fa-solid fa-list pe-2"></i>
                                @lang ('labels.title.menus')
                            </a>
                        </div>
                        @php $show = ($active) ? 'show' : '' @endphp
                        <ul id="menus" class="sidebar-dropdown list-group list-group-flush list-unstyled collapse {{ $show }}" data-bs-parent="#sidebar">
                            @php $active = ($routeName == 'admin.menus.index' || $routeName == 'admin.menus.create' || $routeName == 'admin.menus.edit') ? 'active' : '' @endphp
                            <li class="list-group-item list-group-item-action {{ $active }}">
                                <a href="{{ route('admin.menus.index') }}" class="sidebar-link">@lang ('labels.title.menus')</a>
                            </li>

                            @inject ('menu', 'App\Models\Menu')
                            @foreach ($menu::getCurrentUserMenus() as $menu)
                                @php $active = ($routeName == 'admin.menus.items.index' || $routeName == 'admin.menus.items.create' || $routeName == 'admin.menus.items.edit') ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.menus.items.index', $menu->code) }}" class="sidebar-link">{{ $menu->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endallowto

                    @allowtoany(['global-settings', 'post-settings', 'update-emails'])
                    @php $active = (request()->is('admin/cms/settings*') || request()->is('admin/cms/emails*')) ? 'active' : '' @endphp
                    <li class="sidebar-item">
                        <div class="sidebar-main-item {{ $active }}">
                            <a href="#" class="sidebar-link collapsed" data-bs-target="#cms" data-bs-toggle="collapse"
                                aria-expanded="false"><i class="fa-solid fa-gears pe-2"></i>
                                @lang ('labels.title.cms')
                            </a>
                        </div>
                        @php $show = ($active) ? 'show' : '' @endphp
                        <ul id="cms" class="sidebar-dropdown list-group list-group-flush list-unstyled collapse {{ $show }}" data-bs-parent="#sidebar">
                            @allowto('global-settings')
                                @php $active = (request()->is('admin/cms/settings*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.cms.settings.index') }}" class="sidebar-link">@lang ('labels.title.settings')</a>
                                </li>
                            @endallowto

                            @allowto('update-emails')
                                @php $active = (request()->is('admin/cms/emails*')) ? 'active' : '' @endphp
                                <li class="list-group-item list-group-item-action {{ $active }}">
                                    <a href="{{ route('admin.cms.emails.index') }}" class="sidebar-link">@lang ('labels.title.emails')</a>
                                </li>
                            @endallowto
                        </ul>
                    </li>
                    @endallowto

                    @php $active = (request()->is('admin/files*')) ? 'active' : '' @endphp
                    <li class="sidebar-main-item files {{ $active }}">
                        <a href="{{ route('admin.files.index') }}" class="sidebar-link">
                            <i class="fa-solid fa-file-lines pe-2"></i>
                            @lang ('labels.title.files')
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand px-3 border-bottom navbar-disabled">
                <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse navbar" id="navbar">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="{{ asset(Auth::user()->getThumbnail()) }}" class="avatar img-fluid rounded" alt="User Image">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item">Profile</a>
                                <a href="#" class="dropdown-item">Setting</a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                 {{ __('Logout') }}</a>
                            </div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content px-3 py-2">
                <div class="container-fluid">
                    @include('admin.partials.flash-message')
                    @yield('main')

                    <div class="ajax-progress d-none" id="ajax-progress">
                        <img src="{{ asset('/images/progress-icon.gif') }}" class="progress-icon" />
                    </div>
                </div>
            </main>
            <footer class="footer border-top">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start mt-2">
                            <p class="mb-0">
                                <a href="#" class="text-muted">
                                    <strong>CodzSwod</strong>
                                </a>
                            </p>
                        </div>
                        <div class="col-6 text-end">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="#" class="text-muted">Contact</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-muted">About Us</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-muted">Terms</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-muted">Booking</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Select2 Plugin -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/script.js') }}"></script>
    <!-- Additional js scripts -->
    @stack('scripts')
</body>

</html>
