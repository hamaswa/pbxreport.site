<?php
namespace App\Providers;


use App\Permission;
use Illuminate\Support\Facades\Blade;
use Illuminate\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Permission::get()->map(function($permission){
            //try {
                Gate::define($permission->slug, function ($user) use ($permission) {
                    return $user->hasPermissionTo($permission);
                });
           // } catch (\Exception $e){
           //     return [];
           // }
        });

        //Blade directives
        Blade::directive('role', function ($role){
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})) : ?>";
        });
        Blade::directive('elserole', function ($role){
            return "<?php elseif(auth()->check() && auth()->user()->hasRole({$role})) : ?>";
        });

        Blade::directive('endrole', function ($role){
            return "<?php endif; ?>";
        });

    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}