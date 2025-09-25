<?php

namespace MK\Action;

use Illuminate\Support\ServiceProvider;
use MK\Action\Http\Controllers\ActionController;
use MK\Action\Console\MakeActionCommand;

class ActionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ActionRegistry::class);
        $this->app->singleton(ActionManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        
        // Auto-discover actions in the application
        $this->discoverActions();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeActionCommand::class,
            ]);
        }
    }

    /**
     * Discover and register actions from the application.
     */
    protected function discoverActions(): void
    {
        $registry = $this->app->make(ActionRegistry::class);
        
        // Get all classes that extend BaseAction
        $actionClasses = $this->getActionClasses();
        
        foreach ($actionClasses as $actionClass) {
            if (is_subclass_of($actionClass, BaseAction::class)) {
                $registry->register($actionClass);
            }
        }
    }

    /**
     * Get all action classes from the application.
     *
     * @return array<class-string>
     */
    protected function getActionClasses(): array
    {
        $classes = [];
        
        // Scan app directory for actions
        $appPath = app_path();
        if (is_dir($appPath)) {
            $classes = array_merge($classes, $this->scanDirectory($appPath, 'App'));
        }
        
        return $classes;
    }

    /**
     * Scan directory for PHP classes.
     *
     * @param string $directory
     * @param string $namespace
     * @return array<class-string>
     */
    protected function scanDirectory(string $directory, string $namespace): array
    {
        $classes = [];
        
        if (!is_dir($directory)) {
            return $classes;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $className = $namespace . '\\' . str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relativePath);
                
                if (class_exists($className)) {
                    $classes[] = $className;
                }
            }
        }
        
        return $classes;
    }
}
