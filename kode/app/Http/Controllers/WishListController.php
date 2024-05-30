<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WishList;
use App\Models\Product;
use App\Models\CompareProductList;
use App\Http\Services\Frontend\ProductService;
class WishListController extends Controller
{

    public $productService;
    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function store(Request $request)
    {
        return $this->productService->wishList($request);
       
    }
    public function wishItemCount()
    {
        return $this->productService->wishListItems();
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:wish_lists,id',
        ]);
        return $this->productService->wishListItemsDelete($request);
    }

    public function compareItemCount()
    {
        return $this->productService->compareCount();
    }
}
