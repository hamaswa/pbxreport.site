<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Extension;
use App\Devices;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function  getExtensions($selected=array()){
        $ext = Extension::select('extension_no')->whereNotIn('extension_no',$selected)->get()->ToArray();
        return  Devices::select(array("id","description"))->whereNotIn('id',$ext)->get();

    }
}
