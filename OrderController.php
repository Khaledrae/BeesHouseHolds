<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function getUsersDraftOrders($userid){
        $allorders = Order::where('user', '=', session('userid'), 'AND', 'status', '=', 'Draft')->get();
        $num = count($allorders);
        return $num;
    }
    public function checkForItemInCart($userid, $itemid){
        $allorders = Order::where('user', '=', session('userid'), 'AND', 'status', '=', 'Draft', 'AND', 'itemid', '=', "$itemid")->limit('1')->get();
        if (count($allorders)>=1) {
            foreach ($allorders as $o) {
                $num = $o['quantity'];
                $orderid = $o['id'];
            }    
        }
        else {
            $num = 0;
        }
        return ['orderid'=>$orderid, 'orderqty'=>$num];
    }
    public function getUsersDraftOrderNo($userid){
        $allorders = Order::where('user', '=', session('userid'), 'AND', 'status', '=', 'Draft')->limit('1')->get();
        foreach ($allorders as $o) {
            $orderno = $o['orderno'];
        }
        return $orderno;
    }
    public function saveOrder(Request $request)
    {
        if (!session('userid')) {
            //session()->put('redirectto', 'add-to-duffle')
            return response()->json([
                'status'=>"400",
                'msg'=>"Please Log In",
                'orders'=>'0'
            ]);
        }
        
        $userid = session('userid');
        $itemid = $request->input('itemid');
        if (self::getUsersDraftOrders($userid)>=1) {
            $orderno = self::getUsersDraftOrderNo($userid);
        }
        else {
            $orderno = Order::max('orderno');
            $orderno++;   
        }
        if (self::checkForItemInCart($userid, $itemid)['orderqty']>=1) {
            $qty = self::checkForItemInCart($userid, $itemid)['orderqty'];
            $orderid = self::checkForItemInCart($userid, $itemid)['orderid'];
            $order = Order::find($orderid);
            $qty++;
        }
        else {
            $order = new Order;
            $qty = 1;
            $order->quantity = $qty;
            $order->itemid = $itemid;
            $order->orderno = $orderno;
            $order->status = "Draft";
            $order->user = session('userid');
        }
            if ($order->save()) {
                $myorders = Order::where('user','=', session('userid'))->get();
                $numoforders = count($myorders);
                return response()->json([
                    'status'=>"200",
                    'msg'=>"Order Posted Successfully",
                    'orders'=>$orderno
                ]);
            }
            else {
                $myorders = Order::where('user','=', session('userid'))->get();
                $numoforders = count($myorders);
                return response()->json([
                    'status'=>"410",
                    'msg'=>"Failed To Add To Cart",
                    'orders'=>$orderno
                ]);
            }
    }
    public function getOrders()
    {
        if (session('userlevel')==='Buyer') {
            $allorders = Order::where('user', '=', session('userid'))->get();
        }
        elseif (session('userlevel')==='Team') {
            $allorders = Order::latest()->get();
        }
        return view('orders', ['allorders'=>$allorders]);
    }
    public function postOrderParcel(Request $request){
        $orderno = $request->input('orderno');
        $order = Order::where('orderno', '=', $orderno)->update(['status'=>"Sent"]);
    }
    public function updateOrderQuantity(Request $request){
        $orderid = $request->input('orderid');
        $quantity = $request->input('newquantity');
        $userid = session('userid');
        $order = Order::find($orderid);
        if($order){
            $order->quantity= $quantity;
            if($order->save()){
                $orders = self::getUsersDraftOrders($userid);
                return response()->json([
                    'status'=>"200",
                    'msg'=>"Cart Updated Successfully",
                    'orders'=>$orders
                ]);
            }
            else {
                return response()->json([
                    'status'=>"410",
                    'msg'=>"Failed To Update Cart",
                    'orders'=>"Nill"
                ]);
            }
        }
        else{
            return response()->json([
                'status'=>"418",
                'msg'=>"Order Not Found",
                'orders'=>"Nill"
            ]);
        }
    }
    public function viewOrder($orderno){
        //$order = Order::where('orderno', '=', $orderno)->get();
        $order = Order::where('orderno','=', $orderno)
        ->join('items', 'orders.itemid', '=', 'items.id')
        ->select('orders.*', 'items.*')
        ->get();
        return view('order', ['orderno'=>$orderno, 'allitems'=>$order]);
    }
    public function getDeliveryCost($deliverystore){
        $cost = 0;
        switch ($deliverystore) {
            case '1':
                $cost = 100;
                break;
            
            default:
                $cost = 50;
                break;
        }
        return $cost;
    }
    public function saveDeliveryPoint(Request $request){
            $orderid = $request->input('orderno');
            $deliverystore = $request->input('deliverystore');
            $userid = session('userid');
            $deliverycost = self::getDeliveryCost($deliverystore);
            if (!session('userid')) {
                return redirect('login');
            }
            $order = Order::where('orderno', '=', $orderid)->get();
            if(count($order)>=1){
                $delivery = new Delivery;
                $delivery->orderno = $orderid;
                $delivery->addedby = $userid;
                $delivery->location = $deliverystore;
                $delivery->status = "Pending";
                $delivery->cost = $deliverycost;
                if($delivery->save()){
                    $orders = self::getUsersDraftOrders($userid);
                    return response()->json([
                        'status'=>"200",
                        'msg'=>"Delivery Point Set Successfully",
                        'orders'=>$orders
                    ]);
                }
                else {
                    return response()->json([
                        'status'=>"410",
                        'msg'=>"Failed To Update Delivery Point",
                        'orders'=>"Nill"
                    ]);
                }
            }
            else{
                return response()->json([
                    'status'=>"418",
                    'msg'=>"Order Not Found",
                    'orders'=>"Nill"
                ]);
            }
        }
}
