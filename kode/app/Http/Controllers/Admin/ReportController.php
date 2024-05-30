<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\View\View;

class ReportController extends Controller
{

    public function __construct(){
        $this->middleware(['permissions:view_log']);
    }
    public function userTransaction() :View
    {
        $title        = "User transactions";
        $transactions = Transaction::latest()->users()->search()->date()->latest()->with('user')->paginate(paginate_number());
        return view('admin.report.index', compact('title', 'transactions'));
    }

    public function guestTransaction() :View
    {
        $title        = "User transactions";
        $transactions = Transaction::latest()->guest()->search()->date()->latest()->with('user')->paginate(paginate_number());
        return view('admin.report.index', compact('title', 'transactions'));
    }


    public function sellerTransaction() :View
    {
        $title        = "Seller transactions";
        $transactions = Transaction::sellers()->search()->date()->latest()->with('seller')->paginate(paginate_number());
        return view('admin.report.index', compact('title', 'transactions'));
    }

  
}
