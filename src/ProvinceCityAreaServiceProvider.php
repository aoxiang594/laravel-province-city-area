<?php

namespace Aoxiang\Pca;

use Illuminate\Support\ServiceProvider;

class ProvinceCityAreaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('provincecityarea', function () {
            return $this->app->make('Aoxiang\Pca\ProvinceCityArea');
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->publishes([
//            // 发布配置文件到 laravel 的config 下
//            __DIR__.'/config/province-city-area.php' => config_path('province-city-area.php'),
//        ]);
        // 发布数据库迁移文件到 laravel 的config 下
//        $this->publishes([
//            __DIR__ . '/database/migrations/2019_06_04_222005_create_province_city_area_table.php' => database_path('migrations/2019_06_04_222005_create_province_city_area_table.php'),
//        ], 'migrations');

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        //生成命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\RefreshData::class,
                Commands\ClearData::class,
            ]);
        }
    }
}
