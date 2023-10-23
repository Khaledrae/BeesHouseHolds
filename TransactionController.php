<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //
    public function viewTransactions()
    {
        if (session('userlevel')==='Buyer') {
            $alltransactions = Transaction::where('doneby', '=', session('userid'))->get();
        }
        elseif (session('userlevel')==='Team') {
            $alltransactions = Transaction::latest()->get();
        }
        return view('transactions', ['alltransactions'=>$alltransactions]);
    }
    public function Token(){
        $consumerkey = "DYfPU3YBWdJIDyOjY6Nu4XwqTuDwopbc";
        $consumerSecret ="SKfKAGxBnYAYH1GV";
          $url ="https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $response = Http::withBasicAuth($consumerkey, $consumerSecret)->get($url);
        return $response["access_token"];

    }
    public function initiateSTKPush(){
        $accesstoken = self::Token();
        $url ="https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $businessShortCode=174379;
        $timestamp = Carbon::now()->format('Ymdhis');
        $partyA = 254726104430;
        $partyB = 174379;
        $TransactionType = "CustomerPayBillOnline";
        $amount = 1;
        $PhoneNo = 254726104430;
        $password = base64_encode($businessShortCode.$passkey.$timestamp);
        $callBackUrl = "https://www.graco.com/gb/en.html";
        $accountRefference = "BeesHouseHolds";
        $transactionDescription = "Purchase";
        $response = Http::withToken($accesstoken)->post($url, 
        [
        'BusinessShortCode'=>$businessShortCode,
        'PartyA'=> $partyA,
        'PhoneNumber' => $PhoneNo,
        'PartyB' => $partyB,
        'Timestamp'=>$timestamp,
        'TransactionType' => $TransactionType,
        'Amount' => $amount,
        'AccountRefference' => $accountRefference,
        'CallBackURL' => $callBackUrl,
        'TransactionDesc' => $transactionDescription,
        'Password' => $password
        ]);
        return $response;
    }
    public function stkCallBack(){

    }
}
