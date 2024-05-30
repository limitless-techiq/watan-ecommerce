<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\OrderDetails;
use App\Models\Transaction;
use App\Models\Seller;
use App\Models\Product;
use App\Models\GeneralSetting;
use App\Models\OrderStatus;

class InhouseProductOrderController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permissions:view_order'])->only('index',"search",'placed','confirmed','processing','shipped','delivered','cancel','printInvoice');
        $this->middleware(['permissions:update_order'])->only('orderDetailStatusUpdate','orderStatusUpdate');
        $this->middleware(['permissions:delete_order'])->only('delete');
    }

    public function index()
    {
        $title  = translate("Inhouse all orders");
        $orders = Order::inhouseOrder()->search()->date()->physicalOrder()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function placed()
    {
        $title  = translate("Inhouse placed orders");
        $orders = Order::inhouseOrder()->search()->date()->physicalOrder()->placed()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(10);
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function confirmed()
    {
        $title = translate("Inhouse confirmed orders");
        $orders = Order::inhouseOrder()->search()->date()->physicalOrder()->confirmed()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function processing()
    {
        $title = translate("Inhouse processing orders");
        $orders = Order::inhouseOrder()->physicalOrder()->search()->date()->processing()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function shipped()
    {
        $title = translate("Inhouse shipped orders");
        $orders = Order::inhouseOrder()->physicalOrder()->search()->date()->shipped()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function delivered()
    {
        $title = translate("Inhouse delivered orders");
        $orders = Order::inhouseOrder()->physicalOrder()->search()->date()->delivered()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function cancel()
    {
        $title = translate('Inhouse cancel orders');
        $orders = Order::inhouseOrder()->physicalOrder()->search()->date()->cancel()->orderBy('id', 'DESC')->with('customer',  'shipping', 'shipping.method','orderDetails', 'orderDetails.product')->paginate(paginate_number());
        return view('admin.order.index', compact('title', 'orders'));
    }

    public function details($id)
    {
        $title = translate("Inhouse order details");
        $order = Order::inhouseOrder()->physicalOrder()->where('id', $id)->firstOrFail();

        $orderDeatils = OrderDetails::where('order_id', $order->id)->inhouseOrderProduct()->with('product')->get();

        $orderStatus  = OrderStatus::where('order_id', $id)->latest()->get();

        return view('admin.order.details', compact('title', 'order', 'orderDeatils', 'orderStatus'));
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
        $title = "Inhouse order search by -" . $search;
        $orders = Order::inhouseOrder()->physicalOrder();

        if($request->option_value == 'order_number'){
            $orders->Where('order_id', '=', $search);
        }
        if($request->option_value == 'customer'){
            $orders->whereHas('customer', function($q) use ($search){
                    $q->where('name','like',"%$search%");
                });
        }
        if($request->option_value == 'Amount'){
            $orders->Where('amount', '=', $search);
        }
        if ($scope == 'placed') {
            $orders = $orders->placed();
        }elseif($scope == 'confirmed'){
            $orders = $orders->confirmed();
        }elseif($scope == 'processing'){
            $orders = $orders->processing();
        }elseif($scope == 'shipped'){
            $orders = $orders->shipped();
        }elseif($scope == 'delivered'){
            $orders = $orders->delivered();
        }elseif($scope == 'cancel'){
            $orders = $orders->cancel();
        }
        $orders = $orders->orderBy('id','desc')->with('customer')->paginate(paginate_number());
     return view('admin.order.index', compact('title', 'orders', 'search'));
    }

    public function dateSearch(Request $request, $scope)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);
        $searchDate = explode('-',$request->date);
        $firstDate = $searchDate[0];
        $lastDate = $searchDate[1];
        $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
        if ($firstDate && !preg_match($matchDate,$firstDate)) {
            return back()->with('error',translate("Invalid order search date format"));
        }
        if ($lastDate && !preg_match($matchDate,$lastDate)) {
            return back()->with('error',translate("Invalid order search date format"));
        }
        if ($firstDate) {
            $orders = Order::inhouseOrder()->physicalOrder()->whereDate('created_at',Carbon::parse($firstDate));
        }
        if ($lastDate){
            $orders = Order::inhouseOrder()->physicalOrder()->whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
        }
        if ($scope == 'placed') {
            $orders = $orders->placed();
        }elseif($scope == 'confirmed'){
            $orders = $orders->confirmed();
        }elseif($scope == 'processing'){
            $orders = $orders->processing();
        }elseif($scope == 'shipped'){
            $orders = $orders->shipped();
        }elseif($scope == 'delivered'){
            $orders = $orders->delivered();
        }elseif($scope == 'cancel'){
            $orders = $orders->cancel();
        }
        $orders = $orders->orderBy('id','desc')->with('customer')->paginate(paginate_number());
        $searchDate = $request->date;
        $title = translate('Orders search by') . $searchDate;
        return view('admin.order.index', compact('title','orders','searchDate'));
    }

    public function printInvoice($id, $type)
    {
        $title = "Print invoice";
        $order = Order::inhouseOrder()->physicalOrder()->where('id',$id)->firstOrFail();
        if($type =="inhouse"){
            $orderDeatils = OrderDetails::where('order_id', $order->id)->inhouseOrderProduct()->with('product')->get();
        }else{
            $orderDeatils = OrderDetails::where('order_id', $order->id)->sellerOrderProduct()->with('product')->get();
        }

        return view('admin.order.print', compact('title', 'order', 'orderDeatils'));
    }


    public function orderStatusUpdate(Request $request, $id='')
    {

        if($id==''){
            $id = $request->order_id;
        }

        if($request->payment_status!='' && $request->status==''){
            $datavalidate = [
                'payment_status' => 'required|in:1,2',
            ];
        }elseif($request->payment_status == '' && $request->status!=''){
            $datavalidate = [
                'status' => 'required|in:2,3,4,5,6',
            ];
        }else{
            $datavalidate = [
                'payment_status' => 'required|in:1,2',
                'status' => 'required|in:2,3,4,5,6',
            ];
        }
        
        $this->validate($request, $datavalidate);
        $order = Order::where('id', $id)->firstOrFail();
        if($order->status == 5){

            return back()->with('error',translate('This order has already been delivered'));
        }
        $general = GeneralSetting::first();

        if($request->payment_status){           
            $order->payment_status = $request->payment_status;
        }
        if($request->status){
            $order->status = $request->status;
        }

        foreach ($order->orderDetails as $key => $value) {
            if($request->status == 5){
                $commission  = (($value->total_price * $general->product_commission)/100);
                $finalAmount = $value->total_price - $commission;
                $product = Product::find($value->product_id);
                if($product->seller_id){
                    $seller = Seller::where('id',$product->seller_id)->first();
                    $seller->balance += $finalAmount;
                    $seller->save();

                    $transaction = Transaction::create([
                        'seller_id' => $seller->id,
                        'amount' => $finalAmount,
                        'post_balance' => $seller->balance,
                        'transaction_type' => Transaction::PLUS,
                        'transaction_number' => trx_number(),
                        'details' => 'Amount added for this order '.$order->order_id,
                    ]);
                }
            }
            $value->status = $request->status;
            $value->save();
        }
        $order->save();

        $orderStatus = new OrderStatus();
        $orderStatus->order_id        = $id;
        $orderStatus->payment_status  = $request->payment_status;
        if($request->payment_note!=''){
            $orderStatus->payment_note    = $request->payment_note;
        }
        $orderStatus->delivery_status = $request->status;
        if($request->delivery_note!=''){
            $orderStatus->delivery_note   = $request->delivery_note;
        }
        $orderStatus->save();

        return back()->with('success',translate('Order status has been updated'));
    }


    public function orderDetailStatusUpdate(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:order_details,id',
            'status' => 'required|in:2,3,4,5,6',
        ]);
        $orderDeatils = OrderDetails::where('id', $request->id)->first();
        $orderDeatils->status = $request->status;
        $orderDeatils->save();
 
        return back()->with('success',translate('Order product status has been updated'));
    }

    public function delete($id)
    {
        $order = Order::with(['orderDetails'])->where('id',$id)->first();
        $order->orderDetails()->delete();
        $order->delete();
        return back()->with('success',translate('Order Deleted Successfully'));
    }
}
