<?php

use Illuminate\Database\Seeder;
use App\Tag;
use App\Post;

class TagsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table( 'tags' )->truncate();

		$php       = new Tag();
		$php->name = "PHP";
		$php->slug = "php";
		$php->save();

		$laravel       = new Tag();
		$laravel->name = "Laravel 5";
		$laravel->slug = "laravel-5";
		$laravel->save();

		$joomla       = new Tag();
		$joomla->name = "Joomla";
		$joomla->slug = "joomla";
		$joomla->save();

		$vue       = new Tag();
		$vue->name = "Vue jS";
		$vue->slug = "vuejs";
		$vue->save();

		$tags = [
			$php->id,
			$laravel->id,
			$joomla->id,
			$vue->id
		];

		foreach ( Post::all() as $post ) {
			shuffle( $tags );

			for ( $i = 0; $i < rand( 0, count( $tags ) - 1 ); $i ++ ) {
				$post->tags()->detach( $tags[ $i ] );
				$post->tags()->attach( $tags[ $i ] );
			}
		}

	}
}
