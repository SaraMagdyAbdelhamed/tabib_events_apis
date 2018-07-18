<?php namespace App\Http\Controllers;
    use App\Offer;
    use Illuminate\Http\Request;
use App\Libraries\Helpers;
class OffersController extends Controller {

    const MODEL = "App\Offer";

    use RESTActions;


    public function increment_calls($id)
    {
      $offer = Offer::whereId($id)->increment('number_of_calls');  
      return Helpers::Get_Response(200,'success','',[],Offer::find($id));
    }

    public function increment_views($id)
    {
      $offer = Offer::whereId($id)->increment('number_of_views');  
      return Helpers::Get_Response(200,'success','',[],Offer::find($id));
    }

}
