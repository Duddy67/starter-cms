<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @inject ('setting', 'App\Models\Setting')
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ (isset($metaData) && !empty($metaData['meta_page_title'])) ? $metaData['meta_page_title'] : $setting::getValue('app', 'name') }}</title>
        @if (isset($metaData))
            @include('themes.starter.partials.site.metadata')
        @endif

        @php $public = url('/'); @endphp
	<!-- Bootstrap CSS file -->
	<link rel="stylesheet" href="{{ asset('/vendor/adminlte/dist/css/adminlte.min.css') }}">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- Starter CMS CSS file -->
	<link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    </head>
    <body>

	<div class="container">
	    <!-- Header -->
	    <header id="layout-header">
                @include('themes.starter.partials.site.header')
	    </header>

	    <!-- Content -->
	    <section id="layout-content" class="pt-4">
                @include('themes.starter.pages.'.$page)
	    </section>

	    <!-- Footer -->
	    <footer id="layout-footer" class="page-footer pt-4">
                @include('themes.starter.partials.site.footer')
	    </footer>
	</div>

    <!-- JS files: jQuery first, then Bootstrap JS -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/parent.menu.links.clickable.js') }}"></script>
    <!-- Adds possible extra js scripts pushed by pages and partials. -->
    @stack ('scripts')
    </body>
</html>
