<?php

namespace App\Support\Livewire;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class LivewireModuleDiscovery
{
    /**
     * Automatically discover and register Livewire components in a module.
     */
    public static function discover(string $moduleName, string $modulePath, string $namespace = 'Modules'): void
    {
        // Try app/Livewire first, then fallback to Livewire
        $livewirePath = $modulePath . '/app/Livewire';
        if (!is_dir($livewirePath)) {
            $livewirePath = $modulePath . '/Livewire';
        }

        if (!is_dir($livewirePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*.php')->in($livewirePath);

        foreach ($finder as $file) {
            // Get relative path from livewirePath
            $relativePath = str_replace([$livewirePath, '.php'], '', $file->getRealPath());
            $relativePath = ltrim(str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath), '\\');

            // Construct Class name
            // For nwidart/laravel-modules, the 'app/' directory maps directly to the module root namespace.
            $className = $namespace . '\\' . $moduleName . '\\Livewire\\' . $relativePath;

            if (class_exists($className)) {
                // Generate a component name like: ca.dashboard-page
                $alias = strtolower($moduleName) . '.' . str_replace('\\', '.', Str::kebab($relativePath));
                Livewire::component($alias, $className);
            }
        }
    }
}
