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

    public function offers_sponsors($sponsor_id)
    {
      $offers = Offer::where('sponsor_id',$sponsor_id)->get();  
      return Helpers::Get_Response(200,'success','',[],$offers);
    }

    public function rate_offer(Request $request)
    {
        
        $offer = Offer::find($request['offer_id']);
        $offer->total_number_of_ratings=$offer->total_number_of_ratings+1;
        $offer->total_sum_ratings=$offer->total_sum_ratings+$request['rate'];
        $offer->rating_avg=$offer->total_number_of_ratings/$offer->total_sum_ratings;
        $offer->save();
        return Helpers::Get_Response(200,'success','',[],$offer);
    }
}
