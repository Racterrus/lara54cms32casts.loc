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
    	$categories = Category::with(['posts'=> function($query){
    		$query->published();
	    }])->orderBy('title', 'asc')->get(); //только get()! all() не будет работать после сортировки!

    	$posts = Post::with('author')
	                 ->latestFirst()
	                 ->published()
	                 ->simplePaginate($this->limit);

    	return view("blog.index", compact('posts', 'categories')); //т.е. в шаблон передаем массивы моделей
    }

	public function category($id)
	{
		//счетчик статей
		$categories = Category::with(['posts'=> function($query){
			$query->published();
		}])->orderBy('title', 'asc')->get(); //только get()! all() не будет работать после сортировки!
		//конец счетчика

		$posts = Post::with('author')
		             ->latestFirst()
		             ->published()
		             ->where('category_id', $id) //фильтруем по категории
		             ->simplePaginate($this->limit);

		return view("blog.index", compact('posts', 'categories')); //т.е. в шаблон передаем массивы моделей
	}

	public function show(Post $post)
    {
    	return view("blog.show", compact('post'));
    }
}
