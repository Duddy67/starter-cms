<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\Cms\Setting;


class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('allowto', function ($expression) {
	    return "<?php if (auth()->user()->isAllowedTo($expression)) : ?>";
        });

	Blade::directive('endallowto', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('allowtoany', function ($expression) {
	    return "<?php if (auth()->user()->isAllowedToAny($expression)) : ?>";
        });

	Blade::directive('endallowtoany', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('allowtoall', function ($expression) {
	    return "<?php if (auth()->user()->isAllowedToAll($expression)) : ?>";
        });

	Blade::directive('endallowtoall', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('accessadmin', function () {
	    return "<?php if (auth()->user()->canAccessAdmin()) : ?>";
        });

	Blade::directive('endaccessadmin', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('superadmin', function () {
	    return "<?php if (auth()->user()->isSuperAdmin()) : ?>";
        });

	Blade::directive('endsuperadmin', function () {
            return "<?php endif; ?>";
        });

	/**
	 * @date blade directive
	 * use as @date($object->datefield)
	 * or with a format @date($object->datefield,'m/d/Y')
	 */
	Blade::directive('date', function ($expression) {
	    // Set default format if not present in $expression
	    $default = "'".Setting::getValue('app', 'date_format')."'";

	    $parts = str_getcsv($expression);
	    $parts[1] = (isset($parts[1]))?$parts[1]:$default;

	    return '<?php if(' . $parts[0] . '){ echo e(' . $parts[0] . '->format(' . $parts[1] . ')); } ?>';
	});
    }
}
