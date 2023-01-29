@if ($cookieInfoConfig['enabled'] && ! $cookieInfoAlreadyChecked)

    @include('themes.starter.partials.cookie-info.dialog')

    <script>

        window.laravelCookieInfo = (function () {
            var cookieDialog = new bootstrap.Modal(document.getElementById('cookie-info'), {});
            // Show dialog box by default.
            cookieDialog.show();

            const COOKIE_VALUE = 1;
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            function cookieInfoChecked() {
                setCookie('{{ $cookieInfoConfig['cookie_name'] }}', COOKIE_VALUE, {{ $cookieInfoConfig['cookie_lifetime'] }});
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                cookieDialog.hide();
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));

                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (cookieExists('{{ $cookieInfoConfig['cookie_name'] }}')) {
                hideCookieDialog();
            }
            
            const buttons = document.getElementsByClassName('js-cookie-info-checked');

            for (let i = 0; i < buttons.length; ++i) {
                buttons[i].addEventListener('click', cookieInfoChecked);
            }

            return {
                cookieInfoChecked: cookieInfoChecked,
                hideCookieDialog: hideCookieDialog
            };

        })();
    </script>
@endif
