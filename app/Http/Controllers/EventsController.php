<?php namespace App\Http\Controllers;
use App\Currency;
use App\User;
use App\Event;
use App\Gender;
use App\GeoCity;
use App\user_rule;
use App\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use Illuminate\Support\Facades\Hash;
use App\Libraries\TwilioSmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class EventsController extends Controller {

    const MODEL = "App\Event";

    use RESTActions;
    
    public  function event_details(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                'event_id' => 'required'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        //$user = User::where('api_token',$request->header('access-token'))->first()->id;
        $event = Event::query()
            ->where('id',$request_data['event_id'])
            ->with('prices.currency','categories','hash_tags','media','posts.replies')
            ->withCount('GoingUsers')
            ->get();

        // Get You May Also Like
        if($event->isEmpty()){
            return Helpers::Get_Response(403, 'error', 'not found', [], []);
        }
        $category_ids = Event::find($request_data['event_id'])->categories->pluck('pivot.interest_id');
        $random = array_key_exists('random_limit',$request_data) ? $request_data['random_limit'] :10;
        $count = Event::EventsInCategories($category_ids)->get()->count();
        if($count < 10){
            $result = Event::EventsInCategories($category_ids)->get()->random($count);

        }else{
            $result = Event::EventsInCategories($category_ids)->get()->random($random);

        }
        return Helpers::Get_Response(200, 'success', '', [], [['event'=>$event,'you_may_also_like'=>$result]]);

    }
    public function list_events(Request $request,$type=NULL){
        // read the request
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //Validate
        $validator = Validator::make($request_data,
            [
                "interest_id" => "required"
               
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        $interest = Interest::find($request_data['interest_id']);
        if(!$interest){
            return Helpers::Get_Response(403, 'error', trans('messages.interest_not_found'),[], []);
        }
        if($request->header('access-token')){
            $user = User::where('api_token','=',$request->header('access-token'))->first();

            if(!$user){
                return Helpers::Get_Response(403, 'error', trans('messages.worng_token'),[], []);

            }
            // we want to get all events
            // related to this category - created by the login user
            $users_events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile();
            $non_users_events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile();
            // $data = $interest->events()
            //                 ->with('prices.currency','categories','hash_tags','media')
            //                  ->where('created_by', '=', $user->id)
            //                 ->orWhere(function ($query) use ($user) {
            //                     $query->where('created_by', '!=', $user->id)
            //                           ->where('is_active', '=', 1);
            //                 })->ShowInMobile();
            switch ($type) {
                case 'upcoming':
                    $users_data = $users_events->UpcomingEvents();
                    $not_user_data = $non_users_events->UpcomingEvents();
                    break;
                default:
                    $users_data = $users_events->PastEvents();
                    $not_user_data = $non_users_events->PastEvents();
                    // $data = $data->PastEvents();
                    break;
            }
            $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
            $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
            $result = array_merge($users_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
        }else{
            $events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile();
            switch ($type) {
                case 'upcoming':
                    $data = $events->UpcomingEvents();
                    break;
                default:
                    $data = $events->PastEvents();
                    break;
            }
            $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
            $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
            $result =$data->WithPaginate($page,$limit)->get();
        }
        return Helpers::Get_Response(200, 'success', '', '',$result);

    }

    /**
     * list all past and upcoming big events
     * @param Request $request
     * @param null $type
     * @return \Illuminate\Http\JsonResponse
     */

    public function big_events(Request $request,$type=NULL){
        // read the request
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        //Check if user Login
        if($request->header('access-token')){
            $user = User::where('api_token','=',$request->header('access-token'))->first();
            if(!$user){
                return Helpers::Get_Response(403, 'error', trans('messages.worng_token'),[], []);

            }
            // $user_events =Event::query()->with('prices.currency','hash_tags','categories','media')
            //               ->SuggestedAsBigEvent()
            //               ->CreatedByUser($user);
            // $non_user_events = Event::query()->with('prices.currency','hash_tags','categories','media')
            //     ->SuggestedAsBigEvent()
            //     ->NotCreatedByUser($user);
            $data = Event::query()
                            ->with('prices.currency','categories','hash_tags','media')
                            ->SuggestedAsBigEvent()
                             ->where('created_by', '=', $user->id)
                            ->orWhere(function ($query) use ($user) {
                                $query->where('created_by', '!=', $user->id)
                                      ->where('is_active', '=', 1);
                            });
                            
            switch ($type) {
                case 'upcoming':
                    // $user_data     = $user_events->UpcomingEvents();
                    // $not_user_data = $non_user_events->UpcomingEvents();
                    // $result        = array_merge($user_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
                    $result = $data->UpcomingEvents()->WithPaginate($page,$limit)->get();
                    return Helpers::Get_Response(200, 'success', '', '',$result);

                    break;
                case 'slider':
                    $data = Event::BigEvents()->orderBy('sort_order','DESC')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->IsActive()
                        ->ShowInMobile();
                    $result =$data->WithPaginate($page,$limit)->get();
                    return Helpers::Get_Response(200, 'success', '', '',$result);
                    break;
                default:
                    // $user_data = $user_events->PastEvents();
                    // $not_user_data = $non_user_events->PastEvents();
                    // //$result = $not_user_data->union($user_data)->orderBy("id","DESC")->get();
                    // $result = array_merge($user_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
                    $result = $data->PastEvents()->WithPaginate($page,$limit)->get();

                    return Helpers::Get_Response(200, 'success', '', '',$result);
                    break;
            }

        }else{
            $events = Event::query()
                ->with('prices.currency','hash_tags','categories','media')
                ->IsActive()
                ->ShowInMobile()
                ->SuggestedAsBigEvent();
            switch ($type) {
                case 'upcoming':
                    $data = $events->UpcomingEvents();
                    break;
                case 'slider':
                    $data = Event::BigEvents()->orderBy('sort_order','DESC')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->IsActive()
                        ->ShowInMobile();
                    break;
                default:
                    $data = $events->PastEvents();
                    break;
            }


            $result =$data->WithPaginate($page,$limit)->get();
            return Helpers::Get_Response(200, 'success', '', '',$result);



        }


        //Validate


    }

    public  function current_month_events(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        if($request->header('access-token')){
        $user = User::where('api_token','=',$request->header('access-token'))->first();

        if(!$user){
            return Helpers::Get_Response(403, 'error', trans('messages.worng_token'),[], []);

        }
            //this Month Events
            $this_month_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $this_month_not_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $this_month = array_merge($this_month_by_user->toArray(),$this_month_not_by_user->toArray());

            //Next Events
            $next_month_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->NextMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $next_month_not_by_user = Event::query()
                    ->with('prices.currency','categories','hash_tags','media')
                    ->NotCreatedByUser($user)
                    ->ShowInMobile()
                    ->NextMonthEvents()
                    ->WithPaginate($page,$limit)
                    ->orderBy('end_datetime','DESC')
                    ->get();
            $next_month = array_merge($next_month_by_user->toArray(),$next_month_not_by_user->toArray());
            $start_to_today_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->StartOfMothEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today_not_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->StartOfMothEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today = array_merge($start_to_today_by_user->toArray(),$start_to_today_not_by_user->toArray());

            $result = [
                'start_of_month_to_today'          => $start_to_today,
                'start_of_today_to_end'            => $this_month,
                'next_month'                       => $next_month

            ];
            return Helpers::Get_Response(200,'success','',[],$result);



        }else{
            $this_month = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $next_month = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->NextMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->StartOfMothEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();

            $result = [
                'start_of_month_to_today'          => $start_to_today,
                'start_of_today_to_end'            => $this_month,
                'next_month'                       => $next_month

            ];
            return Helpers::Get_Response(200,'success','',[],$result);

        }


    }

    public function nearby_events(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        

        // Perform The Query
        $lat    = env('JEDDAH_LATITUDE');//get Default locaion of JEDDAH if GPS of user is off
        $lng    = env('JEDDAH_LONGITUDE');

        if(array_key_exists('user_lat',$request_data))
        {
            if($request_data['user_lat'] != ""){
                $lat = $request_data["user_lat"];
            }

        }
        if(array_key_exists('user_lng',$request_data))
        {
            if($request_data['user_lng'] != ""){
                $lng = $request_data["user_lng"];
            }

        }
        $radius = array_key_exists('radius',$request_data) ? $request_data['radius']:50;
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
        if($request->header('access-token')){
            $user = User::where('api_token','=',$request->header('access-token'))->first();

            if(!$user){
                return Helpers::Get_Response(403, 'error', trans('messages.worng_token'),[], []);

            }
            $events_by_user = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            $events_not_by_user = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            $result = array_merge($events_by_user->toArray(),$events_not_by_user->toArray());
            return Helpers::Get_Response(200,'success','',[],[$result]);
        }else{
            $events = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            return Helpers::Get_Response(200,'success','',[],[$events]);
        }

    }

     /**
     * list all event categories
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  event_categories(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $categories = Interest::query()->has('events')->get();
        return Helpers::Get_Response(200,'success','',[],$categories);
    }

}
