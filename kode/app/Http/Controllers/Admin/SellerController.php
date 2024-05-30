<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Order;
use App\Models\SellerShopSetting;
use App\Models\SupportTicket;
use App\Rules\MinMaxCheckRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{

    public function __construct(){
        $this->middleware(['permissions:view_seller']);
    }

    public function index() :View
    {
        $title    = "Manage seller";
        $sellers  = Seller::search()->latest()->with('product', 'sellerShop')->paginate(paginate_number());
        return view('admin.seller.index', compact('title', 'sellers'));
    }

    public function active() :View
    {
        $title   = "Active seller";
        $sellers = Seller::search()->where('status', '1')->with('product', 'sellerShop')->latest()->paginate(paginate_number());
        return view('admin.seller.index', compact('title', 'sellers'));
    }

    public function banned() :View
    {
        $title   = "Banned seller";
        $sellers = Seller::search()->where('status', '0')->with('product', 'sellerShop')->latest()->paginate(paginate_number());
        return view('admin.seller.index', compact('title', 'sellers'));
    }

    public function shop(int $id) :View
    {
        $title   = "Seller Shop";
        $seller  = Seller::findOrFail($id);
        return view('admin.seller.shop', compact('title', 'seller'));
    }

    public function shopUpdate(Request $request, int $id) :RedirectResponse
    {
  
        $seller     = Seller::findOrFail($id);
        $sellerShop = SellerShopSetting::where('seller_id', $seller->id)->first();
        if( $sellerShop){
            $sellerShop->status = $request->status;
            $sellerShop->save();
        }
        return back()->with('success',translate('Seller shop status has been updated'));
    }

    public function details(int $id) :View
    {
        $title  = "Seller details";
        $seller = Seller::findOrFail($id);
        $orders['count']    = Order::specificSellerOrder($id)->count();
        $orders['physical'] = Order::specificSellerOrder($id)->physicalOrder()->orderBy('id', 'DESC')->get();
        $orders['digital']  = Order::specificSellerOrder($id)->digitalOrder()->orderBy('id', 'DESC')->count();
        return view('admin.seller.detail', compact('title', 'seller', 'orders'));
    }


    public function login(int $id) :RedirectResponse
    {

        $seller = Seller::findOrFail($id);
        Auth::guard('seller')->login($seller);

        return redirect()->route('seller.dashboard');

    }

    public function update(Request $request, int $id) :RedirectResponse
    {
        $request->validate([
            'name'     => 'nullable|max:120',
            'email'    => 'nullable|unique:sellers,email,'.request()->route('id'),
            'phone'    => 'nullable|unique:sellers,phone,'.request()->route('id'),
            'address'  => 'nullable|max:250',
            'city'     => 'nullable|max:250',
            'state'    => 'nullable|max:250',
            'zip'      => 'nullable|max:250',
            'rating'   => 'gt:0|lte:5',
            'status'   => 'nullable|in:1,2',
        ]);
        $seller         = Seller::findOrFail($id);
        $seller->name   = $request->name;
        $seller->email  = $request->email;
        $seller->phone  = $request->phone;
        $address = [
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip
        ];
        $seller->address = $address;
        $seller->status  = $request->status;
        $seller->rating  = $request->rating;
        $seller->save();
        return back()->with('success',translate('Seller has been updated'));
    }

    public function sellerAllProduct(int $id) :View
    {
        $seller   = Seller::findOrFail($id);
        $title    = ucfirst($seller->name)." products";
        $products = Product::sellerProduct()->physical()->where('seller_id', $id)->latest()->with('seller', 'category', 'order')->paginate(paginate_number());
        return view('admin.seller_product.index', compact('title', 'products'));
    }

    public function sellerTransaction(int $id) :View
    {
        $seller = Seller::findOrFail($id);
        $title  = ucfirst($seller->name) ." transaction";
        $transactions = Transaction::sellers()->where('seller_id', $id)->latest()->with('seller')->paginate(paginate_number());
        return view('admin.report.index', compact('title', 'transactions'));
    }

    public function sellerWithdraw(int $id) :View
    {
        $seller    = Seller::findOrFail($id);
        $title     = ucfirst($seller->name) ." all withdraw log";
        $withdraws = Withdraw::where('status', '!=', 0)->where('seller_id', $id)->latest()->with('method', 'seller')->paginate(paginate_number());
        return view('admin.withdraw.index', compact('title', 'withdraws'));
    }

    public function sellerAllDigitalProduct(int $id) :View
    {
        $seller = Seller::findOrFail($id);
        $title  = ucfirst($seller->name)." digital products";
        $inhouseDigitalProducts = Product::with('category')->sellerProduct()->where('seller_id', $id)->digital()->latest()->with('seller')->paginate(paginate_number());
        return view('admin.digital_product.index', compact('title', 'inhouseDigitalProducts'));
    }

    public function sellerDigitalProductOrder(int $id) :View
    {
        $seller = Seller::findOrFail($id);
        $title = ucfirst($seller->name)." digital product orders";
        $orders = Order::sellerOrder()->digitalOrder()->whereHas('digitalProductOrder',function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('admin.digital_order.index', compact('title', 'orders'));
    }

    public function sellerPhysicalProductOrder(int $id) :View
    {
        $seller = Seller::findOrFail($id);
        $title  = ucfirst($seller->name)."  product orders";
        $orders = Order::sellerOrder()->physicalOrder()->whereHas('orderDetails',function($q) use ($seller){
            $q->whereHas('product', function($query) use ($seller){
                $query->where('seller_id', $seller->id);
            });
        })->orderBy('id', 'DESC')->with('customer')->paginate(paginate_number());
        return view('admin.seller_order.index', compact('title', 'orders'));
    }

    public function ticket(int $id) :View
    {
        $user = Seller::findOrFail($id);
        $title = ucfirst($user->name)." all support tickets";
        $supportTickets = SupportTicket::where('seller_id', $id)->latest()->paginate(paginate_number());
        return view('admin.support_ticket.index', compact('title', 'supportTickets'));
    }

  


    public function bestSeller(int $id) :RedirectResponse
    {
        $seller = Seller::findOrFail($id);
        $seller->best_seller_status = $seller->best_seller_status == 1 ? 2 : 1;
        $seller->save();

        return back()->with('success',translate('Best seller status has been updated'));
    }


    public function sellerBalanceUpdate(Request $request) :RedirectResponse
    {
        $request->validate([
            'seller_id'    => 'required',
            'balance_type' => 'required|in:1,2',
            'amount'       => 'required|numeric|gt:0',
        ]);
        $seller = Seller::findOrFail($request->seller_id);
        if($request->balance_type == 1){
            $seller->balance += $request->amount;
            $seller->save();
            $transaction = Transaction::create([
                'seller_id'          => $seller->id,
                'amount'             => $request->amount,
                'post_balance'       => $seller->balance,
                'transaction_type'   => Transaction::PLUS,
                'transaction_number' => trx_number(),
                'details'            => 'Balance Added by admin',
            ]);
        }else{
            if($request->amount >  $seller->balance  ){
                return back()->with('error',translate('Seller Doesnot have enough balance to withdraw'));   
            }
            $seller->balance -= $request->amount;
            $seller->save();
            $transaction = Transaction::create([
                'seller_id'          => $seller->id,
                'amount'             => $request->amount,
                'post_balance'       => $seller->balance,
                'transaction_type'   => Transaction::MINUS,
                'transaction_number' => trx_number(),
                'details' => 'Balance subtract by admin',
            ]);
        }

        return back()->with('success',translate('Seller balance has been updated'));
    }
}
