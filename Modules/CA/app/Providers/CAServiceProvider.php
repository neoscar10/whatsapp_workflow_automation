<?php

namespace Modules\CA\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\CA\Console\Commands\RolloverCompliancesCommand;

class CAServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'CA';

    protected string $moduleNameLower = 'ca';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));

        // Discover and register Livewire Components inside this module
        \App\Support\Livewire\LivewireModuleDiscovery::discover($this->moduleName, module_path($this->moduleName));

        // Register CA Links in Sidebar Registry
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'CA Dashboard',
            'route' => 'ca.dashboard',
            'icon' => 'account_balance',
            'activePattern' => 'ca.dashboard',
            'group' => 'modules',
            'module' => 'ca',
            'order' => 10,
        ]);

        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Automation Library',
            'route' => 'ca.knowledge-base.index',
            'icon' => 'library_books',
            'activePattern' => 'ca.knowledge-base.index',
            'group' => 'modules',
            'module' => 'ca',
            'order' => 10.5,
        ]);
        
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'CA Clients',
            'route' => 'ca.clients.index',
            'icon' => 'group',
            'activePattern' => 'ca.clients.*',
            'group' => 'modules',
            'module' => 'ca',
            'order' => 11,
        ]);

        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Compliance Calendar',
            'route' => 'ca.calendar',
            'icon' => 'calendar_month',
            'activePattern' => 'ca.calendar',
            'group' => 'modules',
            'module' => 'ca',
            'order' => 12,
        ]);

        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'CA Reporting',
            'route' => 'ca.reporting',
            'icon' => 'bar_chart',
            'activePattern' => 'ca.reporting',
            'group' => 'modules',
            'module' => 'ca',
            'order' => 13,
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            RolloverCompliancesCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('ca:rollover-compliances')->dailyAt('00:00');
        });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder','')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
