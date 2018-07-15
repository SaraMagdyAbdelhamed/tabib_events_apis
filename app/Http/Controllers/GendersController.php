<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use App\Gender;
class GendersController extends Controller {

    const MODEL = "App\Gender";

    use RESTActions;
    public function all_genders(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        return Helpers::Get_Response(200,'success','',[],Gender::all());

    }
}
