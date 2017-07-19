<?php

namespace App\Providers;

use App\Post;
use Illuminate\Support\ServiceProvider;
use App\Category;

class ComposerServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		view()->composer( 'layouts.sidebar', function ( $view ) {
			//счетчик опубликованных статей и вывод меню?
			$categories = Category::with( [
				'posts' => function ( $query ) {
					$query->published();
				}
			] )->orderBy( 'title', 'asc' )->get(); //только get()! all() не будет работать после сортировки!

			return $view->with( 'categories', $categories ); //под каким именем что передаем
		} );

		view()->composer( 'layouts.sidebar', function ( $view ) {
			$popularPosts = Post::published()->popular()->take( 3 )->get();

			//функцию popular определели в модели
			return $view->with( 'popularPosts', $popularPosts );
		} );
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
