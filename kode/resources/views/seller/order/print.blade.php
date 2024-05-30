@extends('seller.layouts.app')
@section('main_content')
@php
      $shopSetting =App\Models\SellerShopSetting::where('seller_id', Auth::guard('seller')->user()->id)->firstOrFail();
@endphp
<div class="page-content">
  <div class="container-fluid">

      <div class="row">
          <div class="col-12">

            <section class="mt-5" id='DivIdToPrint'
              style="
                width: 970px;
                height: fit-content;
                padding: 50px 0;
                background: rgb(255, 255, 255);
                margin: 0 auto;
                box-shadow: 0px 0px 9px 2px rgba(0, 0, 0, 0.33);
                position: relative;
              "
            >
            <div style="position: absolute;right: 3%;
            bottom: 3%;">
              <button type="button" id="btn" value="Print" onclick="printDiv();" style="border: none;padding: 5px 10px;font-size: 16px;background: #182652;color: #fff;display: flex;align-items: center;border-radius: 5px;column-gap: 7px;cursor: pointer;"><i class="las la-print" style="font-size: 20px;"></i>{{translate('Print')}}</button>
            </div>
              <div
                style="
                        display: flex;
                        align-items: center;
                        column-gap: 10px;
                        padding: 0 30px;
                        background: #777;
                        padding: 10px;
                "
              >
              <img
                  src="{{show_image(file_path()['shop_first_image']['path'].'/'.@$shopSetting->shop_first_image ,file_path()['shop_first_image']['size'] )}}"
                  alt="{{$general->site_logo}}"
                  style="width: 140px; height: 40px"
                />
              </div>
              <div
                style="
                  width: 100%;
                  height: 40px;
                  background-color: #263e6e;
                  position: relative;
                  margin: 30px 0;
                "
              >
                <div
                  style="
                    position: absolute;
                    right: 10%;
                    top: 0;
                    background: white;
                    width: 300px;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                  "
                >
                  <p style="font-size: 36px; font-weight: 600; line-height:1; margin-bottom:0;">{{translate('INVOICE')}}</p>
                </div>
              </div>

              <div
                style="
                  padding: 0 30px;
                  display: flex;
                  align-items: stretch;
                  justify-content: space-between;
                "
              >
                <div>
                  <p
                    style="
                      font-size: 20px;
                      font-weight: 600;
                      color: #000;
                      margin: 0;
                      padding-bottom: 10px;
                    "
                  >
                    {{translate('Invoice To')}}:
                  </p>
                  <h3 style="font-size: 18px; font-weight: 600; color: #555; margin: 0">
                    {{@$order->billing_information->first_name ?? "N/A" }} {{@$order->billing_information->last_name ?? "N/A"}}
                  </h3>
                  <address
                    style="
                      display: flex;
                      flex-direction: column;
                      row-gap: 5px;
                      color: #333;
                    "
                  >
                    <span>{{@$order->billing_information->address}}, {{@$order->billing_information->country}} ,{{@$order->billing_information->city}} {{$order->billing_information->zip}}</span>
                    <span>{{@$order->billing_information->email}}</span>
                    <span>{{@$order->billing_information->phone}}</span>
                  </address>
                </div>

                <div>
                  <p style="font-size: 15px; line-height:1; color: #555; font-weight: 500; margin: 0; width:250px; display:flex; align-items:center; justify-content:space-between">
                    {{translate('Invoice')}}#
                    <span style="font-size: 14px; padding-left: 30px; color: #333"
                      >{{$order->order_id}}</span
                    >
                  </p>
                  <p
                    style="
                     font-size: 15px; line-height:1; color: #555; font-weight: 500; margin: 0;
                      width:250px; display:flex; align-items:center; justify-content:space-between; padding-top: 10px;
                    "
                  >
                    {{translate('Date')}}
                    <span style="font-size: 14px; padding-left: 30px; color: #333"
                      >{{get_date_time($order->created_at, 'd-m-Y')}}</span
                    >
                  </p>
                  <p
                    style="
                      font-size: 15px;
                      color: #555;
                      font-weight: 500;
                      margin: 0;
                      padding-top: 5px;
                      width:250px; display:flex; align-items:center; justify-content:space-between;
                      column-gap: 30px;

                    "
                  >
                    {{translate('Status')}}
                    @if($order->status == App\Models\Order::PLACED)
                      <span class="badge badge-soft-primary">{{translate('Placed')}}</span>
                    @elseif($order->status == App\Models\Order::CONFIRMED)
                      <span class="badge badge-soft-info">{{translate('Confirmed')}}</span>
                    @elseif($order->status == App\Models\Order::PROCESSING)
                      <span class="badge badge-soft-secondary">{{translate('Processing')}}</span>
                    @elseif($order->status == App\Models\Order::SHIPPED)
                      <span class="badge badge-soft-warning">{{translate('Shipped')}}</span>
                    @elseif($order->status == App\Models\Order::DELIVERED)
                      <span class="badge badge-soft-success">{{translate('Delivered')}}</span>
                    @elseif($order->status == App\Models\Order::CANCEL)
                      <span class="badge badge-soft-danger">{{translate('Cancel')}}</span>
                    @endif
                  </p>
                </div>
              </div>

              @if($order->shipping_deliverie_id)
                <div style="padding: 0 30px">
                  <h1 style=" font-size: 16px; font-weight: 600; text-align: center;margin: 20px;">{{translate('Shipping Information')}}</h1>
                  <table style=" border-collapse: collapse; width: 100%">
                    <tr>
                      <th
                        style="
                          border: 1px solid #dddddd;
                          border-style:solid !important;
                          text-align: center;
                          padding: 8px;
                          color: #535353;
                          font-weight: 500;
                        ">{{translate('Name')}}
                      </th>

                      <th
                        style="
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                          text-align: center;
                          padding: 8px;
                          color: #535353;
                          font-weight: 500;
                        "
                      >
                        {{translate('Method')}}
                      </th>

                      <th
                        style="
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                          text-align: center;
                          padding: 8px;
                          color: #535353;
                          font-weight: 500;
                        "
                      >
                        {{translate('Duration')}}
                      </th>
                      <th
                        style="
                          text-align: center;
                          padding: 8px;
                          color: #535353;
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                          font-weight: 500;
                        "
                      >
                        {{translate('Price')}}
                      </th>
                    </tr>
                    <tr style="border: 1px solid #dddddd; border-style:solid !important;">
                      <td
                        style="
                          text-align: center;
                          padding: 8px;
                          color: #969696;
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                        "
                      >{{(@$order->shipping->name)}}</td>
                      <td
                        style="
                          text-align: center;
                          padding: 8px;
                          color: #969696;
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                        "
                      >{{(@$order->shipping->method->name)}}</td>
                      <td
                        style="
                          text-align: center;
                          padding: 8px;
                          color: #969696;
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                        "
                      >{{(@$order->shipping->duration)}} {{translate('Days')}}</td>
                      <td
                        style="
                          text-align: center;
                          padding: 8px;
                          color: #969696;
                          border: 1px solid #dddddd;
                           border-style:solid !important;
                        ">
                        {{show_currency()}}{{@round(short_amount(@$order->shipping->price))}}
                      </td>
                    </tr>
                  </table>
                </div>
              @endif


              <div style="padding: 30px 30px 0">
                <h1 style=" font-size: 16px;font-weight: 600;text-align: center;margin: 0;">{{translate('Product Information')}}</h1>
                <table style="margin-top: 20px; border-collapse: collapse; width: 100%">
                  <tr>
                    <th
                      style="
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                        text-align: center;
                        padding: 8px;
                        color: #535353;
                        font-weight: 500;
                      ">{{translate('Item Description')}}
                    </th>
                    <th
                      style="
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                        text-align: center;
                        padding: 8px;
                        color: #535353;
                        font-weight: 500;
                      "
                    >
                      {{translate('Price')}}
                    </th>
                    <th
                      style="
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                        text-align: center;
                        padding: 8px;
                        color: #535353;
                        font-weight: 500;
                      "
                    >
                      {{translate('Qty')}}
                    </th>
                    <th
                      style="
                        text-align: center;
                        padding: 8px;
                        color: #535353;
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                        font-weight: 500;
                      "
                    >
                      {{translate('Total')}}
                    </th>
                  </tr>

                  @php
                    $subtotal = 0;
                  @endphp

                  @foreach($orderDeatils as $orderDetail)
                  <tr style="border: 1px solid #dddddd; border-style:solid !important;">
                    <td
                      style="
                        text-align: center;
                        padding: 8px;
                        color: #969696;
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                      "
                    >{{($orderDetail->product->name)}}</td>
                    <td
                      style="
                        text-align: center;
                        padding: 8px;
                        color: #969696;
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                      "
                    >{{show_currency()}}{{round(short_amount($orderDetail->total_price/$orderDetail->quantity))}}
                    </td>
                    <td
                      style="
                        text-align: center;
                        padding: 8px;
                        color: #969696;
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                      "
                    >{{$orderDetail->quantity}}</td>
                    <td
                      style="
                        text-align: center;
                        padding: 8px;
                        color: #969696;
                        border: 1px solid #dddddd;
                         border-style:solid !important;
                      "
                    >
                      {{show_currency()}}{{round(short_amount($orderDetail->total_price),2)}}
                    </td>
                  </tr>
                  @php
                    $subtotal += $orderDetail->total_price;
                  @endphp
                  @endforeach
                  <tr style="border: 1px solid #dddddd; border-style:solid !important;">
                    <td style="text-align: left; padding: 8px"></td>
                    <td style="text-align: left; padding: 8px"></td>
                    <td style="
                        text-align: left;
                        padding: 8px;
                        font-weight: 600;
                        text-align: center;
                      ">{{translate('Total')}} :</td>
                    <td
                      style="
                        text-align: left;
                        padding: 8px;
                        font-weight: 600;
                        text-align: center;
                      "
                    >
                    {{show_currency()}}{{round(short_amount($subtotal),2)}}
                    </td>
                  </tr>
                </table>
              </div>

              <div
                style="
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                  padding: 0 30px;
                  margin: 35px 0;
                "
              >
                <div style="width: 50%">
                  <div style="padding: 0 30px">
                    @php
                        $invoiceLogos = json_decode($general->invoice_logo, true);
                    @endphp
                      @if($order->payment_type != 1)
                          @if($order->status == 5)
                            @if($invoiceLogos['Delivered'])
                                <img src="{{ asset('assets/images/backend/invoiceLogo/'.$invoiceLogos['Delivered']) }}" alt="{{$invoiceLogos['Delivered']}}" style="width: 100px; margin-left: 50px"/>
                            @endif
                          @elseif($order->payment_status == 2)
                            @if($invoiceLogos['paid'])
                                <img src="{{ asset('assets/images/backend/invoiceLogo/'.$invoiceLogos['paid']) }}" alt="{{$invoiceLogos['paid']}}" style="width: 100px; margin-left: 50px"/>
                            @endif
                          @elseif($order->payment_status == 1)
                            @if($invoiceLogos['unpaid'])
                              <img src="{{ asset('assets/images/backend/invoiceLogo/'.$invoiceLogos['unpaid']) }}" alt="{{$invoiceLogos['unpaid']}}" style="width: 100px; margin-left: 50px"/>
                            @endif
                          @endif
                      @else
                          @if($invoiceLogos['Cash On Delivery'])
                              <img src="{{ asset('assets/images/backend/invoiceLogo/'.$invoiceLogos['Cash On Delivery']) }}" alt="{{$invoiceLogos['Cash On Delivery']}}" style="width: 100px; margin-left: 50px"/>
                          @endif
                      @endif

                  </div>
                </div>

                <div style="width: 50%; text-align: right">
                  <p style="
                      font-size: 14px;
                      font-weight: 500;
                      display: flex;
                      align-items: center;
                      justify-content: flex-end;
                      padding-right: 10px;
                      margin: 0;
                      color: #555;
                    ">
                    {{translate('Sub Total')}} :
                    <small style="padding-left: 30px; color: #333; font-size: 14px"
                      >{{show_currency()}}{{round(short_amount($subtotal),2)}}</small
                    >
                  </p>
                  @if($order->shipping_deliverie_id)
                    <p
                      style="
                        font-size: 14px;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                        justify-content: flex-end;
                        padding-right: 10px;
                        margin: 0;
                        color: #555;
                      "
                    >
                      {{translate('Shipping Cost')}} :
                      <small style="padding-left: 30px; color: #333; font-size: 14px">{{show_currency()}}{{round(short_amount($order->shipping_charge),2)}}</small>
                    </p>
                  @endif


                  <p
                    style="
                      font-size: 20px;
                      font-weight: 600;
                      display: flex;
                      align-items: center;
                      justify-content: flex-end;
                      padding-right: 10px;
                    "
                  >
                    {{translate('Total')}}:
                    <small style="font-weight: 700; padding-left: 30px">{{show_currency()}}{{round(short_amount($order->shipping_charge + $subtotal))}}</small>
                  </p>
                </div>
              </div>
            </section>
          </div>
      </div>
  </div>
</div>
@endsection


@push('script-push')
<script>
  "use strict";
  function printDiv()
  {
    var divToPrint=document.getElementById('DivIdToPrint');
    var newWin=window.open('','Print-Window');
    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
    newWin.document.close();
  }
</script>
@endpush

