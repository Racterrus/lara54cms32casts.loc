<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// reset the users table
		DB::statement('SET FOREIGN_KEY_CHECKS=0');
		DB::table('users')->truncate();

		// generate 3 users/author
		$faker = Factory::create();

		DB::table('users')->insert([
			[
				'name'     => "John Doe",
				'slug'     => "john_doe",
				'email'    => "johndoe@test.com",
				'password' => bcrypt( 'secret' ),
				'bio'      => $faker->text( rand( 300, 350 ) )
			],
			[
				'name'     => "Jane Doe",
				'slug'     => "jane_doe",
				'email'    => "janedoe@test.com",
				'password' => bcrypt( 'secret' ),
				'bio'      => $faker->text( rand( 300, 350 ) )
			],
			[
				'name'     => "Edo Masaru",
				'slug'     => "edo_masaru",
				'email'    => "edo@test.com",
				'password' => bcrypt( 'secret' ),
				'bio'      => $faker->text( rand( 200, 300 ) )
			],
		]);
	}
}
