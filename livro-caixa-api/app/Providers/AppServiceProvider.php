<?php

namespace App\Providers;

use App\Models\Account;
use Illuminate\Http\Request;
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
        Request::macro('currentAccount', function (): ?Account {
            /** @var Request $this */
            return $this->attributes->get('current_account');
        });
    }
}
