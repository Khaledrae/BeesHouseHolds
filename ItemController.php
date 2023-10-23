<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Image;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
public function getAllItems()
{
    $items = Item::all();
    $categories = Categories::all();
    return view('shop', ['allitems'=>$items, 'categoryoptions'=>$categories]);
}
public function index()
{
    $items = Item::all();
    $latestitems = Item::latest()->get();
    $categories = Categories::all();
    return view('/welcome', ['allitems' => $items, 'latestitems'=>$latestitems, 'categoryoptions'=>$categories]);
}
public function saveItem(Request $request)
{
    $item = new Item;
    $itemtitle = $request->input('piecetitle');
    $itemmaterial = $request->input('material');
    $itemprice = $request->input('price');
    $itemweight = $request->input('weight');
    $item->title = $itemtitle;
    $item->price = $itemprice;
    $item->categories = $request->input('categories');
    $item->weight = $itemweight;
    $item->material = $itemmaterial;
    $item->availablepieces = $request->input('stock');
        if ($item->save()) {
        $currentitem = Item::where('title', '=', $itemtitle)
            ->where('price', '=', $itemprice)
            ->where('weight', '=', $itemweight)
            ->get();
    
        foreach ($currentitem as $c) {
            $itemid = $c['id'];
        }
    
        if ($request->hasfile('pieceimages')) {
            $imagesarr = $request->file('pieceimages');
            $num = 0;
            $msg = "";
    
            foreach ($imagesarr as $image) {
                $newImage = new Image;
                $newImage->item = $itemid;
                $destination = 'images/products/';
                $imagename = $image->getClientOriginalName();
                $image->move($destination, $imagename);
                $newImage->title = $imagename;
                $num++;
                if (!$newImage->save()) {
                    $msg = 'Failed To Upload Images ('.$num.')';
                }
                else {
                    $msg = 'Image Added Successfully ('.$num.')';
                }
            }
        } else {
            $msg= 'No Images To Upload For This Item';
        }
    }      
    return redirect('shop')->with('msg', $msg);
}            
    public function getItemDetails($itemid)
    {
        $itemdetails = Item::where('id','=',$itemid)->get();
        $images = Image::where('item','=',$itemid)->get();
        return view('product', ['itemdetails'=>$itemdetails, 'images'=>$images]);
    }
    public function likeItem(Request $request){
        $pieceid = $request->input('itemid');
        $getpieces = Item::find($pieceid);
        if ($getpieces) {
            $numoflikes = $getpieces['likes'];
            $newnum = $numoflikes+1;
            $getpieces->likes = $newnum;
            $getpieces->save();
            return response()->json([
                "status"=>"200",
                "piecedetails"=>$getpieces
            ]);
        }
        else {
            return response()->json([
                "status"=>"404",
                "msg"=>"Piece Not Found"
            ]);
        }
    }
    public function dislikeItem(Request $request){
        $itemid = $request->input('itemid');
        $getitems = item::find($itemid);
        if ($getitems) {
            $numoflikes = $getitems['dislikes'];
            $getitems->dislikes = ++$numoflikes;
            $getitems->save(); 
            return response()->json([
                "status"=>"200",
                "msg"=>"item Found",
                "itemdetails"=>$getitems
            ]);
        }
        else {
            return response()->json([
                "status"=>"404",
                "msg"=>"item Not Found"
            ]);
        }
    }
    /*public function imageUploader(Request $request){
        $put = $request->input('images');
        if ($request->hasfile('images')) {
            $imagesarr = $request->file('images');
            $numofimages = count($imagesarr);
            for ($i=0; $i < $numofimages; $i++) { 
                $image = new Image;
                $image->item  = $itemid;
                $destination = 'images/products/';
                $pieceimage = $imagesarr[$i]->file('pieceimages');
                $imagename = $pieceimage->getClientOriginalName();
                $pieceimage->move("$destination", $imagename);
                $image->title = $imagename;
                $image->save();
            }
           return response()->json([
                "status"=>"200",
                "images"=>"$imagesarr",
                "num"=>$numofimages
            ]);
        }
        else {
            return response()->json([
                "status"=>"404",
                "msg"=>"item Not Found",
                "images"=>$put
            ]);
        }
    }*/
}
