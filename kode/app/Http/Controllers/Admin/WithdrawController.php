<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdraw;

use App\Models\Transaction;
use App\Models\Seller;
use App\Models\GeneralSetting;
use App\Http\Utility\SendMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WithdrawController extends Controller
{

    public function __construct(){
        $this->middleware(['permissions:view_log'])->only('index','pending','approved','rejected','detail','approvedBy','search');
        $this->middleware(['permissions:update_log'])->only('rejectedBy');

    }

    public function index() :View
    {
        $title     = "All withdraw log";
        $withdraws = Withdraw::search()->with('currency')->date()->where('status', '!=', 0)->latest()->with('method', 'seller')->paginate(paginate_number())->appends(request()->all());
        return view('admin.withdraw.index', compact('title', 'withdraws'));
    }

    public function pending():View
    {
        $title     = "All withdraw log";
        $withdraws = Withdraw::with('currency')->search()->date()->where('status', '!=', 0)->pending()->latest()->with('method', 'seller')->paginate(paginate_number())->appends(request()->all());
        return view('admin.withdraw.index', compact('title', 'withdraws'));
    }

    public function approved():View
    {
        $title     = "All withdraw log";
        $withdraws = Withdraw::search()->date()->with('currency')->where('status', '!=', 0)->approved()->latest()->with('method', 'seller')->paginate(paginate_number())->appends(request()->all());
        return view('admin.withdraw.index', compact('title', 'withdraws'));
    }

    public function rejected():View
    {
        $title     = "All withdraw log";
        $withdraws = Withdraw::search()->date()->where('status', '!=', 0)->rejected()->latest()->with('method', 'seller')->paginate(paginate_number())->appends(request()->all());
        return view('admin.withdraw.index', compact('title', 'withdraws'));
    }

    public function detail(int $id):View
    {
        $title    = "Withdraw Details";
        $withdraw = Withdraw::where('status', '!=', 0)->where('id', $id)->firstOrFail();
        return view('admin.withdraw.detail', compact('title', 'withdraw'));
    }

    public function approvedBy(Request $request) :RedirectResponse
    {
        $request->validate(['id' => 'required|exists:withdraws,id']);
        $general = GeneralSetting::first();

        $withdraw = Withdraw::where('id',$request->id)->where('status',2)->firstOrFail();
        $withdraw->status = 1;
        $withdraw->save();
        $seller = Seller::where('id', $withdraw->seller_id)->firstOrFail();
        $mailCode = [
            'trx'              => $withdraw->trx,
            'amount'           => ($withdraw->amount),
            'charge'           => ($withdraw->charge),
            'currency'         => $general->currency_name,
            'rate'             => ($withdraw->rate),
            'method_name'      => $withdraw->method->name,
            'method_currency'  => $withdraw->currency->name,
            'method_amount'    => ($withdraw->final_amount),
            'user_balance'     => ($seller->balance)
        ];
        SendMail::MailNotification($seller,'WITHDRAW_APPROVED',$mailCode);
        return back()->with('success',translate('Withdraw has been approved'));
    }

    public function rejectedBy(Request $request) :RedirectResponse
    {
        $request->validate(['id' => 'required|exists:withdraws,id']);
        $general  = GeneralSetting::first();
        $withdraw = Withdraw::where('id',$request->id)->where('status',2)->firstOrFail();
        $withdraw->status    = 3;
        $withdraw->feedback  = $request->details;
        $withdraw->save();

        $seller = Seller::find($withdraw->seller_id);
        $seller->balance += $withdraw->amount;
        $seller->save();

        $transaction = Transaction::create([
            'seller_id'          => $seller->id,
            'amount'             => $withdraw->amount,
            'post_balance'       => $seller->balance,
            'transaction_type'   => Transaction::PLUS,
            'transaction_number' => $withdraw->trx_number,
            'details'            => short_amount($withdraw->amount) . ' ' . $general->currency_name . ' Refund for withdrawal rejected',
        ]);
        $mailCode = [
            'trx'              => $withdraw->trx,
            'amount'           => short_amount($withdraw->amount),
            'charge'           => short_amount($withdraw->charge),
            'currency'         => $general->currency_name,
            'rate'             => short_amount($withdraw->rate),
            'method_name'      => $withdraw->method->name,
            'method_currency'  => $withdraw->currency->name,
            'method_amount'    => short_amount($withdraw->final_amount),
            'user_balance'     => short_amount($seller->balance)
        ];
        SendMail::MailNotification($seller,'WITHDRAW_APPROVED',$mailCode);

        return back()->with('success',translate('Withdraw has been rejected.'));
    }



}
