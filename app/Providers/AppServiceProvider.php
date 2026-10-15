<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.simas');
        Paginator::defaultSimpleView('vendor.pagination.simas');

        // Format tanggal Indonesia yang konsisten di seluruh tampilan.
        Blade::directive('tanggal', function (string $expression) {
            return "<?php echo e(($expression) ? ($expression)->translatedFormat('d M Y') : '-'); ?>";
        });

        Blade::directive('tanggaljam', function (string $expression) {
            return "<?php echo e(($expression) ? ($expression)->translatedFormat('d M Y, H:i') : '-'); ?>";
        });
    }
}
