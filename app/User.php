<?php

namespace App;

//use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use GrahamCampbell\Markdown\Facades\Markdown;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function posts()
    {
    	return $this->hasMany(Post::class, 'author_id');
    }

	public function gravatar( $value = '' ) {
		$email   = $this->email;
		$default = "http://avatar.img.digart.pl/data/avatar/p/365312";
		$size    = 50;

		return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
	}

	public function getBioHtmlAttribute( $value ) {
		return $this->bio ? Markdown::convertToHtml( e( $this->bio ) ) : null;
	}

    public function getRouteKeyName() { //не путать имя функции!!!
	    return 'slug';
    }

	//Мутатор, хеширует пароль, прежде чем отправить его в БД
	public function setPasswordAttribute( $value ) {
		if ( ! empty( $value ) ) {
			$this->attributes['password'] = bcrypt( $value );
		}
	}
}
