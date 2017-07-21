<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [
	'uses' => 'BlogController@index',
	'as' => 'blog'
]);

Route::get('/blog/{post}', [
	'uses' => 'BlogController@show',
	'as'   => 'blog.show'
] );

Route::get( '/category/{category}', [ //в модели вместо id указали слаг
	'uses' => 'BlogController@category', //контроллер и его метод
	'as'   => 'category' //шаблон
] );

Route::get( '/author/{author}', [ //в модели вместо id указали слаг
	'uses' => 'BlogController@author', //контроллер и его метод
	'as'   => 'author' //шаблон
] );

Route::resource( '/backend/blog', 'Backend\BlogController', [
	'names' => [
		'create'  => 'backend.blog.create',
		'index'   => 'backend.blog.index',
		'edit'    => 'backend.blog.edit',
		'destroy' => 'backend.blog.destroy',
	]
]);


Auth::routes();

Route::get( '/home', 'Backend\HomeController@index' )->name( 'home' );
