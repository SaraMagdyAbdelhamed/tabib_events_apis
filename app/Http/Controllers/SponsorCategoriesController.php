<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use App\SponsorCategory;
class SponsorCategoriesController extends Controller {

    const MODEL = "App\SponsorCategory";

    use RESTActions;

    public function get_sponsors($sponsor_id)
    {
       return Helpers::Get_Response(200,'success','',[],SponsorCategory::where('id',$sponsor_id)->with('sponsors')->get());
    }

}
