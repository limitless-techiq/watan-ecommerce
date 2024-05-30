<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Models\ShippingDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShippingController extends Controller
{

    public function __construct(){

        $this->middleware(['permissions:view_settings'])->only('method','shippingIndex');
        $this->middleware(['permissions:create_settings'])->only('methodStore','shippingUpdate','shippingEdit','shippingStore');
        $this->middleware(['permissions:update_settings'])->only('methodUpdate','methodDelete','shippingDelete');
    }

    /**
     *  Get all shipping method
     *
     * @return View
     */
    public function method() :View
    {
        $title           = "Shipping method";
        $shippingMethods = ShippingMethod::when(request()->input('search'),function($q){
             return $q->where('name','like','%'.request()->input('search').'%');
        })->latest()->get();
        return view('admin.shipping.method', compact('title', 'shippingMethods'));
    }


    /**
     *  Create a new shipping method 
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function methodStore(Request $request) :RedirectResponse
    {

        $data = $this->validate($request, [
            'name'   => 'required|max:255',
            'status' => 'required|in:1,2'
        ]);
        $image = null;
        if($request->hasFile('image')) {
            try {
                $image = store_file($request->image, file_path()['shipping_method']['path'],);
            }catch (\Exception $exp) {

            }
        }
        $data['image'] = $image;
        ShippingMethod::create($data);
        return back()->with('success',translate('Shipping method has been created'));
    }


    /**
     * Update Shipping method 
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function methodUpdate(Request $request) :RedirectResponse
    {
        $data = $this->validate($request, [
            'name'   => 'required|max:255',
            'status' => 'required|in:1,2'
        ]);
        $shipping = ShippingMethod::findOrFail($request->id);
        $image = $shipping->image;
        if($request->hasFile('image')) {
            try {
                $image = store_file($request->image, file_path()['shipping_method']['path'], null, $image);
            }catch (\Exception $exp) {

            }
        }
        $data['image'] = $image;
        $shipping->update($data);
        return back()->with('success',translate('Shipping method has been created'));
    }



    /**
     * Delete a shipping method 
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function methodDelete(Request $request) :RedirectResponse
    {
        $shippingMethod = shippingMethod::where('id', $request->id)->first();

        if(count($shippingMethod->shippingdelivery) > 0){
            return back()->with('error',translate('Before delete shipping delivery and try again'));
        } 

        $shippingMethod->delete();
        return back()->with('success',translate('Shiping methode has been deleted'));

    }


    /**
     * Get all shipping Delivary 
     *
     * @return View
     */
    public function shippingIndex() :View
    {
   
        $title              = "Manage Shipping Delivery";
        $methods            = ShippingMethod::where('status', 1)->get();
        $shippingDeliverys  = ShippingDelivery::latest()->with('method')
                                ->when(request()->input('search'),function($q){
                                        $searchBy = '%'. request()->input('search').'%';
                                        return $q->where('name','like',$searchBy)
                                                    ->orWhereHas('method',function($q) use($searchBy){
                                                    $q->where("name",'like', $searchBy);
                                                    });
                                })
                                ->get();

        return view('admin.shipping.delivery', compact('title', 'shippingDeliverys','methods'));
    }



    /**
     * Undocumented function
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function shippingStore(Request $request) :RedirectResponse
    {
        $data = $this->validate($request,[
                        'name'        => 'required',
                        'method_id'   => 'required|exists:shipping_methods,id',
                        'duration'    => 'required',
                        'price'       => 'required|numeric|gt:0',
                        'description' => 'required',
                        'status'      => 'required|in:1,0'
        ]);

        ShippingDelivery::create($data);

        return back()->with('success',translate('Shipping delivery has been store'));
    }

    /**
     * Shipping delivary edit
     *
     * @param int | string $id 
     * @return View
     */
    public function shippingEdit(int | string $id) :View
    {
        $title             = "Shipping delivery system update";
        $shippingDelivery  = ShippingDelivery::findOrFail($id);
        $methods           = ShippingMethod::where('status', 1)->get();
        $countries         = json_decode(file_get_contents(resource_path('views/partials/country_file.json')));

        return view('admin.shipping.edit_delivery', compact('title', 'methods', 'shippingDelivery', 'countries'));

    }




    /**
     * Delete a shipping delivary method 
     *
     * @param Request $request
     * @return RedirectResponse
     */
     public function shippingDelete(Request $request) :RedirectResponse {

        $shipping = ShippingDelivery::with(['order'])->where('id',$request->id)->first();
        if(count($shipping->order) == 0){
            $shipping->delete();
            return back()->with('success',translate('Shipping Delivery Deleted'));
        }
     
        return back()->with('error',translate('This Shipping Delivary Has Order Under It ,Please Try again'));
        

     }


     /**
      * Update Shipping Delivery  
      *
      * @param Request $request
      * @param  int | string $id $id
      * @return RedirectResponse
      */
    public function shippingUpdate(Request $request, int | string $id) :RedirectResponse
    {
        $data = $this->validate($request,[
            'name'        => 'required',
            'method_id'   => 'required|exists:shipping_methods,id',
            'duration'    => 'required',
            'price'       => 'required|numeric|gt:0',
            'description' => 'required',
            'status'      => 'required|in:1,0'
        ]);

        $shippingDelivery = ShippingDelivery::findOrFail($id);

        $shippingDelivery->update($data);

        return back()->with('success',translate('Shipping delivery has been updated'));
    }

   
}
