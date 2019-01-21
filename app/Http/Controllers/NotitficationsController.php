<?php namespace App\Http\Controllers;

use App\Notification;
use App\Libraries\Helpers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotitficationsController extends Controller {

    const MODEL = "App\Notitfication";

    use RESTActions;

    public function get_all_notifications(Request $request) {
        $request = (array)json_decode($request->getContent(), true);
        if(array_key_exists('lang_id',$request)) {
            Helpers::Set_locale($request['lang_id']);
        }  
        if ($request->header('access-token')) {
            $user = User::where('api_token', '=', $request->header('access-token'))->first();
            if($user) {
                $notifications = Notification::
                        where('user_id',$user->id)
                        ->where('is_read',0)
                        ->where('is_push',1)
                        ->orwhere(function($q){
                            $q->where('is_read',1)->where('created_at','>=',Carbon::now());
                        })->get();
                if($notifications->count() == 0)
                {
                  return Helpers::Get_Response(402,'success','no data found',[],[]);
                }
                return Helpers::Get_Response(200,'success','',[],$notifications);
                }
                else
                {
                 return Helpers::Get_Response(400,'error',trans('messages.logged'),[],(object)[]);
                }

          }
      else
         {
           return Helpers::Get_Response(400,'error',trans('messages.logged'),[],(object)[]);
          }
    }

}
