<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\Frontend\ProductService;
use Illuminate\Support\Facades\Auth;

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
            'id.required' => "Product must be selected",
            'id.exists' => "Product doesn't exist",
        ]);
        
        // إضافة تحقق من حالة المستخدم
        $validator->after(function ($validator) use ($request) {
            $user = Auth::user();
            if ($user->status != 1) {
                $validator->errors()->add('user_status', 'Your account is not verified. Please contact support.');
            }
        });
        
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
        $user=Auth::user();

        $status=$user->status;

        if($status=='0')
        {
            return response()->json(['validation' => translate("Your account has not been verified yet. Contact support.")]);    
        }
        
        $this->validate($request,[
            'id' => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:0',
        ]);
        return $this->productService->updateCartItem($request);
    }

}
