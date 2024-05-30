<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Models\DigitalProductAttribute;
use App\Http\Requests\DigitalProductRequest;
use App\Http\Requests\DigitalProductUpdateRequest;
use App\Models\Cart;
use App\Models\DigitalProductAttributeValue;
use App\Models\PlanSubscription;

class DigitalProductController extends Controller
{
    public function __construct()
    {
         $this->middleware('sellercheckstatus');
    }

    public function index()
    {
        $title  = "Manage digital product";
        $seller = auth()->guard('seller')->user();
        $digitalProducts = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());

        return view('seller.digital.index', compact('title', 'digitalProducts'));
    }

    public function new()
    {
        $title = "Manage digital new product";
        $seller = auth()->guard('seller')->user();
        $digitalProducts = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->new()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('seller.digital.index', compact('title', 'digitalProducts'));
    }

    public function approved()
    {
        $title = "Manage digital approved product";
        $seller = auth()->guard('seller')->user();
        $digitalProducts = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->published()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('seller.digital.index', compact('title', 'digitalProducts'));
    }

    public function create()
    {
        $title      = "Add new digital product";
        $categories = Category::where('status', '1')->with('parent')->select('id', 'name')->get();
        return view('seller.digital.create', compact('title', 'categories'));
    }

    public function store(DigitalProductRequest $request)
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
        $seller = auth()->guard('seller')->user();
        $product = Product::create([
            'name'=> $request->name,
            'seller_id'=> $seller->id,
            'product_type'=> Product::DIGITAL,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'description'=> build_dom_document($request->description,'seller_digital_des'.rand(10,200)),
            'meta_title'=> $request->meta_title ?? null,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description ?? null,
            'meta_image'=> $featuredImage,
            'featured_image'=> $featuredImage,
            'status'=> Product::NEW,
        ]);

        if($request->attribute_option){
            $i = 0;
            foreach(@$request->attribute_option['name'] as $val){
                    DigitalProductAttribute::create([
                        'product_id' => $product->id,
                        'name'       => @$request->attribute_option['name'][$i]?? "N/A",
                        'price'      => @$request->attribute_option['price'][$i] ?? 0,
                    ]);

                    $i++;
                
            }
       }

       $subscription->total_product -=1;

       $subscription->save();


           
        return back()->with('success', translate("Digital product has been created"));
    }

    public function edit($id)
    {
        $title = "Update digital product";
        $seller = auth()->guard('seller')->user();
        $categories = Category::where('status', '1')->select('id', 'name')->with('parent')->get();
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        return view('seller.digital.edit', compact('title', 'categories', 'product'));
    }


    public function update(DigitalProductUpdateRequest $request, $id)
    {
        $seller = auth()->guard('seller')->user();
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
         $featuredImage = $product->featured_image;
      
        if($request->hasFile('featured_image')){
            try {
                $featuredImage = store_file($request->featured_image,file_path()['product']['featured']['path'],null, $product->featured_image);
            }catch (\Exception $exp) {
               
            }
        }
        $product->update([
            'name'=> $request->name,
            'seller_id'=> $seller->id,
            'product_type'=> Product::DIGITAL,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'description'=> build_dom_document($request->description,'seller_digital_des_editA'.rand(10,200)),
            'meta_title'=> $request->meta_title ?? null,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description ?? null,
            'meta_image'=> $featuredImage,
            'featured_image'=> $featuredImage,
            'status'=> Product::NEW,
        ]);

           
        return back()->with('success', translate("Digital product has been updated"));
    }


    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);

        $product = Product::with(['digitalProductAttribute'=>function($q){
            return $q->with(['digitalProductAttributeValueKey']);
        }])->sellerProduct()->digital()->where('id',$request->id)->first();

        $cart = Cart::where('product_id', $request->id)->get();

         if(count($product->order) == 0 &&  count($product->wishlist) == 0 && count($cart) == 0)  {
            $product->delete();
        
            return back()->with('success', translate("Product has been deleted"));
         }

         else{
            return back()->with('error', translate("This Product Has Order or Added In Cart Or In WishList, Plese Try Again"));
         }
    }

    public function attribute($id)
    {
        $seller = auth()->guard('seller')->user();
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name)." Attribute List";
        $digitalProductAttributes = DigitalProductAttribute::where('product_id', $product->id)->latest()->paginate(paginate_number());
        return view('seller.digital.attribute', compact('title', 'digitalProductAttributes', 'product'));
    }


    public function attributeStore(Request $request)
    {
        $data = $this->validate($request,[
            'product_id' => 'required|exists:products,id',
            'name' => 'required|max:255',
            'short_details' =>'',
            'price' => 'required|numeric|gt:0',
        ]);

        $seller = auth()->guard('seller')->user();
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $request->product_id)->firstOrFail();
        DigitalProductAttribute::create($data);
        return back()->with('success', translate("Digital product attribute has been created"));

    }

    public function attributeEdit($id)
    {
        $seller = auth()->guard('seller')->user();
        $digitalProductAttribute = DigitalProductAttribute::findOrFail($id);
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $digitalProductAttribute->product_id)->firstOrFail();
        $title = "Attribute value store for ".$digitalProductAttribute->name;
        $digitalProductAttributeValues = DigitalProductAttributeValue::where('digital_product_attribute_id', $digitalProductAttribute->id)->paginate(paginate_number());
        return view('seller.digital.attribute_edit', compact('title', 'digitalProductAttribute', 'digitalProductAttributeValues'));
    }

    public function attributeValueStore(Request $request, $id)
    {
        $this->validate($request,[
            'file' => 'nullable|mimes:xls,xlsx',
        ]);
        if(!$request->hasFile('file') && !$request->text){
            return back()->with('error', translate('Please submit attribute value'));

        }
        $seller = auth()->guard('seller')->user();
        $digitalAttribute = DigitalProductAttribute::findOrFail($id);
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $digitalAttribute->product_id)->firstOrFail();
       
        if($request->text){
            $value = preg_replace('/[ ,]+/', ',', trim($request->text));
            $attributeValues = explode(",",$value);
            foreach($attributeValues as $key => $attributeValue){
                DigitalProductAttributeValue::create([
                    'digital_product_attribute_id' => $digitalAttribute->id,
                    'value' => $attributeValue,
                    'status' => DigitalProductAttributeValue::ACTIVE,
                ]);
            }
        }
    
        return back()->with('success', translate("Attribute value stored"));
    }

    public function attributeValueDelete(Request $request)
    {
        $seller = auth()->guard('seller')->user();
        $value = DigitalProductAttributeValue::findOrFail($request->id);
        $digitalProductAttribute = DigitalProductAttribute::findOrFail($value->digital_product_attribute_id);
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $digitalProductAttribute->product_id)->firstOrFail();
        $value->delete();
        return back()->with('success', translate("Attribute value has been deleted"));
    }

  
    public function restore(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);
        $seller = Auth::guard('seller')->user();
        $product = Product::sellerProduct()->digital()->where('seller_id', $seller->id)->where('id', $request->id)->restore();
        return back()->with('success', translate("Product has been restore"));
    }
}
