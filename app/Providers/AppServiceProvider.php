<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    URL::forceRootUrl('https://online-test-vyo8.onrender.com');
    URL::forceScheme('https');
    }
}