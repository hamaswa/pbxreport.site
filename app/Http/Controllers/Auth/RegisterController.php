<?php

namespace App\Http\Controllers\Auth;

use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
    }




    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'mobile_no' => 'required|max:14',
            'did_no' => 'required|max:10',
        ]);
    }

    public function showRegistrationForm()
    {
        $data['extension'] = $this->getExtensions();
        $data['roles'] = Role::all();
        return view('auth.register',$data);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'did_no' => $data['did_no'],
            'mobile_no' => $data['mobile_no'],
            'active' => isset($data['active'])?$data['active']:0,
            'password' => bcrypt($data['password']),
        ]);
        if(isset($data['role'])) {
            $role = Role::where('slug', $data['role'])->first();
            $user->roles()->attach($role);
        }


        foreach ($data['extension_no'] as $ext) {
            $user->extensions()->create(['extension_no' => $ext, 'description' => $ext]);
        }

    }


}
