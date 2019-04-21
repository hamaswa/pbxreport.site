<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use App\DataTables\UsersDatatable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use DB;
use Datatables;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'password' => 'string|min:6|confirmed',
            'mobile_no' => 'required|max:14',
            'did_no' => 'required|max:10',
        ]);

    }

    public function index(UsersDatatable $datatable)
    {
        return  $datatable->render('users.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        if(auth()->user()->hasRole('administrator')
            or auth()->user()->hasPermission('edit-user')
            or auth()->user()->hasPermissionThroughRole('edit-user')) {


            $user = User::find($id);
            $data['selected'] = $user->extensions()->Pluck("extension_no")->ToArray();
            $data['extension'] = $this->getExtensions($data['selected']);
            $data['roles'] = Role::all();
            return view('users.edit', $data)->with('user', $user);
        }
        else return "You don't have permissions to edit User";
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id,UsersDatatable $datatable)
    {
        $this->validator($request->all())->validate();

        $data = $request->all();
        $user = User::find($id);
        $user->update([ 'name' => $data['name'],
            'did_no' => $data['did_no'],
            'mobile_no' => $data['mobile_no'],
            'active' => $data['active']
            ]);
        $user->extensions()->delete();


        if(isset($data['role'])) {
            $user->roles()->detach();
            $role = Role::where('id', $data['role'])->first();
            $user->roles()->attach($role);
        }

        foreach ($data['extension_no'] as $ext) {
            $user->extensions()->create(['extension_no' => $ext, 'description' => $ext]);
        }
        return redirect( route("users.index"));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect( route("users.index"));


    }

}
