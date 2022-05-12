@extends ('layouts.admin')

@section ('main')
  @inject ('setting', 'App\Models\Setting')
    <div class="row">
    <div class="col-sm-6">
      <div class="card">
        <div class="card-body">
          <p class="h3">{{ __('messages.dashboard.welcome', ['name' => Auth::user()->name]) }}</p>

          @if (Auth::user()->last_seen_at)
              <p class="card-text">{{ __('messages.dashboard.last_connection', ['date' => $setting::getFormattedDate(Auth::user()->last_seen_at)]) }}</p>
          @endif

          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>
    <div class="card col-sm-6 p-0" style="width: 18rem;">
        <div class="card-header bg-primary">
          <span class="h3">{{ __('messages.dashboard.last_users_logged_in') }}</span>
        </div>
        <ul class="list-group list-group-flush">
            @foreach ($users as $user)
                @if ($user->last_logged_in_at)
                    <li class="list-group-item"><span class="font-weight-bold mr-4">{{ $user->name }}</span> {{ $setting::getFormattedDate($user->last_logged_in_at) }}</li>
                @endif
            @endforeach
        </ul>
      </div>
    </div>
@endsection
