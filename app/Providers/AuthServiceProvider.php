<?php

namespace App\Providers;

use App\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Prediction;
use App\Policies\PostPolicy;
use App\Policies\PredictionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Prediction::class => PredictionPolicy::class,
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
        //какую ВОЗМОЖНОСТЬ каким РОЛЯМ мы присваиваем
        Gate::define('administer-users', function ($user) {
            return $user->role == 'manager' || $user->role == 'admin';
        });

        Gate::define('predictor', function ($user) {
            return $user->role == 'predictor';
        });

//	    Gate::define('editPost', function ($user) {
//		    return $user->role == 'manager' || $user->role !== 'admin';
//	    });
    }


}
