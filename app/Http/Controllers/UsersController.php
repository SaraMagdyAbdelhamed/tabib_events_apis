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
        use App\userInfo;
        use App\userGoing;
        use App\GeoCity;
        use App\GeoCountry;
        use App\userRules;
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
        $input['username'] = $request['first_name'] ;
        $input['code'] = mt_rand(100000, 999999);
        $input['mobile_verification_code'] = str_random(4);
        $input['is_mobile_verification_code_expired'] = 0;
        $input['email_verification_code'] = str_random(4);
        $input['is_email_verified'] = 1;
        $input['is_mobile_verified'] = 1;
        // $input['is_profile_completed']=1;
        if(isset($request['city_id'])){
        $city_id=$request['city_id'];
        $city = GeoCity::find($city_id);
        $input['country_id'] = $city->geo_country->id;
        $input['timezone'] = $city->geo_country->timezone;
        $input['longitude'] = $city->longitude;
        $input['latitude'] = $city->latitude;
        
        }
        $user = User::create($input);
        $user_info= new userInfo;
        $user_info->user_id=$user->id;
        $user_info->region_id=$request['region_id'];
        $user_info->is_backend=0;
        $user_info->is_profile_completed=1;
        if(isset($request['mobile2']))
        {
            $user_info->mobile2=$request['mobile2']; 
        }
        if(isset($request['mobile3']))
        {
            $user_info->mobile2=$request['mobile3']; 
        }
        $user_info->save();
        // $user_info->users()->save($user);
        $user_array = User::where('mobile','=',$request['mobile'])->first();
 
        if ($user) {
            $sms_mobile = $request['tele_code'] . '' . $request['mobile'];
            $sms_body = trans('messages.your_verification_code_is') . $input['mobile_verification_code'];
            $status = $twilio->send($sms_mobile, $sms_body);
            //process rules
            $rules = userRules::create(['user_id'=>$user_array->id ,'rule_id'=>2 ]);
            $mail_mobile_code=Helpers::mail($request['email'],$input['username'],$input['mobile_verification_code']);
            $mail=Helpers::mail_verify_withview('emails.verifications',$request['email'],$input['email_verification_code']);
            //dd($mail);

        }
        
        return Helpers::Get_Response(200, 'success', '', $validator->errors(),array($user_array) );
    }

    public function verify_email(Request $request)
    {

  
        $email = $request->input('email');
        $email_verification_code = $request->input('email_verification_code');
      
        $user = User::where('email', $email)->where("email_verification_code", "=", $email_verification_code)->first();

        if ($user) {
            if ($user->email_verification_code == $email_verification_code) {

                // $user->is_mobile_verification_code_expired = 1;
                if ($user->is_email_verified == 0) {

                    $user->update(['is_email_verified' => 1]);

                }


                if($user->save()){

                 $mail=Helpers::mail_verify_withview('emails.verification2',$request['email'],'verified');   
                }else{
                 $mail=Helpers::mail_verify_withview('emails.verification2',$request['email'],'error');   
                }
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_verification_code'), $validator->errors(), []);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('Email is not registered'), [], []);
        }


       // return Helpers::Get_Response(200, 'success', '', $validator->errors(),array($user));
         return redirect('http://eventakom.com/');

    }

    public function edit_profile(Request $request)
    {
        $twilio_config = [
            'app_id' => 'AC3adf7af798b1515700c517b58bdfc56b',
            'token' => '7f31eeed993ba1f5d62fd7ef2a3b1354',
            'from' => '+16039452091'
        ];

        $twilio = new TwilioSmsService($twilio_config);

        $api_token = $request->header('access-token');
        //dd($api_token);
       

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }

        $user = User:: where("api_token", "=", $api_token)->first();
    // dd($request);
    if(array_key_exists('email', $request)){
        if ($user->email == $request['email']) {
            $email_valid = 'email|max:35';
        } else {
            $email_valid = 'email|unique:users|max:35';
        }
    }
    if(array_key_exists('mobile', $request)){
        if ($user->mobile == $request['mobile']) {
            $mobile_valid = 'numeric|digits:11';
        } else {
            $mobile_valid = 'numeric|unique:users|digits:11';
        }
    }
        
        $validator = Validator::make($request,
            [
                'first_name' => 'between:1,100',
                'email' => $email_valid,
                //  'region_id' => 'required',
                'mobile' => $mobile_valid,
                // 'password' => 'required|between:8,20',
                'mobile_os' => 'in:android,ios',
                'lang_id' => 'in:1,2',
                // 'country_id'=>'required',
                

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }

        if (array_key_exists('photo', $request)) {
            $request['photo'] = Base64ToImageService::convert($request['photo'], '/mobile_users/');
        }
        $input = $request;
        /*id username  password  first_name  last_name email tele_code mobile  country_id  city_id gender_id photo birthdate is_active created_by  updated_by  created_at  updated_at  device_token  mobile_os is_social access_token  social_token  lang_id mobile_verification_code is_mobile_verification_code_expired  last_login  api_token longtuide latitude*/
        // if (Hash::check($request['password'], $user->password)) {
        //     $input['password'] = $user->password;
        // } else {

        //     $input['password'] = Hash::make($input['password']);
        // }
        // $input['password'] = $user->password;
        //$input['is_active'] = 0;
        $input['username'] = $request['first_name'];
        $input['mobile'] =  $request['mobile'];
        $city_id=$request['city_id'];
        $city = GeoCity::find($city_id);
        $input['country_id'] = $city->geo_country->id;
        $input['timezone'] = $city->geo_country->timezone;
        $input['longitude'] = $city->longitude;
        $input['latitude'] = $city->latitude;
        //$input['code']=mt_rand(100000, 999999);
        $input['mobile_verification_code'] = str_random(4);
        // $input['is_mobile_verification_code_expired'] = 0;
        // $input['email_verification_code'] = str_random(4);
        // $input['is_email_verified'] = 0;
        // $input['is_mobile_verified'] = 1;
        $input['email_verification_code'] = str_random(4); //change it to email_verification_code
        //$input['is_mobile_verification_code_expired']=0;
        $old_email = $user->email;
        $user_update = $user->update($input);
        // if ($user_update && $old_email != $request['email']) {
        //     //$status =$twilio->send($request['mobile'],$input['mobile_verification_code']);
        //    $mail=Helpers::mail_verify($request['email'],$input['username'],$input['email_verification_code']);
        //     $user->update(['is_email_verified' => 0]);
        // }
         $user_array = User:: where("api_token", "=", $api_token)->first();
        // $base_url = 'http://penta-test.com/doctors_events_dev_apis/public/';
        // $user_array->photo = $base_url.$user_array->photo;
        // $user_array->user_info()->update([
        //     'address'=>$request['address'],
        //     'specialization_id'=>$request['specialization_id'],
        //     'is_profile_completed'=>1,
        //     'region_id'=>$request['region_id']
        // ]);
        if ($user_array) {
            $sms_mobile = $request['tele_code'] . '' . $request['mobile'];
            $sms_body = trans('messages.your_verification_code_is') . $input['mobile_verification_code'];
            $status = $twilio->send($sms_mobile, $sms_body);
            //process rules
            $rules = userRules::create(['user_id'=>$user_array->id ,'rule_id'=>2 ]);
            // $mail_mobile_code=Helpers::mail($request['email'],$input['username'],$input['mobile_verification_code']);
            // $mail=Helpers::mail_verify_withview('emails.verifications',$request['email'],$input['email_verification_code']);
            //dd($mail);

        }
        return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));
    }

    public function change_password(Request $request)
    {
        //read the request
        $request_data = (array)json_decode($request->getContent(), true);
        //valdiation
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }

        $validator = Validator::make($request_data,
            ["new_password" => "required|Between:8,20", "old_password" => "required|Between:8,20"]);
        //check validation result
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);

        } else {
            $user = User::where('api_token', '=', $request->header('access-token'))->first();

            if (Hash::check($request_data['old_password'], $user->password)) {
                $user->password = Hash::make($request_data['new_password']);
                $user->save();
                return Helpers::Get_Response(200, 'success', '', $validator->errors(),array($user));


            } else {

                return Helpers::Get_Response(401, 'faild', trans('messages.wrong_user_password'), [], []);




            }


        }


    }
    
    public function mail_existence(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "email" => "required",
            "lang_id"=>"required"

        ]);

          if ($validator->fails()) {
                return Helpers::Get_Response(403, 'error', '', $validator->errors(),[]);

        } else {

   $user = User::where('email', $request['email'])->first();

        if ($user) {
         return Helpers::Get_Response(204, trans('messages.email_already_exist'), '', $validator->errors(), []);

        }else{

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), []);
        }



        }


    }


    public function mobile_existence(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "mobile" => "required",
            "tele_code"=>"required",
            "lang_id"=>"required"

        ]);


        if ($validator->fails()) {
                return Helpers::Get_Response(403, 'error', '', $validator->errors(),[]);

        } else {

   $user = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();

        if ($user) {
         return Helpers::Get_Response(204, trans('messages.mobile_already_exist'), '', $validator->errors(), []);

        }else{

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), []);
        }



        }

    }
    
    public function login(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "mobile" => "required",
            "tele_code"=>"required",
            "password" => "required|min:8|max:20",
//            "device_token"=>'required',
//            "lang_id"=>'required',
//            "mobile_os"=>'required',
        ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }
        if (array_key_exists('mobile', $request) && array_key_exists('password', $request)) {

            //////

            // if (isset($request['MobileOrEmail'])) {
        $user = User::where("mobile", "=", $request['mobile'])->where('tele_code', $request['tele_code'])->with('rules')->with('user_info')->first();

               // if (!$user) {
                 //   $user = User::where("email", "=", $request['MobileOrEmail'])->with('rules')->with('user_info')->first();
                    if(! $user)
                    {
                  return Helpers::Get_Response(400, 'error', trans('messages.invalid_mobile_number'), $validator->errors(), []);

                    }
              //  }
            // }else{
            //      return Helpers::Get_Response(400, 'error', trans('messages.invalid_mobile_number_or_email'), $validator->errors(), []);
            // }

            
            // elseif (filter_var($request['mobile'], FILTER_VALIDATE_EMAIL)) {
            //   $user = User:: where("email", "=", $request['mobile'])->with('rules')->first();
            //   if(!$user) {
            //    return Helpers::Get_Response(400,'error',trans('this e-mail isn’t registered'),$validator->errors(),[]);}
            // }

            //////

            // $user = User:: where("mobile", "=", $request['mobile_email'])->with('rules')->first();
            if ($user) {
                if (Hash::check($request['password'], $user->password)) {
                    if ($user->is_active == 1 && $user->is_mobile_verified==1) {
                        $tokenobj = $user->createToken('api_token');
                        $token = $tokenobj->accessToken;
                        $token_id = $tokenobj->token->id;
                        //$user = new User;
                        $user->api_token = $token_id;
                        $user->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $user->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                        if(array_key_exists('device_token',$request)){
                            if($request['device_token'] != ''){
                                $user->device_token = $request['device_token'];
                            }
                        }
                        $user->save();

                        // $user_array = $user->toArray();
                        // foreach ($user_array['rules'] as  $value) {
                        //     if(array_key_exists('lang_id',$request) && $request['lang_id']==1) {
                        //         $rules []=  array($value['id'] => $value['name']);
                        //     } else {
                        //         $rules []= array($value['id'] => $value['name_ar']);
                        //     }
                        //     $rule_ids [] = $value['id'];
                        // }
                        // $user_array['rule_ids']  = $rule_ids;
                        // $user_array['rules'] = $rules;
                        // $user['roles']=$rules;
                        // if ($user['photo'] != null) {
                        //     $user['photo'] = ENV('FOLDER') . $user['photo'];
                        // }
//                        $user->update([
//                            "device_token"=>$request['device_token'],
//                            "lang_id"=>$request['lang_id']
//                            "mobile_os"=>$request['mobile_os'],
//                        ]);
                    //   $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
                    //   $base_url = 'hhttp://penta-test.com/doctors_events_dev_apis/public/';
                    //   $user_array->photo = $base_url.$user_array->photo;
                        return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user));
                    } else {
                        return Helpers::Get_Response(400, 'error', trans('messages.active'), $validator->errors(), []);
                    }
                }
                return Helpers::Get_Response(400, 'error', trans('messages.wrong_password'), $validator->errors(), []);
            } else {
                return Helpers::Get_Response(400, 'error', trans('messages.mobile_isn’t_registered'), $validator->errors(), []);
            }
            $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
            // $base_url = 'http://eventakom.com/eventakom_dev/public/';
            // $user_array->photo = $base_url.$user_array->photo;
            return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));
        } else {
            return Helpers::Get_Response(401, 'error', trans('Invalid mobile number'), $validator->errors(), []);
        }
    }


    public function logout(Request $request)
    {
        $api_token = $request->header('access-token');
        //dd($request->header('api_token'));
        // $request_header = (array)json_decode($request->header('api_token'), true);
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        // dd($request_header);
        // if(array_key_exists('api_token',$request) && $request['api_token'] != '')
        // {

        // $user=User:: where("api_token", "=",  $api_token )
        //              ->first();
        $user = User:: where("api_token", "=", $api_token)
            ->first();
        if ($user) {
            $user->update(['api_token' => null]);
            $user->save();
            return Helpers::Get_Response(200, 'success', '', '', array($user));
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.logged'), [], []);
        }
        // }else{
        //   return Helpers::Get_Response(400,'error',trans('messages.logged'),[],[]);
        // }
    }

    public function change_lang(Request $request)
    {
        $api_token = $request->header('access-token');

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "lang_id" => "required|in:1,2"

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        } else {
            $user = User:: where("api_token", "=", $api_token)->first();


            if ($user) {
                $user->update(['lang_id' => $request['lang_id']]);
                $user->save();
                // $base_url = 'http://penta-test.com/doctors_events_dev_apis/public/';
                 $user_array = User:: where("api_token", "=", $api_token)->first();
                // $user_array->photo = $base_url.$user_array->photo;
                return Helpers::Get_Response(200, 'success', '', '', array($user_array));
            } else {

                return Helpers::Get_Response(400, 'error', trans('No user Registerd with this token'), $validator->errors(), []);

            }
        }

    }
    public function resend_verification_code(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }

        $validator = Validator::make($request, [
            "mobile" => "required|numeric",
            "tele_code" => "required",
            "lang_id" => "required|in:1,2"

        ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        } else {
            $twilio_config = [
                'app_id' => 'AC2305889581179ad67b9d34540be8ecc1',
                'token' => '2021c86af33bd8f3b69394a5059c34f0',
                'from' => '+13238701693'
            ];

            $twilio = new TwilioSmsService($twilio_config);

            $user = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
            if(!$user){
             return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
            }else{
            // dd($user);
            $mobile_verification_code = str_random(4);
            $sms_mobile = $user->tele_code. '' .$user->mobile;
            $sms_body = trans('messages.your_verification_code_is') . $mobile_verification_code;
            $user_date = date('Y-m-d', strtotime($user->verification_date));
            if ($user->is_mobile_verification_code_expired != 1 && $user->verification_count < 5) {

                //send verification code via Email , sms
                //increase verification count by 1
                $user->verification_date = Carbon::now()->format('Y-m-d');
                //$mobile_verification_code =str_random(4);
                $user->is_mobile_verified = 0;
                $user->mobile_verification_code = $mobile_verification_code;
                $user->verification_count = $user->verification_count + 1;
                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
                // $base_url = 'http://eventakom.com/eventakom_dev/public/';
                // $user_array->photo = $base_url.$user_array->photo;
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));
            } //date_format("Y-m-d", $user->verification_date) dont forget
            elseif ($user->verification_count >= 5 && $user_date != Carbon::now()->format('Y-m-d')) {
                //set is_mobile_verification_code_expired to 0
                $user->is_mobile_verified = 0;
                $user->is_mobile_verification_code_expired = 0;
                //reset verification count to 0
                $user->verification_count = 0;
                // update verification date to current date
                $user->verification_date = Carbon::now()->format('Y-m-d');

                //increase verification count by 1
                $user->verification_count = $user->verification_count + 1;

                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
                // $base_url = 'http://eventakom.com/eventakom_dev/public/';
                // $user_array->photo = $base_url.$user_array->photo;
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));
            } elseif ($user->verification_count >= 5 && $user_date == Carbon::now()->format('Y-m-d')) {
                //set is_mobile_verification_code_expired to 1
                $user->is_mobile_verified = 0;
                $user->is_mobile_verification_code_expired = 1;
                // response : sorry you have exeeded your verifications limit today
                return Helpers::Get_Response(400, 'error', trans('messages.exceeded_verifications_limit'), $validator->errors(), []);
            } elseif ($user->is_mobile_verification_code_expired = 1 && $user->verification_count < 5 && $user_date == Carbon::now()->format('Y-m-d')) {
                $user->is_mobile_verification_code_expired = 0;
                $user->is_mobile_verified = 0;
                //send verification code via Email , sms
                //increase verification count by 1
                $user->verification_date = Carbon::now()->format('Y-m-d');
                //$mobile_verification_code =str_random(4);
                $user->mobile_verification_code = $mobile_verification_code;
                $user->verification_count = $user->verification_count + 1;
                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
                // $base_url = 'http://penta-test.com/doctors_events_dev_apis/public/';
                // $user_array->photo = $base_url.$user_array->photo;
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));

            }
}
        }
    }

    public function verify_verification_code(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required",
                "tele_code" => "required",
                "mobile_verification_code" => "required",
                "lang_id" => "required|in:1,2"
            ]);
        if ($validator->fails()) {
            // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }
        $user = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
// dd($user->name);
        if ($user) {
            if ($user->mobile_verification_code == $request['mobile_verification_code']) {

                $user->is_mobile_verification_code_expired = 1;
                if ($user->is_active == 0 || $user->is_mobile_verified == 0 ) {

                    $user->update(['is_active' => 1,'is_mobile_verified'=>1,'is_email_verified'=>0, 'verification_date' => Carbon::now()->format('Y-m-d')]);

                }


                $user->save();
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_verification_code'), $validator->errors(), []);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.mobile_number_not_registered'), $validator->errors(), []);
        }
        $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
        // $base_url = 'http://penta-test.com/doctors_events_dev_apis/public/';
        // $user_array->photo = $base_url.$user_array->photo;
        return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user_array));

    }


    public function reset_password(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
                "new_password" => "required|min:6|max:20",
                "confirm_password" => "required|min:6|max:20|same:new_password",
            ]);
        if ($validator->fails()) {
            // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }
        $user = User::where('mobile', $request['mobile'])->first();
        if ($user) {
            if ($user->verificaition_code == $request['verificaition_code'] && $user->is_mobile_verification_code_expired == 1) {
                $user->update(['password' => Hash::make($request['confirm_password'])]);

            } else {
                // echo $user->verificaition_code;
                return Helpers::Get_Response(400, 'error', trans('messages.invalid_verification_code'), $validator->errors(), array($user));

            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.mobile'), $validator->errors(), []);
        }

        return Helpers::Get_Response(200, 'success', '', $validator->errors(), array($user));

    }

    public function forget_password(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required",
                "tele_code"=>"required",
                "mobile_verification_code" => "required",
                "new_password" => "required|between:8,20"
            ]);
        if ($validator->fails()) {

            return Helpers::Get_Response(403, 'error', '', $validator->errors(), []);
        }

        $user = User::where('mobile', $request['mobile'])->first();
        if (is_numeric($request['mobile'])) {
        if ($user) {
            if ($user->mobile_verification_code == $request['mobile_verification_code']) {

                $user->is_mobile_verification_code_expired = 1;

                $new_password = Hash::make($request['new_password']);
                $user->update(['password' => $new_password]);


                $user->save();
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_verification_code'), $validator->errors(), []);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.mobile_isn’t_registered'), $validator->errors(), []);
        }
    }else{
         return Helpers::Get_Response(400, 'error', trans('messages.invalid_mobile_number'), $validator->errors(), []);

    }

        $user_array = User::where('mobile', $request['mobile'])->where('tele_code', $request['tele_code'])->first();
        // $base_url = 'http://penta-test.com/doctors_events_dev_apis/public/';
        // $user_array->photo = $base_url.$user_array->photo;
        return Helpers::Get_Response(200, 'success', '', $validator->errors(),array($user_array));

    }

    public function users_sponsors($sponsor_id)
    {
      $Users = User::whereHas('sponsors_categories',function($q)use($sponsor_id){
          $q->where('sponsor_category_id',$sponsor_id);
      })->with('sponsors_categories')->get();  
      return Helpers::Get_Response(200,'success','',[],$Users);
    }
}
