<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CategoryDestroyRequest extends Request {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		//перенос статей из удаляемой категории в категорию, указанную в настройках cms.default_category_id, и запрет удаления этой самой категории
		return ! ( $this->route( 'category' ) == config( 'cms.default_category_id' ) );
	}

	public function forbiddenResponse() {
		return redirect()->back()->with( 'error-message', 'You cannot delete default category!' );
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			//
		];
	}
}
