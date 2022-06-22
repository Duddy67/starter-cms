<nav class="navbar navbar-expand-md navbar-light bg-light">
  @if ($menu = $menu::getMenu('main-menu'))
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
	    <ul class="navbar-nav mr-auto">
		@foreach ($menu->getMenuItems() as $item)
		    @include ('themes.starter.partials.menu.items')
		@endforeach
	    </ul>
      </div>
  @endif

  @if (Route::has('login'))
      <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
	  @auth
	      <a href="{{ url('/profile') }}" class="text-sm text-gray-700 underline">Profile</a>
	  @else
	      <a href="{{ route('login') }}" class="text-sm text-gray-700 underline">Log in</a>

	      @if (Route::has('register'))
		  <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
	      @endif
	  @endauth
      </div>
   @endif
</nav>

