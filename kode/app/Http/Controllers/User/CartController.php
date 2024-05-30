<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\Frontend\ProductService;
class CartController extends Controller
{
    public $productService;
    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:products,id',
        ],[
            'product_id.required' => "Product must be selected",
            'product_id.exists' => "Product doesn't exists",
        ]);
        
        if ($validator->fails()) {
            return response()->json(['validation' => $validator->errors()]);
        }

        $response = $this->productService->addToCart($request);
        return response()->json($response);
    }

    public function getCartData()
    {
        $response = $this->productService->getCart();
        return view('frontend.partials.cart_item', [
            'items' => $response['latest_item'],
            'subtotal' => $response['sub_total'],
        ]);
    }

    public function totalCartAmount(){
        $response = $this->productService->getCart();
        return  short_amount($response['sub_total']);
    }

    public function cartTotalItem()
    {
       return $this->productService->totalCartData();
    }

    public function delete(Request $request)
    {
        return $this->productService->deleteCartItem($request);
    }

    public function viewCart()
    {
        $title = 'Shopping Cart';
        $items = $this->productService->getCartItem();

        if(request()->ajax()){
            return [
                'html' => view('frontend.ajax.cart_list', compact('title', 'items'))->render()
            ];
        }
        return view('frontend.view_cart', compact('title', 'items'));
    }


    public function updateCart(Request $request)
    {
        $this->validate($request,[
            'id' => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:0',
        ]);
        return $this->productService->updateCartItem($request);
    }

}
