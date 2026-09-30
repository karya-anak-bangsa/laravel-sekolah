<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Dasar untuk ServiceProvider tiap modul di app/Modules.
 *
 * Mendaftarkan migrations, views (dengan namespace), dan routes milik modul
 * secara otomatis bila foldernya ada. Modul cukup menentukan namespace view-nya.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** Namespace view, mis. "ppdb" untuk pemanggilan view('ppdb::index'). */
    abstract protected function viewNamespace(): string;

    public function boot(): void
    {
        $path = $this->modulePath();

        if (is_dir($path.'/Database/Migrations')) {
            $this->loadMigrationsFrom($path.'/Database/Migrations');
        }

        if (is_dir($path.'/resources/views')) {
            $this->loadViewsFrom($path.'/resources/views', $this->viewNamespace());
        }

        if (is_file($path.'/routes/web.php')) {
            Route::middleware('web')->group($path.'/routes/web.php');
        }
    }

    /** Folder modul, diturunkan dari lokasi file provider (Modul/Providers/X.php). */
    protected function modulePath(): string
    {
        return dirname((new ReflectionClass($this))->getFileName(), 2);
    }
}
