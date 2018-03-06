<?php

namespace App\Policies;

use App\User;
use App\Post;
use Illuminate\Support\Facades\Gate; //добавлено мной
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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

    //с Хабра :)
    public function destroyPost(User $user, Post $post)
    {
        return $user->id == $post->author_id || Gate::check('administer-users');
    }

    public function editPost(User $user, Post $post)
    {
        return $user->id == $post->author_id || Gate::check('administer-users');
    }

    public function indexAllPosts(User $user) //при провере аргументоа передаем Post::class (Post - в соответствии с полисом :))
    {
        return Gate::check('administer-users');
    }


}
