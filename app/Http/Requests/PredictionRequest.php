<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PredictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|unique:predictions,title|max:255',
            //поля SLUG - пока нет! Значит, проверять пока не будем, иначе может быть ошибка!
            //'slug'         => 'required|unique:predictions,slug',
            'text' => 'required',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
            'deadline' => 'required|date_format:Y-m-d',
            'confirmed' => 'nullable|in:YES,NO,NULL',
            //'category_id'  => 'required',
            //'image'        => 'nullable|mimes:jpg,jpeg,bmp,png',
            'proof' => 'nullable|unique:predictions,proof'
        ];

        switch ($this->method()) {
            //типы запросов
            case 'PUT':
            case 'PATCH':
                //если данные равны данным обновляемого поста, то он проходият валидацию! Тут $this->route( 'prediction' ) - это id в роуте, см. php artisan route:list
                $rules['title'] = 'required|unique:predictions,title,' . $this->route('prediction');
                $rules['proof'] = 'nullable|unique:predictions,proof,' . $this->route('prediction');
                //$rules['slug'] = 'nullable|unique:predictions,slug,' . $this->route( 'prediction' );
                break;
        }

        return $rules;
    }
}
