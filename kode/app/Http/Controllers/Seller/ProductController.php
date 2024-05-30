<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\ProductStock;
use App\Models\ProductImage;
use App\Models\ShippingDelivery;
use App\Models\ProductShippingDelivery;
use App\Http\Utility\ProductGallery;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\DigitalProductAttributeValue;
use App\Models\PlanSubscription;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('sellercheckstatus');
    }

    public function index()
    {
        $title = "Manage product";
        $seller = Auth::guard('seller')->user();
        $products = Product::search()->sellerProduct()->physical()->where('seller_id', $seller->id)->orderBy('id', 'DESC')->with('category', 'brand', 'subCategory', 'order')->paginate(paginate_number());

        return view('seller.product.index', compact('title', 'products'));
    }

    public function approved()
    {
        $title = "Manage product";
        $seller = Auth::guard('seller')->user();
        $products = Product::search()->sellerProduct()->physical()->published()->where('seller_id', $seller->id)->orderBy('id', 'DESC')->with('category', 'brand', 'subCategory', 'order')->paginate(paginate_number());
        return view('seller.product.index', compact('title', 'products'));
    }

    public function refuse()
    {
        $title = "Manage product";
        $seller = Auth::guard('seller')->user();
        $products = Product::search()->sellerProduct()->physical()->inactive()->where('seller_id', $seller->id)->orderBy('id', 'DESC')->with(['category', 'brand', 'subCategory', 'order'])->paginate(paginate_number());
        return view('seller.product.index', compact('title', 'products'));
    }

    public function trashed()
    {
        $title = "Manage product";
        $seller = Auth::guard('seller')->user();
        $products = Product::with(['order'])->search()->sellerProduct()->onlyTrashed()->where('seller_id', $seller->id)->orderBy('id', 'DESC')->with('category', 'brand', 'subCategory')->paginate(paginate_number());
        return view('seller.product.index', compact('title', 'products'));
    }

    public function create()
    {
        $attributes = Attribute::with('value')->get();
        $title = "Add new physical product";
        $brands = Brand::where('status', '1')->select('id', 'name')->get();
        $categories = Category::where('status', '1')->select('id', 'name')->with('parent')->get();
        $shippingDeliveries = ShippingDelivery::get(['name','id']);
        return view('seller.product.create', compact('title', 'categories', 'brands', 'attributes', 'shippingDeliveries'));
    }

    public function store(ProductRequest $request)
    {


     
       $subscription = PlanSubscription::where('seller_id',Auth::guard('seller')->user()->id)->where('status',1)->first();
       if(!$subscription){
          return back()->with('error',translate('You dont have any runnig subscription'));
       }
       if($subscription->total_product < 1 ){
           return back()->with('error',translate('You dont have enough product balance to add a new product'));
       }

       $featuredImage = null;
       
      
        if($request->hasFile('featured_image')){
            try {
                $featuredImage = store_file($request->featured_image,file_path()['product']['featured']['path']);
            }catch (\Exception $exp) {
   
            }
        }
        $seller = Auth::guard('seller')->user();
        $product = Product::create([
            'name'=> $request->name,
            'slug'=> make_slug($request->name),
            'seller_id' => $seller->id,
            'product_type' => Product::PHYSICAL,
            'price'=> $request->price,
            'discount'=> $request->discount_percentage ? $request->price - ($request->price * $request->discount_percentage / 100) : null,
            'discount_percentage'=> $request->discount_percentage ?? null,
            'minimum_purchase_qty'=> $request->minimum_purchase_qty,
            'maximum_purchase_qty'=> $request->maximum_purchase_qty,
            'brand_id'=> $request->brand_id ?? null,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'short_description'=> build_dom_document( $request->short_description,'seller_short_descripiton'.rand(10,1000)),
            'description'=> build_dom_document($request->description,'seller_descripiton'.rand(10,1000)),
            'shipping_country'=> $request->shipping_country,
            'featured_image'=> $featuredImage,
            'meta_title'=> $request->meta_title,
            'meta_image'=> $featuredImage,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description,
            'warranty_policy'=> $request->warranty_policy,
            'status'=> Product::NEW,
        ]);

        $collection = collect( $request);

        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                foreach ($collection[$str] as $key => $eachValue) {
                    array_push($attribute_data, $eachValue);
                }
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }
        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }
        $product->attributes_value =   $choice_options;
        $product->attributes =   $attributes;
        $product->save();

        if($request->hasFile('gallery_image')){
            $galleryImage = array_filter($request->gallery_image);
            ProductGallery::imageStore($request, $galleryImage, $product->id);
        }

        if($request->shipping_delivery_id){
            if($request->shipping_delivery_id[0] == 0){
                $shippingDeliveries = ShippingDelivery::pluck('id');

                foreach($shippingDeliveries as $value){
                    ProductShippingDelivery::create([
                        'product_id' => $product->id,
                        'shipping_delivery_id' => $value
                    ]);
                }
            }
            else{
                foreach($request->shipping_delivery_id as $value){
                    ProductShippingDelivery::create([
                        'product_id' => $product->id,
                        'shipping_delivery_id' => $value
                    ]);
                }
            }
        }


        $this->stockStore($request->only([
            'choice_no','product_id'
       ]), $product);


       $subscription->total_product -=1;

       $subscription->save();

 

      
        return back()->with('success', translate("Product has been created"));

    }

    public function details($slug,$id)
    {
        $title = "Product details";
        $seller = Auth::guard('seller')->user();
        $product = Product::with(['rating', 'rating.customer'])->sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        return view('seller.product.details', compact('title', 'product'));
    }


    public function edit($slug,$id)
    {
        $title = "Product update";
        $seller = Auth::guard('seller')->user();
        $brands = Brand::where('status', '1')->select('id', 'name')->get();
        $shippingDeliveries = ShippingDelivery::get(['name','id']);
        $categories = Category::where('status', '1')->select('id', 'name')->with('parent')->get();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        return view('seller.product.edit', compact('title', 'categories', 'brands', 'shippingDeliveries', 'product'));
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        $featuredImage = $product->featured_image;
      
        if($request->hasFile('featured_image')){
            try {
                $featuredImage = store_file($request->featured_image,file_path()['product']['featured']['path'],file_path()['product']['featured']['size'], $featuredImage);
            }catch (\Exception $exp){
             
            }
        }
        $product->update([
            'name'=> $request->name,
            'seller_id' => $seller->id,
            'product_type' => Product::PHYSICAL,
            'price'=> $request->price,
            'discount'=> $request->discount_percentage ? $request->price - ($request->price * $request->discount_percentage / 100) : null,
            'discount_percentage'=> $request->discount_percentage ?? null,
            'minimum_purchase_qty'=> $request->minimum_purchase_qty,
            'maximum_purchase_qty'=> $request->maximum_purchase_qty,
            'brand_id'=> $request->brand_id,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'short_description'=> build_dom_document( $request->short_description,'seller_short_descripiton_edit'.rand(10,1000)),
            'description'=> build_dom_document($request->description,'seller_descripiton_edit'.rand(10,1000)),
            'shipping_country'=> $request->shipping_country,
            'featured_image'=> $featuredImage,
            'status'=> $request->status,
            'meta_title'=> $request->meta_title,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description,
            'warranty_policy'=> $request->warranty_policy,
            'meta_image'=> $featuredImage,
            'status'=> Product::NEW,
        ]);

        if($request->hasFile('gallery_image')){
            $galleryImage = array_filter($request->gallery_image);
            ProductGallery::imageStore($request, $galleryImage, $product->id);
        }
        ProductShippingDelivery::where('product_id',$product->id)->delete();

        if($request->shipping_delivery_id){
            if($request->shipping_delivery_id[0] == 0){
                $shippingDeliveries = ShippingDelivery::pluck('id');

                foreach($shippingDeliveries as $value){
                    ProductShippingDelivery::create([
                        'product_id' => $product->id,
                        'shipping_delivery_id' => $value
                    ]);
                }
            }
            else{
                foreach($request->shipping_delivery_id as $value){
                    ProductShippingDelivery::create([
                        'product_id' => $product->id,
                        'shipping_delivery_id' => $value
                    ]);
                }
            }
        }

        $collection = collect( $request);

        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                foreach ($collection[$str] as $key => $eachValue) {
                    array_push($attribute_data, $eachValue);
                }
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }
        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }
        $product->attributes_value =   $choice_options;
        $product->attributes =   $attributes;
        $product->save();
        foreach ($product->stock as $key => $stock) {
            $stock->delete();
        }

        $this->stockStore($request->only([
            'choice_no','product_id'
        ]), $product);




        return back()->with('success', translate("Product has been updated"));

    }


    public function stock($id)
    {
        $title = "Product stock update";
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        return view('seller.product.stock', compact('title', 'product'));
    }

    public function productStockUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'stock' => 'required',
        ]);
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        foreach($request->name as $key => $value){
            foreach($request->name[$key] as $okey => $stockValue){
                if(ctype_digit($request->stock[$key][$okey])){
                    continue;
                }else{
                    return back()->with('error',translate("Product stock value must be integer."));
                }
            }
        }
        $productDelete = ProductStock::where('product_id', $product->id)->delete();
        foreach($request->name as $key => $value){
            foreach($request->name[$key] as $okey => $stockValue){
                ProductStock::create([
                    'product_id' => $product->id,
                    'attribute_id' => $key,
                    'attribute_value' => $request->name[$key][$okey],
                    'stock' => $request->stock[$key][$okey],
                ]);
            }
        }

        return back()->with('success', translate("Product stock has been updated"));
    }

    public function singleProductAllOrder($id)
    {
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " all orders log";

        $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller, $product){
            $q->where('product_id', $product->id)->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }
    public function singleProductPlacedOrder($id)
    {
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " placed orders log";
        $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller, $product){
            $q->where('product_id', $product->id)->where('status', 1)->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }
    public function singleProductDeliveredOrder($id)
    {
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name). " delivered orders log";
         $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller, $product){
            $q->where('product_id', $product->id)->where('status', 5)->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function productGalleryImageDelete($id)
    {
        $seller = Auth::guard('seller')->user();
        $productGallery = ProductImage::findOrFail($id);
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $productGallery->product_id)->firstOrFail();
        $file = file_path()['product']['gallery']['path'].'/'.$productGallery->image;
        if(file_exists($file)){
            @unlink($file);
        }
        $productGallery->delete();

        return back()->with('success', translate("Gallery image has been deleted"));
    }

    public function search(Request $request, $scope)
    {
        $request->validate([
            'searchFilter'=>'required',
        ]);

        if($request->option_value == 'Select Menu'){
            return back()->with('error', translate("Please Select A Value Form Select Box"));

        }
        $search = $request->searchFilter;
        $title = "Search by -" . $search;
        $seller = Auth::guard('seller')->user();
        $products=Product::with(['category', 'order'])->sellerProduct()->physical()->where('seller_id', $seller->id);

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
        if ($request->option_value == 'price') {
            $products->Where('price', 'like', "%$search%");
        }
        if ($scope == 'approved') {
            $products = $products->published();
        }elseif($scope == 'refuse'){
            $products = $products->inactive();
        }elseif($scope == 'trashed'){
            $products = $products->onlyTrashed();
        }
        $products = $products->orderBy('id','desc')->paginate(paginate_number());
        return view('seller.product.index', compact('title','products','search'));
    }


    public function delete(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->physical()->where('seller_id', $seller->id)->where('id', $request->id)->firstOrFail();
        $cart = Cart::where('product_id', $request->id)->get();
        if(count($product->order) == 0 && count($product->digitalProductAttribute) == 0  && count($product->wishlist) == 0 && count($cart) == 0)  {
           $product->delete();
           return back()->with('success', translate("Product has been deleted"));

        }
        else{
           return back()->with('error', translate("This Product Has Order or Added in Cart or Added in WishList . Please Try Again"));
        }

    }


    public function restore(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->where('seller_id', $seller->id)->where('id', $request->id)->restore();
        return back()->with('success', translate("Product has been restore"));
    }


    /**
     * parmanaent delete form trash
     */
    public function permanentDelete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);

        $seller = Auth::guard('seller')->user();
        $product = Product::with(['digitalProductAttribute'=>function($q){
            return $q->with(['digitalProductAttributeValueKey']);
        }])->onlyTrashed()->sellerProduct()->where('seller_id', $seller->id)->where('id', $request->id)->first();
        $product->shippingDelivery()->delete();
        $product->gallery()->delete();
        $product->stock()->delete();
        $product->rating()->delete();
        if($product->digitalProductAttribute){

            foreach($product->digitalProductAttribute as  $digitalProductAttribute ){
                DigitalProductAttributeValue::where('digital_product_attribute_id',$digitalProductAttribute->id)->delete();
            }
            $product->digitalProductAttribute()->delete();
        }
        $product->forceDelete();
        return back()->with('success', translate("Product Permenently Deleted"));

    }

    /** attribute implemented */
    public  function combination(Request $request) {
        $options = array();

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no') && count($request->input('choice_no',[])) > 0) {

            foreach ($request->input('choice_no',[]) as $key => $no) {

                $name = 'choice_options_' . $no;
                $data = array();
            
                if(isset($request[$name])){
                    foreach ($request[$name] as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
             
            }
        }

        $combinations = $this->combineAttr($options);

        return view('admin.product.combination', compact('combinations', 'unit_price', 'product_name'));

    }

    public  function combineAttr($arrays) {
        $resultArr = array(array());
        foreach ($arrays as $key => $arr_values) {
            $tempArr = array();
            foreach ($resultArr as $resultArr_item) {
                foreach ($arr_values as $arr_val) {
                    $tempArr[] = array_merge($resultArr_item, array($key => $arr_val));
                }
            }
            $resultArr = $tempArr;
        }
        return $resultArr;
    }

    public function attrValue(Request $request){
        $attrValues = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();
        $html = '';
        foreach ($attrValues as $value) {
            $html .= '<option value="' . $value->name . '">' . $value->name . '</option>';
        }
        echo json_encode($html);
    }


    public function stockStore(array $data, $product){
        
        $collection = collect($data);
        $options = array();

        if ($collection->has('choice_no')) {
            foreach ($collection['choice_no'] as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
            
                foreach (request()[$name] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }
                array_push($options, $data);
            }
        }
     
        $combinations = $this->combineAttr($options);

        $variant = '';
        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            $product->save();
            foreach ($combinations as $key => $combination) {
                $str = $this->combinationString($combination, $collection);
                $stock = new ProductStock();
                $stock->product_id = $product->id;
                $stock->attribute_value = $str;
                $stock->price = request()['price_' . str_replace('.', '_', $str)];
                $stock->qty = request()['qty_' . str_replace('.', '_', $str)];
                $stock->save();
            }
        }
    }



    public function stock_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $options = array();
    
        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no') && count($request->input('choice_no',[])) > 0) {
            foreach ($request->input('choice_no',[]) as $key => $no) {
         
                $name = 'choice_options_' . $no;
                $data = array();
                if(isset($request[$name])){
                    foreach ($request[$name] as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = $this->combineAttr($options);
        return view('admin.product.combination_edit', compact('combinations', 'unit_price', 'product_name','product'));

    }


    public static function combinationString($combination, $collection)
    {
        $str = '';
        foreach ($combination as $key => $item) {
            if ($key > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
               
                    $str .= str_replace(' ', '', $item);

            }
        }
        return $str;
    }



}
