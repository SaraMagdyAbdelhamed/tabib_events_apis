<?php namespace App\Http\Controllers;
        use Illuminate\Http\Request;
        use Illuminate\Support\Facades\Validator;
        use App\Libraries\Helpers;
        use App\Libraries\Base64ToImageService;
        use Illuminate\Support\Facades\Hash;
        use App\Libraries\TwilioSmsService;
        use Carbon\Carbon;
        use Illuminate\Support\Facades\Auth;
        use \Illuminate\Support\Facades\Lang;
        use App\User;
class UsersController extends Controller {

    const MODEL = "App\User";

    use RESTActions;
    public function signup(Request $request)
    {

        $twilio_config = [
            'app_id' => 'AC3adf7af798b1515700c517b58bdfc56b',
            'token' => '7f31eeed993ba1f5d62fd7ef2a3b1354',
            'from' => '+16039452091'
        ];

        $twilio = new TwilioSmsService($twilio_config);

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $user = new User;
        $validator = Validator::make($request, $user::$rules);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }

        if (array_key_exists('photo', $request)) {
            $request['photo'] = Base64ToImageService::convert($request['photo'], 'mobile_users/');
        }
        $input = $request;
        /*id	username	password	first_name	last_name	email	tele_code	mobile	country_id	city_id	gender_id	photo	birthdate	is_active	created_by	updated_by	created_at	updated_at	device_token	mobile_os	is_social	access_token	social_token	lang_id	mobile_verification_code	is_mobile_verification_code_expired	last_login	api_token	longtuide	latitude*/
        $input['password'] = Hash::make($input['password']);
        $input['is_active'] = 0;
        $input['username'] = $request['first_name'] . '' . $request['last_name'];
        $input['code'] = mt_rand(100000, 999999);
        $input['mobile_verification_code'] = str_random(4);
        $input['is_mobile_verification_code_expired'] = 0;
        $input['email_verification_code'] = str_random(4);
        $input['is_email_verified'] = 0;
        $input['is_mobile_verified'] = 0;
        if(isset($request['city_id'])){
        $city_id=$request['city_id'];
        $city = GeoCity::find($city_id);
        $input['country_id'] = $city->geo_country->id;
        $input['timezone'] = $city->geo_country->timezone;
        $input['longitude'] = $city->longitude;
        $input['latitude'] = $city->latitude;
        
        }
        $user = User::create($input);
        $user_array = User::where('mobile','=',$request['mobile'])->first();
 
        if ($user) {
            $sms_mobile = $request['tele_code'] . '' . $request['mobile'];
            $sms_body = trans('messages.your_verification_code_is') . $input['mobile_verification_code'];
            $status = $twilio->send($sms_mobile, $sms_body);
            //process rules
            $rules = user_rule::create(['user_id'=>$user_array->id ,'rule_id'=>2 ]);
            $mail_mobile_code=Helpers::mail($request['email'],$input['username'],$input['mobile_verification_code']);
            $mail=Helpers::mail_verify_withview('emails.verification',$request['email'],$input['email_verification_code']);
            //dd($mail);

        }
        return Helpers::Get_Response(200, 'success', '', $validator->errors(),array($user_array) );
    }

}
