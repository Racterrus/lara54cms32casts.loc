<?php

namespace App\Policies;

use App\User;
use App\Prediction;
use Illuminate\Support\Facades\Gate; //добавлено мной
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class PredictionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //с Хабра :) Тут у нас 'операции'.

    /*public function destroyPrediction(User $user, Prediction $prediction)
    {
        return $user->id == $prediction->author_id || Gate::check('administer-users');
    }
    */

    public function editAllPrediction(User $user, Prediction $prediction)
    {
        return Gate::check('administer-users');
    }

    public function editPrediction(User $user, Prediction $prediction)
    {
        return ($user->id == $prediction->author_id) || Gate::check('administer-users');
    }

    public function destroyPrediction(User $user, Prediction $prediction)
    {
        return (($user->id == $prediction->author_id) && $prediction->created_at > Carbon::now()->subMinutes(config('cms.time_for_edit'))) || Gate::check('administer-users');
    }

    public function indexAllPredictions(User $user) //при провере аргументоа передаем Prediction::class (Prediction - в соответствии с полисом :))
    {
        return Gate::check('administer-users');
    }


}
