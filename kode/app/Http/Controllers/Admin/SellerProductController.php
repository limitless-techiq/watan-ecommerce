<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Order;

class SellerProductController extends Controller
{

    public function __construct(){
        $this->middleware(['permissions:view_seller']);
    }

    public function index()
    {
        $title = "Seller all products";
        $products = Product::latest()->sellerProduct()->search()->physical()->with('seller', 'category', 'order')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }

    public function new()
    {
        $title = "Seller new products";
        $products = Product::sellerProduct()->physical()->search()->new()->latest()->with('seller', 'category', 'order')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }

    public function approved()
    {
        $title = "Seller approved products";
        $products = Product::sellerProduct()->physical()->search()->published()->latest()->with('seller', 'category', 'order')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }
    
    public function refuse()
    {
        $title = "Seller cancel products";
        $products = Product::sellerProduct()->physical()->search()->inactive()->latest()->with('seller', 'category', 'order')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }

    public function trashed()
    {
        $title = "Trashed products";
        $products = Product::with(['seller','category','order'])->sellerProduct()->search()->onlyTrashed()->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }

    public function details($id)
    {
        $title = "Product details";
        $product = Product::with(['shippingDelivery'=>function($q){
            return $q->with(['shippingDelivery']);
        }])->whereNotNull('seller_id')->where('id', $id)->firstOrFail();
        return view('admin.seller_product.details', compact('title','product'));
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::whereNotNull('seller_id')->where('id', $request->id)->delete();
        return back()->with('success',translate("Seller product has been deleted"));
    }

    public function approvedBy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::sellerProduct()->physical()->where('id', $request->id)->firstOrFail();
        $product->status = Product::PUBLISHED;
        $product->save();
        return back()->with('success',translate("Seller product has been approved"));
    }

    public function cancelBy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::sellerProduct()->physical()->where('id', $request->id)->firstOrFail();
        $product->status = Product::INACTIVE;
        $product->save();
        return back()->with('success',translate("Seller product has been inactive"));
    }

    public function restore(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);
        $product = Product::whereNotNull('seller_id')->onlyTrashed()->where('id', $request->id)->restore();
        return back()->with('success',translate("Seller product has been restore"));
    }



    public function search(Request $request, $scope)
    {
        $request->validate([
            'searchFilter'=>'required',
        ]);

        if($request->option_value == 'Select Menu'){

            return back()->with('error',translate("Please Select A Value Form Select Box"));
        }
        $search = $request->searchFilter;
        $title = "Search by -" . $search;
        $products=Product::with(['category','order','seller'])->sellerProduct()->physical();


        if($request->option_value == 'product_name'){
            $products->Where('name', 'like', "%$search%");
        }
        if($request->option_value == 'category'){
            $products->whereHas('category', function($q) use ($search){
                    $q->where('name','like',"%$search%");
            });
        }
        if($request->option_value == 'brand'){
            $products->whereHas('brand', function($q) use ($search){
                $q->where('name','like',"%$search%");
        });
        }
        if($request->option_value == 'price'){
            $products->Where('price', 'like', "%$search%");
        }

        if($scope == 'new') {
            $products = $products->new();
        }elseif($scope == 'approved') {
            $products = $products->published();
        }elseif($scope == 'refuse'){
            $products = $products->inactive();
        }elseif($scope == 'trashed'){
            $products = $products->onlyTrashed();
        }
        $products = $products->orderBy('id','desc')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title','products','search'));
    }

    public function singleProductAllOrder($id)
    {
        $product = Product::sellerProduct()->physical()->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " all orders log";
        $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($product){
            $q->where('product_id', $product->id);
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('admin.seller_order.index', compact('title', 'orders'));
    }

    public function singleProductPlacedOrder($id)
    {
        $product = Product::sellerProduct()->physical()->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " placed orders log";
        $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($product){
            $q->where('product_id', $product->id)->where('status', 1);
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('admin.seller_order.index', compact('title', 'orders'));
    }

    public function singleProductDeliveredOrder($id)
    {
        $product = Product::sellerProduct()->physical()->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " delivered orders log";
         $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($product){
            $q->where('product_id', $product->id)->where('status', 5);
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('admin.seller_order.index', compact('title', 'orders'));
    }


    public function sellerProductUpdateStatus(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        $product->status = $request->status;
        $product->save();
        return back()->with('success',translate("Product status has been successfully updated"));
    }
}
