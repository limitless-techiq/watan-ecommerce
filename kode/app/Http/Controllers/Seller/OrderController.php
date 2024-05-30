<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use Carbon\Carbon;

class OrderController extends Controller
{
    
    public function index()
    {
        $title = "Seller all orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','customer','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->date()->search()->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function placed()
    {
        $title = "Seller placed orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->date()->search()->placed()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function confirmed()
    {
        $title = "Seller confirmed orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->date()->search()->confirmed()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function processing()
    {
        $title = "Seller processing orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->date()->search()->processing()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function shipped()
    {
        $title = "Seller shipped orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->date()->search()->shipped()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function delivered()
    {
        $title = "Seller delivered orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->physicalOrder()->date()->search()->delivered()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }

    public function cancel()
    {
        $title = "Seller cancel orders";
        $seller = Auth::guard('seller')->user();
        $orders = Order::with(['log','paymentMethod','orderDetails' => function($q){
            return $q->sellerOrderProduct()->whereHas('product', function($query)  {
                $query->where('seller_id', Auth::guard('seller')->user()->id);
            });
        },'customer','shipping','orderDetails.product'])->sellerOrder()->date()->search()->physicalOrder()->cancel()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('seller.order.index', compact('title', 'orders'));
    }


    public function details($id)
    {
        $title = "Seller order details";
        $seller = Auth::guard('seller')->user();
        $order = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->where('id', $id)->firstOrFail();

        $orderDeatils = OrderDetails::where('order_id', $order->id)->sellerOrderProduct()->whereHas('product', function($query) use ($seller){
            $query->where('seller_id', $seller->id);
        })->with('product')->get();

        $orderStatus  = OrderStatus::where('order_id', $id)->latest()->get();
        return view('seller.order.details', compact('title', 'order', 'orderDeatils','orderStatus'));
    }


    public function orderStatusUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:2,3',
        ]);
        $seller = Auth::guard('seller')->user();
        $order = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->where('id', $id)->firstOrFail();
        $orderDeatils = OrderDetails::where('order_id', $order->id)->sellerOrderProduct()->whereHas('product', function($query) use ($seller){
            $query->where('seller_id', $seller->id);
        })->get();

        if($order->status == 5){
            return  back()->with('error',translate("Order Already Delivered"));
        } else{
            foreach ($orderDeatils as $key => $value) {
                $value->status = $request->status;
                $value->save();
            }
            $order->save();
            return back()->with('success',translate("Order status has been updated"));
        }
        
    }





    public function printInvoice($id)
    {
        $title = "Print invoice";
         $seller = Auth::guard('seller')->user();
        $order = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails', function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->where('id', $id)->firstOrFail();
        $orderDeatils = OrderDetails::where('order_id', $order->id)->sellerOrderProduct()->whereHas('product', function($query) use ($seller){
            $query->where('seller_id', $seller->id);
        })->with('product')->get();
        return view('seller.order.print', compact('title', 'order', 'orderDeatils'));
    }
}
