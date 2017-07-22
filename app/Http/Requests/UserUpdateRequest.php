<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserUpdateRequest extends Request {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'name'     => 'required',
			//разрешаем тот же емейл, что уже есть у юзера
			'email'    => 'email|required|unique:users,email,' . $this->route( 'user' ),
			//required_with:password_confirmation означает, что проверяется только поле password_confirmation заполнено, а пустое поле разрешается при обновлении юзера
			'password' => 'required_with:password_confirmation|confirmed'
		];
	}
}
