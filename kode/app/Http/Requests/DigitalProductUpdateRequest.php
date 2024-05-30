<?php

namespace App\Http\Requests;

use App\Rules\General\FileExtentionCheckRule;
use App\Rules\General\FileLengthCheckRule;
use Illuminate\Foundation\Http\FormRequest;

class DigitalProductUpdateRequest extends FormRequest
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
        return [
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'description' => 'required',
            'featured_image' => ['nullable',new FileExtentionCheckRule(file_format())],           
            'meta_image' => ['nullable','image'],
        ];
    }

    
    public function messages()
    {
       return [
            'name.required' => 'Product title is required',
            'category_id.required' => 'Category is required',
            'description.required' => 'Description is required',
        ];
    }
}
