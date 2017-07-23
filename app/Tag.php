<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
	public function posts() {
		//поскольку у нас все по конвенции, по соглашению, то достаточно написать только одну модель, Tag тут не обязательно указывать, Laravel сам поймет
		return $this->belongsToMany( Post::class );
	}

	public function getRouteKeyName() {
		return 'slug';
	}
}
