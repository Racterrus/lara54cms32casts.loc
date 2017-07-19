<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

	public function show(Post $post)
    {


	    //в compact передаем переменые в шаблон
    	return view("blog.show", compact('post'));
    }
}
