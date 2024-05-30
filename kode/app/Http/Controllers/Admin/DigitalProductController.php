<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\DigitalProductAttribute;
use App\Models\Category;
use App\Models\DigitalProductAttributeValue;
use App\Http\Requests\DigitalProductRequest;
use App\Http\Requests\DigitalProductUpdateRequest;
use App\Models\Cart;
use Carbon\Carbon;
use Image;
class DigitalProductController extends Controller

{

    public function __construct()
    {
        $this->middleware(['permissions:view_product'])->only('index');
        $this->middleware(['permissions:create_product'])->only('create','store');
        $this->middleware(['permissions:update_product'])->only('edit','update');
        $this->middleware(['permissions:delete_product'])->only('delete');
    }

    public function seller()
    {
        $title = "Seller digital products";
        $sellerDigitalProducts = Product::sellerProduct()->digital()->orderBy('id', 'DESC')->search()->with('category', 'seller', 'subCategory')->paginate(paginate_number());
        return view('admin.digital_product.seller', compact('title', 'sellerDigitalProducts'));
    }

    public function sellerTrashedProduct()
    {
        $title = "Seller digital trashed product showing item";
        $sellerDigitalProducts = Product::with(['seller','category','order'])->sellerProduct()->digital()->onlyTrashed()->search()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('admin.digital_product.seller', compact('title', 'sellerDigitalProducts'));
    }

    public function sellerProductDetails($id)
    {
        $title = "Seller digital product details";
        $sellerDigitalProduct = Product::with(['shippingDelivery','shippingDelivery.shippingDelivery'
         ])->sellerProduct()->digital()->search()->where('id', $id)->firstOrFail();
        return view('admin.digital_product.seller_details', compact('title', 'sellerDigitalProduct'));
    }

    
    public function replicate ($id)
    {

        $product = Product::with(['digitalProductAttribute'])->find($id);
        $replicatedProduct = $product->replicate();
        $replicatedProduct->created_at = Carbon::now();
        $replicatedProduct->save();

        return back()->with('success',translate("Product Replicated"));

    }


    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);

        $product = Product::with(['digitalProductAttribute'=>function($q){
            return $q->with(['digitalProductAttributeValueKey']);
        }])->Digital()->inhouseProduct()->where('id',$request->id)->first();

        $cart = Cart::where('product_id', $request->id)->get();

         if(count($product->order) == 0 &&  count($product->wishlist) == 0 && count($cart) == 0)  {
            $product->delete();
            return back()->with('success', translate("Product has been deleted"));
         }

         
       return back()->with('error', translate("This Product Has Order or Added In Cart Or In WishList, Plese Try Again"));
         
    }
    public function sellerProductAttributeValue($id)
    {
        $title = "Seller digital product attribute value";
        $sellerDigitalProductAttributeValues = DigitalProductAttributeValue::where('digital_product_attribute_id', $id)->latest()->paginate(paginate_number());
        return view('admin.digital_product.seller_attribute_value', compact('title', 'sellerDigitalProductAttributeValues'));
    }


    public function sellerProductItem(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:new,published,inactive,all',
        ]);
        $type = $request->type;
        $title = "Seller ".$type." digital product showing item";
        $sellerDigitalProducts = Product::sellerProduct()->digital();
        if($type == "new"){
            $sellerDigitalProducts = $sellerDigitalProducts->new();
        }elseif($type == "published"){
            $sellerDigitalProducts = $sellerDigitalProducts->published();
        }elseif($type == "inactive"){
            $sellerDigitalProducts = $sellerDigitalProducts->inactive();
        }
        $sellerDigitalProducts = $sellerDigitalProducts->search()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('admin.digital_product.seller', compact('title', 'sellerDigitalProducts', 'type'));
    }

    public function sellerProductDelete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::sellerProduct()->digital()->where('id', $request->id)->delete();
        return back()->with('success', translate("Seller digital product has been deleted"));
    }

    public function sellerProductApprovedBy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::sellerProduct()->digital()->where('id', $request->id)->firstOrFail();
        $product->status = Product::PUBLISHED;
        $product->save();
        return back()->with('success', translate("Seller digital product has been approved"));
    }

    public function sellerProductInactive(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::sellerProduct()->digital()->where('id', $request->id)->firstOrFail();
        $product->status = Product::INACTIVE;
        $product->save();
        return back()->with('success', translate("Seller digital product has been inactive"));
    }

    public function sellerProductRestore(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id'
        ]);
        $product = Product::sellerProduct()->digital()->onlyTrashed()->where('id', $request->id)->restore();
         return back()->with('success', translate("Seller digital product has been restore"));
    }

    public function index()
    {
        $title = "In-house digital products";
        $inhouseDigitalProducts = Product::inhouseProduct()->digital()->search()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('admin.digital_product.index', compact('title', 'inhouseDigitalProducts'));
    }
    public function trashed()
    {
        $title = "In-house digital trashed products";
        $inhouseDigitalProducts = Product::inhouseProduct()->digital()->onlyTrashed()->search()->orderBy('id', 'DESC')->with('category','subCategory')->paginate(paginate_number());
        return view('admin.digital_product.index', compact('title', 'inhouseDigitalProducts'));
    }

    public function create()
    {
        $title = "Add new digital product";
        $categories = Category::where('status', '1')->with('parent')->select('id', 'name')->get();
        return view('admin.digital_product.create', compact('title', 'categories'));
    }

    public function store(DigitalProductRequest $request)
    {
        $metaImage = null; $featuredImage = null;
        if($request->hasFile('meta_image')){
            try {
                $metaImage = store_file($request->meta_image, file_path()['seo_image']['path']);
            }catch (\Exception $exp) {
                 
            }
        }
        if($request->hasFile('featured_image')){

            try {
                $featuredImage = store_file($request->featured_image,file_path()['product']['featured']['path']);
            }catch (\Exception $exp) {
              
            }
        }
        $product = Product::create([
            'name'=> $request->name,
            'slug'=> make_slug($request->name),
            'product_type' => Product::DIGITAL,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'description'=> build_dom_document($request->description,'digital_description'.rand(10,22000)),
            'meta_title'=> $request->meta_title ?? null,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description ?? null,
            'meta_image'=> $metaImage,
            'featured_image'=> $featuredImage,
            'status'=> $request->status,
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

        return back()->with('success', translate("Digital product has been created"));
    }

    public function edit($id)
    {
        $title = "Digital product update";
        $product = Product::inhouseProduct()->digital()->where('id',$id)->firstOrFail();
        $categories = Category::where('status', '1')->select('id', 'name')->with('parent')->get();
        return view('admin.digital_product.edit', compact('title', 'product', 'categories'));
    }

    public function update(DigitalProductUpdateRequest $request, $id)
    {
        $product = Product::inhouseProduct()->digital()->where('id', $id)->firstOrFail();
        $metaImage = $product->meta_image; $featuredImage = $product->featured_image;
        if($request->hasFile('meta_image')){
            try {
                $metaImage = store_file($request->meta_image, file_path()['seo_image']['path'], null, $product->meta_image);
            }catch (\Exception $exp) {
             
            }
        }
        if($request->hasFile('featured_image')){
            try {
                $featuredImage = store_file($request->featured_image,file_path()['product']['featured']['path'],null , $featuredImage);
            }catch (\Exception $exp) {
               
            }
        }
        $product->update([
            'product_type' => Product::DIGITAL,
            'name'=> $request->name,
            'category_id'=> $request->category_id,
            'sub_category_id'=> $request->subcategory_id,
            'description'=> build_dom_document($request->description,'update_digital_description'.rand(10,22000)),

            'meta_title'=> $request->meta_title ?? null,
            'meta_keywords'=> $request->meta_keywords ?? null,
            'meta_description'=> $request->meta_description ?? null,
            'meta_image'=> $metaImage,
            'featured_image'=> $featuredImage,
            'status'=> $request->status,
        ]);
       
         return back()->with('success', translate("Digital product has been updated"));
    }

    public function attribute($id)
    {
  
        $product = Product::digital()->where('id', $id)->firstOrFail();
        $title = ucfirst($product->name)." Attribute List";
        $digitalProductAttributes = DigitalProductAttribute::where('product_id', $product->id)->paginate(paginate_number());
        return view('admin.digital_product.attribute', compact('title', 'digitalProductAttributes', 'product'));
    }

    public function attributeStore(Request $request)
    {
        $data = $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|max:255',
            'price' => 'required|numeric|gt:0',
        ]);

        $digitalProductAttribute = new DigitalProductAttribute();
        $digitalProductAttribute->product_id = $request->product_id;
        $digitalProductAttribute->name = $request->name;
        $digitalProductAttribute->short_details = $request->short_details;
        $digitalProductAttribute->price = $request->price;
        $digitalProductAttribute->save();

         return back()->with('success', translate("Digital product attribute has been created"));
    }

    public function attributeDetails($id)
    {
        $digitalProductAttribute = DigitalProductAttribute::where('id', $id)->first();
        $title = "Attribute value store for ".$digitalProductAttribute->name;
        $digitalProductAttributeValues = DigitalProductAttributeValue::where('digital_product_attribute_id', $digitalProductAttribute->id)->paginate(paginate_number());
        return view('admin.digital_product.attribute_edit', compact('title', 'digitalProductAttribute', 'digitalProductAttributeValues'));
    }

    public function attributeValueUpdate(Request $request)
    {
       $this->validate($request, [
            'id'  => 'required',
            'name' => 'required|max:255',
            'price' => 'required|numeric|gt:0',
        ]);
        $digitalProductAttribute = DigitalProductAttribute::where('id', $request->id)->first();
        $digitalProductAttribute->name = $request->name;
        $digitalProductAttribute->short_details = $request->short_details;
        $digitalProductAttribute->price = $request->price;
        $digitalProductAttribute->save();
        return back()->with('success', translate("Digital product attribute has been updated"));
    }

    public function attributeDelete(Request $request)
    {
        $this->validate($request, [
            'id'  => 'required',          
        ]);
        $digitalProductAttribute = DigitalProductAttribute::where('id', $request->id)->first();
        if($digitalProductAttribute->status != 2) {
            if($digitalProductAttribute->digitalProductAttributeValueKey->count() == 0){
                $digitalProductAttribute->delete();
                $response = 'success';   
                $message = translate("Digital product attribute has been deleted");  
            } else{
                $response = 'error';   
                $message = translate("At first delete your attribute value then try again ");  
            }
        } else{
            $response = 'error';   
            $message = translate("Your attribute has been sold");   
        }
         return back()->with( $response,  $message);
    }

    public function attributeValueStore(Request $request, $id)
    {
        $this->validate($request,[
            'text' => 'required',
        ]);
        $digitalAttribute = DigitalProductAttribute::where('id',$id)->first();
        if($request->text){
            $value = preg_replace('/[ ,]+/', ',', trim($request->text));
            $attributeValues = explode(",",$value);
            foreach($attributeValues as $key => $attributeValue){
                DigitalProductAttributeValue::create([
                    'digital_product_attribute_id' => $digitalAttribute->id,
                    'value' => $attributeValue,
                    'status'=>1
                ]);
            }
        }
         return back()->with('success', translate("Attribute value store for").$digitalAttribute->name);
    }

    public function attributeValueDelete(Request $request)
    {
        $value = DigitalProductAttributeValue::where('id', $request->id)->first();
        $value->delete();
        return back()->with('success', translate("Attribute value has been deleted"));
    }

}
