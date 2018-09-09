<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use App\GeoRegion;
class GeoRegionsController extends Controller {

    const MODEL = "App\GeoRegion";

    use RESTActions;

    public function getregioncity(Request $request)
    {
    $lang_id = $request->input('lang_id');
    $locale =Helpers::Set_locale($lang_id);
    //dd($lang_id);
      $regions= GeoRegion::where('city_id',$request->input('city_id'))->get();
      $regioncity= array();
      foreach($regions as $key=>$region){

// $citycounty[$key]= $city->name.','.$city->geo_country->name;
$regioncity[$key]= $region->getNameAttribute($region->name).','.$region->geo_city->getNameAttribute($region->geo_city->name);

      }
      if(count($regions)==0)
      {
        return Helpers::Get_Response(204,'NO Content',trans('messages.noregions'),[],$regions); 
      }
      return Helpers::Get_Response(200,'success','',[],$regions);
       // return response()->json($regions);
    }

}
