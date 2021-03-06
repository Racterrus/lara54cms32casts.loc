<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Post;
use App\Category;
use App\User;
use App\Tag;

class BlogController extends Controller
{
    protected $limit = 4;

    public function index()
    {
	    $posts = Post::with( 'author', 'tags', 'category' )
		    //без этих 'author', 'tags',  'category' будет больше запросов к БД
		             ->latestFirst()
	                 ->published()
            ->paginate($this->limit);

        return view("front.blog.index", compact('posts'));
    }

	public function category( Category $category ) {
		$categoryName = $category->title;

		$posts = $category->posts()
		                  ->with( 'author', 'tags', 'category' )
		                  ->latestFirst()
		                  ->published()
            ->paginate($this->limit);

        return view("front.blog.index", compact('posts', 'categoryName'));
	}

	public function tag( Tag $tag ) {
		$categoryName = $tag->title;

		$posts = $tag->posts()
		             ->with( 'author', 'category' )
		             ->latestFirst()
		             ->published()
            ->paginate($this->limit);

        return view("front.blog.index", compact('posts', 'tagName'));
	}

	public function author( User $author ) {
		$authorName = $author->name;

		$posts = $author->posts()
		                ->with( 'category', 'tags', 'author' )
		                ->latestFirst()
		                ->published()
            ->paginate($this->limit);

        return view("front.blog.index", compact('posts', 'authorName'));
	}

	public function show( Post $post )
    {
	    $post->increment( 'view_count' );

        $postComments = $post->comments()->simplePaginate(3);

        return view("front.blog.show", compact('post', 'postComments'));
    }
}