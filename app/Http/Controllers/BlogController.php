<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\User;

class BlogController extends Controller
{
	protected $limit = 3;

    public function index()
    {

    	$posts = Post::with('author')
	                 ->latestFirst()
	                 ->published()
	                 ->simplePaginate($this->limit);

    	return view("blog.index", compact('posts')); //т.е. в шаблон передаем массивы моделей
    }

	public function category(Category $category)
	{
		$categoryName = $category->title;

		$posts = $category->posts()
		                 ->with('author') //чтобы уменьшить кол-во запросов
		                 ->latestFirst()
		                 ->published()
		                 ->simplePaginate($this->limit);

		return view("blog.index", compact('posts', 'categoryName')); //т.е. в шаблон передаем переменные из того же контроллера
	}

	public function author(User $author)
	{
		$authorName = $author->name;

		$posts = $author->posts()
		                  ->with('category') //чтобы уменьшить кол-во запросов
		                  ->latestFirst()
		                  ->published()
		                  ->simplePaginate($this->limit);

		return view("blog.index", compact('posts', 'authorName'));	}

	public function show(Post $post)
    {
	    //счетчик просмотров постов, для этого способа не нужно указывать поле fillable в модели
	    $post->increment( 'view_count' );

	    //в compact передаем переменую-объект? в шаблон
    	return view("blog.show", compact('post'));
    }
}
