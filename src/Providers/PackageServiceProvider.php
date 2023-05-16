<?php

namespace Prisma\ThemesManager\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Prisma\ThemesManager\Components\Image;
use Prisma\ThemesManager\Components\PageTitle;
use Prisma\ThemesManager\Components\Script;
use Prisma\ThemesManager\Components\Style;
use Prisma\ThemesManager\Console\Commands;
use Prisma\ThemesManager\Console\Generators;
use Prisma\ThemesManager\Facades\ThemesManager as ThemesManagerFacade;
use Prisma\ThemesManager\Http\Middleware;
use Prisma\ThemesManager\ThemesManager;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Name for this package to publish assets.
     */
    protected const PACKAGE_NAME = 'themes-manager';

    /**
     * Pblishers list.
     */
    protected $publishers = [];

    /**
     * Bootstrap the application events.
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom($this->getPath('resources/views'), 'themes-manager');
        $this->loadViewComponentsAs('theme', [
            Image::class,
            PageTitle::class,
            Script::class,
            Style::class,
        ]);

        $this->strapPublishers();
        $this->strapCommands();

        $router->aliasMiddleware('theme', Middleware\ThemeLoader::class);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerConfigs();

        $this->app->singleton('themes-manager', function () {
            return new ThemesManager();
        });

        AliasLoader::getInstance()->alias('ThemesManager', ThemesManagerFacade::class);
        AliasLoader::getInstance()->alias('Theme', ThemesManagerFacade::class);

        $this->app->register(BladeServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ThemesManager::class];
    }

    /**
     * Get Package absolute path.
     *
     * @param string $path
     */
    protected function getPath($path = '')
    {
        // We get the child class
        $rc = new \ReflectionClass(get_class($this));

        return dirname($rc->getFileName()) . '/../../' . $path;
    }

    /**
     * Get Module normalized namespace.
     *
     * @param mixed $prefix
     */
    protected function getNormalizedNamespace($prefix = '')
    {
        return Str::start(Str::lower(self::PACKAGE_NAME), $prefix);
    }

    /**
     * Bootstrap our Configs.
     */
    protected function registerConfigs()
    {
        $configPath = $this->getPath('config');

        $this->mergeConfigFrom(
            "{$configPath}/config.php",
            $this->getNormalizedNamespace()
        );
    }

    protected function strapCommands()
    {
        if ($this->app->runningInConsole() || 'testing' == config('app.env')) {
            $this->commands([
                Commands\ClearCache::class,
                Commands\ListThemes::class,
                Generators\MakeTheme::class,
            ]);
        }
    }

    /**
     * Bootstrap our Publishers.
     */
    protected function strapPublishers()
    {
        $configPath = $this->getPath('config');

        $this->publishes([
            "{$configPath}/config.php" => config_path($this->getNormalizedNamespace() . '.php'),
        ], 'config');

        $this->publishes([
            $this->getPath('resources/views') => resource_path('views/vendor/themes-manager'),
        ], 'views');
    }
}
