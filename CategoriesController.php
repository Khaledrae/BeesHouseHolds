<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Item;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    //
    public function getCategories()
    {
        $categories = Categories::all();
        return view('categories', ['categories'=>$categories]);
    }
    public function saveNewCategory(Request $request)
    {
        $category =  new Categories();
        $category->name = request('categoryname');
        if($category->save()){
            return redirect('/categories')->with('msg', "Category Added Successfully");
        }
        else{
            //$request->validate(['name'=>'required']);
            error_log(request('categoryname'));
            //$categorydetails = $request->all();
            //Categories::create($categorydetails);
            return redirect('/categories')->with('msg', "Failed To Add Category");
        }
    }
    public function getCategoryItems($categoryid)
    {
        $allpieces = Item::where('categories','LIKE',"%$categoryid%")->get();
        $categories = Categories::all();
        return view('/shop', ['allitems' => $allpieces, 'categoryoptions'=>$categories]);
    }
}
