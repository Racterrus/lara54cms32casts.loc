<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Requests\CommentStoreRequest;
use App\Comment;

class CommentsController extends Controller
{
    public function store(Post $post, CommentStoreRequest $request)
    {
//        $data = $request->all();
//        $data['post_id'] = $post->id;
//
//        Comment::create($data);
        //можно так, как закомментировано выше

        //$post->comments()->create($request->all()); //а можно и так :)

        //а еше можно так, как ниже, написав медод в моделе Post
        $post->createComment($request->all());

        return redirect()->back()->with('message', 'Ваш комментарий успешно отправлен.');
    }
}
