<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    //
    public function viewAllDeliveries()
    {
        if (session('userlevel')==='Buyer') {
            $allDeliveries = Delivery::where('addedby', '=', session('userid'))->get();
        }
        elseif (session('userlevel')==='Team') {
            $allDeliveries = Delivery::latest()->get();
        }
        else{
            return redirect('signup');
        }
        return view('transactions', ['alldeliveries'=>$allDeliveries]);
    }
    public function getDeliveryGuy()
    {
        $deliveryguy = User::where('level', '=', 'Delivery');
        $lastdelivery = "";
        foreach ($deliveryguy as $d) {
            $dlast = Delivery::latest()->where('deliveredby', '=', $d['id'])->limit('1')->get();
            foreach ($dlast as $trip) {
                $dprev = $trip['created_at'];
                if ($dprev<=$lastdelivery) {
                    $todeliver = $d['id'];
                }
            }   
        }
        return $todeliver;
    }
    public function saveNewDelivery(Request $request)
    {
        $delivery = New Delivery;
        $deliveryguy =self::getDeliveryGuy();
        $delivery->handledby = $deliveryguy;
        $delivery->orderno = $request->input('orderno');
        $delivery->location = $request->input('location');
        $delivery->addedby = session('userid');
        $delivery->status = "Pending";
        $delivery->cost = $request->input('cost');
        if ($delivery->save()) {
            return view('orders')->with('msg', 'Delivery Set Successfully');
        }
        else {
            return view('orders')->with('msg', 'Failed To Initiate Delivery');
        }
    }
}
