<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use App\SponsorCategory;
class SponsorCategoriesController extends Controller {

    const MODEL = "App\SponsorCategory";

    use RESTActions;

    public function get_sponsors($cat_id)
    {
        if($cat_id == 0 )
        {
    return Helpers::Get_Response(200,'success','',[],SponsorCategory::with(['sponsors' => function ($query) {
            $query->with('user_info')->where('deleted_at', '=', null);
            }])->get());

        }
       return Helpers::Get_Response(200,'success','',[],SponsorCategory::where('id',$cat_id)->with(['sponsors' => function ($query) {
            $query->with('user_info')->where('deleted_at', '=', null);
            }])->get());
    }

}
