<?php

//created_by: Ash

namespace App\Libraries;
use App\Entity;
use Illuminate\Support\Facades\Mail;
use App\Notification;
use App\Notification_Types;

class Helpers
{
  

  public static  function Get_Response($code , $message , $error_details , $validation_errors , $content) {
    $validation = [];
    $i = 0;
    $validation_errors = current((array) $validation_errors);
    if(is_array($validation_errors) && sizeof($validation_errors) != 0) {
        foreach($validation_errors as $key=>$value) {
            $validation[$i]['field']=$key;
            $validation[$i]['message']=$value;
            $i++;
        }
    }
    return response()->json(['status'=>['code'=>$code,'message'=>$message,'error_details'=>$error_details,'validation_errors'=>$validation],'content'=>$content],200,[],JSON_UNESCAPED_UNICODE);
  }

  public static function Set_locale($locale)
  {
    if($locale == 2)
      {
        app('translator')->setLocale('ar');
      }
     else  if($locale == 1)
      {
        app('translator')->setLocale('en');
      }
  }

//   public static function  localizations($table_name , $field , $item_id)
//     {
//       $value_localized = Entities::where('name',$table_name)->with([
//         'localizations' => function($query) use($field, $item_id)
//         {
//             $query->where('field', $field)->where('item_id', $item_id);
//         }
//       ])->get();
//       foreach ($value_localized as  $value) {
        
//         foreach ($value->localizations as $value1) {
//             return $value1->value;       
//         }
//       }
     
        
//     }
public static function localization($table_name, $field_name, $item_id, $lang_id) {
    $localization = Entity::where('table_name', $table_name)->with(['localizations' => function($q) use ($field_name, $item_id, $lang_id){
        $q->where('field', $field_name)->where('item_id', $item_id)->where('lang_id', $lang_id); }
    ])->first();


    $result = isset($localization->localizations[0]) ? $localization->localizations[0]->value : "Error";
    return $result;
}

       public static function mail($email ,$code ,$verification_code){
        Mail::raw('Welcome To Tabib Events   Your code is ('.$code.' ) And Your Verification code is ('.$verification_code.')', function($msg) use($email){ 
            $msg->to([$email])->subject('Tabib'); 
            $msg->from(['tabib_events@penta-test.com']); 

          });
    }

    public static function mail_contact($body){
        Mail::raw('Welcome To Tabib   New Feedback'.$body, function($msg){ 
            $msg->to(['tabib_events@penta-test.com'])->subject('Tabib'); 
            $msg->from(['tabib_events@penta-test.com']); 

          });
    }

    public static function mail_verify($email ,$code ,$verification_code){
      Mail::raw('Welcome To Tabib Events  ..Please verify your Email by visiting this link: http://penta-test.com/doctors_events_dev_apis/public/verify_email?email='.$email.'&email_verification_code='.$verification_code, function($msg) use($email){
          $msg->to([$email])->subject('Eventakom');
          $msg->from(['tabib_events@penta-test.com']);
          // dd( $msg->getSwiftMessage()); 
        });
  }


   public static function mail_verify_withview($view,$email ,$email_verification_code){
     Mail::send($view, ['email' => $email, 'email_verification_code'=>$email_verification_code ], function($msg) use($email){
          $msg->to([$email])->subject('Tabib');
          $msg->from(['tabib_events@penta-test.com']);
      });

  }

  public static function isValidTimestamp($timestamp)
  {
     return ((string) (int) $timestamp === $timestamp)
         && ($timestamp <= PHP_INT_MAX)
         && ($timestamp >= ~PHP_INT_MAX);
  }

  /**
   * use this function in order to remove al hamazat and al tashkeel to
   * perform search more accurate in arabic search
   * @param $text
   * @return mixed
   */
  public static function CleanText($text){
      $arr = ['أ' => 'ا',
          'إ' => 'ا',
          'آ' => 'ا',
          "ة" => 'ه',
          "ّ" => '',
          "َّ" => '',
          "ُّ" => '',
          "ٌّ" => '',
          "ًّ" => '',
          "ِّ" => '',
          "ٍّ" => '',
          "ْ" => '',
          "َ" => '',
          "ً" => '',
          "ُ" => '',
          "ِ" => '',
          "ٍ" => '',
          "ٰ" => '',
          "ٌ" => '',
          "ۖ" => '',
          "ۗ" => '',
          "ـ" => ''
      ];
      foreach ($arr as $key => $val) {
          $cleaned_text = str_replace($key, $val, $text);
          $text = $cleaned_text;
      }
      return $text;
  }

    public static function CleanStriptagText($text){
              $text = html_entity_decode($text);
              $text = strip_tags($text);
              $text = str_replace('&nbsp;', '', $text);
              $text = trim(preg_replace('/\s+/', ' ', $text));
              $text = Helpers::CleanText($text);
              return $text;
    }

     public static function ParseHTML($text){

                // $text = html_entity_decode($text);
                // $text = strip_tags($text);
                $text=  htmlspecialchars_decode($text);
                $text = str_replace('&nbsp;', '', $text);
                $text = str_replace('&quot;', '', $text);
                $text = str_replace('RTL', '', $text);
                $text = trim(preg_replace('/\s+/', ' ', $text));
                return $text;
      }

      public static function notification($user_id , $entity_name , $item_id , $notification_type_id)
      {
        $notification_type = Notification_Types::find($notification_type_id);
        $entity = Entity::where('table_name', $entity_name)->first();
        $notification = Notification::create([
          "msg"=>$notification_type->msg,
          "user_id"=>$user_id,
          "entity_id"=> $entity->id,
          "item_id"=>$item_id,
          'is_push'=>$notification_type->is_push,
          "notification_type_id"=>$notification_type_id,
          "is_read"=>0,
          "is_sent"=>0
  
        ]);
  
        return self::Get_Response('200','success',"",[],(object)[]);
      }

    
}