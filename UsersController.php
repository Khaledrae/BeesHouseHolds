<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    //
    public function headToLogin(){
        return view('signup');
    }
    public function headToSignup()
    {
        return view('signup', ['form'=>'signupform']);
    }
    public function registerUser(Request $request)
    {
        $user = new User;
        $user->last_name = $request->input('lname');
        $user->first_name = $request->input('fname');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->picture = $request->input('profilepic');
        $user->numoflogins = 0;
        $user->level = "Buyer";
        $password = $request->input('password');
        $confirmpassword = $request->input('confirmpassword');
        //$setpassword = bcrypt($password);
        if ($confirmpassword==$password) {
            $user->password = $password;
            if ($user->save()) {
                return redirect('login')->with('msg', 'User Registered Successfuly');
            }
            else {
                return redirect('signup')->with('msg', 'Failed To Register User');
            }
        }
        else {
            return redirect('signup')->with('msg', "Passowrds Don't Match.");
        }
    }
    public function registerTeam(Request $request)
    {
        $user = new User;
        $user->last_name = $request->input('lname');
        $user->first_name = $request->input('fname');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->picture = $request->input('profilepic');
        $user->numoflogins = 0;
        $user->level = "Team";
        $password = $request->input('password');
        $confirmpassword = $request->input('confirmpassword');
        //$setpassword = bcrypt($password);
        if ($confirmpassword==$password) {
            $user->password = $password;
            if ($user->save()) {
                return redirect('login')->with('msg', 'Team Member Registered Successfuly');
            }
            else {
                return redirect('signup')->with('msg', 'Failed To Register Team Member');
            }
        }
        else {
            return redirect('signup')->with('msg', "Passowrds Don't Match.");
        }
    }
    public function loginUser(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        //$userpass = bcrypt($password);
        $getuser = User::where('phone', '=', $username, 'OR', 'email', '=', $username)->get();
        $numofusers = count($getuser);
        if ($numofusers>=1) {
            foreach($getuser as $user){
                if ($password == $user['password']){
                    $userid = $user['id'];
                    $userlevel = $user['level'];
                    $username = $user['first_name'];
                    session()->put('userid', "$userid");
                    session()->put('userlevel', "$userlevel");
                    session()->put('username', "$username");
                    return redirect('home')->with('msg', 'Welcome '.$username.'');        
                }
                else{
                    return redirect('login')->with('msg', "Credentials Don't Match");
                }
            }
        }
        else {
            return redirect('login')->with('msg', 'Failed To Login');
        }
        //return User::where('firstname', $username)->get();
}

    public function logout()
    {
        session()->flush();
        return redirect('home');
    }
    public function getUsers()
    {
        $users = User::all();
        return view('users', ['users'=>$users]);
    }
}
