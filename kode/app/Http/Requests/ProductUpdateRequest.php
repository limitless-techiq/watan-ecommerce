<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\General\FileExtentionCheckRule;
use App\Rules\General\FileLengthCheckRule;
class ProductUpdateRequest extends FormRequest
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
            'price' => 'required|numeric|gt:-1',
            'discount_percentage' => 'nullable|numeric|gt:-1',
            'minimum_purchase_qty' => 'required|integer|min:1',
            'maximum_purchase_qty' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'short_description' => 'required',
            'description' => 'required',
            'featured_image' => ['nullable',new FileExtentionCheckRule(file_format())],
            'gallery_image.*' => ['nullable',new FileExtentionCheckRule(file_format())],
            'shipping_delivery_id' => 'required',
            'meta_title' => 'nullable|max:250',
            'meta_keywords.*' => 'nullable|max:250',
            'meta_description' => 'nullable|max:500',
            'choice_no' => 'required',
        ];

    }

    
    public function messages()
    {
       return [
            'name.required' => 'Product title is required',
            'price.required' => 'Product Regular price is required',
            'minimum_purchase_qty.required' => 'Minimum Purchase Quantity is Required',
            'maximum_purchase_qty.required' => 'Maximum Purchase Quantity is Required',
            'category_id.required' => 'Category is required',
            'short_description.required' => 'Short Description is required',
            'description.required' => 'Description is required',
            'shipping_delivery_id.required' => 'Product Shipping is required',
            'choice_no.required' => 'Product Stock is Required',
        ];
    }
}
