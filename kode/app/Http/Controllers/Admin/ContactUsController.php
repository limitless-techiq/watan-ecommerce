<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Models\GeneralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactUsController extends Controller
{
    private $contactUs;
    public function __construct(ContactUs $contactUs)
    {
        $this->contactUs = $contactUs;
        $this->middleware(['permissions:view_support'])->only('index',"show");
        $this->middleware(['permissions:delete_support'])->only('destroy');
    }

    public function index() :View
    {
        $title    = "Contact Message List";
        $contacts = $this->contactUs->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
            return $q->where('name','like',$searchBy)
                      ->orWhere('email','like',$searchBy)
                      ->orWhere('subject','like',$searchBy);
                        
            })->orderBy('id','DESC')->paginate(paginate_number())->appends(request()->all());
        return view('admin.contact_us.index', compact('title','contacts'));
    }

    public function show(int $id) :View
    {
        $title   = "Message show page";
        $contact = $this->contactUs->where('id', $id)->first();
        return view('admin.contact_us.show', compact('title','contact'));
    }

    public function destroy() :RedirectResponse
    {
        $contact = $this->contactUs->where('id', request()->id)->first();
        $contact->delete();
        return back()->with('success',translate('Conatct message deleted successfully'));
    }


    public function sendMail(Request $request) :RedirectResponse
    {

        $request->validate([
            'id'      => 'required|exists:contact_us,id',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $contact = $this->contactUs->where('id', request()->id)->first();

        $general = GeneralSetting::first();

        $header = "From: <$general->mail_from> \r\n";
        $header .= "Reply-To: $contact->email \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        @mail($contact->email, $request->subject, build_dom_document($request->details,'send_mail'.rand(10,22222)), $header);

        return back()->with('success',translate('Email will be sent to the email'));    }
    
}
