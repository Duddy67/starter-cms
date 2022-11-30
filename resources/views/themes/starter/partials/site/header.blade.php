<nav class="navbar navbar-expand-md navbar-light bg-light">
    <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>

    @if ($menu)
        <div class="collapse navbar-collapse" id="navbarCollapse">
              <ul class="navbar-nav mr-auto">
                  @foreach ($menu->getMenuItems() as $item)
                      @include ('themes.starter.partials.menu.items')
                  @endforeach
              </ul>
        </div>
    @endif
{{ app()->getLocale() }}
    <div class="me-5">
        @include ('themes.starter.partials.site.locales')
    </div>

    @if (Route::has('login'))
        <div class="hidden fixed me-2 px-6 py-4 sm:block">
            @auth
                <a href="{{ url('/'.app()->getLocale().'/profile') }}" class="text-sm text-gray-700 underline">Profile</a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-700 underline">Log in</a>

                @if (Route::has('register') && $menu->allow_registering)
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
                @endif
            @endauth
        </div>
     @endif
</nav>
