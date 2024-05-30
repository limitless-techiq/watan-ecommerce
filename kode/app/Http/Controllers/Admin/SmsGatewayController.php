<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsGateway;
use App\Models\GeneralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SmsGatewayController extends Controller
{
    public function __construct(){

        $this->middleware(['permissions:view_configuration'])->only('index','globalSMSTemplate','globalSMSTemplateStore');
        $this->middleware(['permissions:update_configuration'])->only('edit','update','defaultGateway');
    }

    public function index() :View
    {
    	$title       = translate("SMS Gateway list");
    	$smsGateways = SmsGateway::latest()->get();
    	return view('admin.sms_gateway.index', compact('title', 'smsGateways'));
    }

    public function edit(int | string $id) :View
    {
        $title      = translate("API Gateway update");
    	$smsGateway = SmsGateway::findOrFail($id);
    	return view('admin.sms_gateway.edit', compact('title', 'smsGateway'));
    }

    public function update(Request $request, int $id) :RedirectResponse
    {

    	$this->validate($request, [
            'status' => 'required|in:1,0',
        ]);
    	$smsGateway = SmsGateway::findOrFail($id);
    	$parameter = [];
        foreach ($smsGateway->credential as $key => $value) {
            $parameter[$key] = $request->sms_method[$key];
        }
        $smsGateway->credential = $parameter;
        $smsGateway->status     = $request->status;
        $smsGateway->save();

        return back()->with('success',translate('SMS Gateway has been updated'));
    }

    public function defaultGateway(Request $request) :RedirectResponse
    {
    	$smsGateway = SmsGateway::findOrFail($request->id);
    	$setting    = GeneralSetting::first();
    	$setting->sms_gateway_id = $smsGateway->id;
    	$setting->save();

        return back()->with('success',translate('Default SMS Gateway has been updated'));
    }

    public function globalSMSTemplate() :View
    {
        $title = "SMS Global template";
        return view('admin.sms_gateway.global_template', compact('title'));
    }

    public function globalSMSTemplateStore(Request $request) :RedirectResponse
    {
        $this->validate($request,[
            'sms_template' => 'required',
        ]);
        $general               = GeneralSetting::first();
        $general->sms_template = $request->sms_template;
        $general->save();
        return back()->with('success',translate('Global SMS template has been updated'));
    }
}