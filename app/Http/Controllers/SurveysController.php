<?php namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    use Kreait\Firebase;
    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;
    use App\Libraries\Helpers;
class SurveysController extends Controller {

    // const MODEL = "App\Survey";

    // use RESTActions;
    public function firebase_database()
    {
        if (!function_exists('public_path')) {
            /**
             * Return the path to public dir
             *
             * @param null $path
             *
             * @return string
             */
            function public_path($path = null)
            {
                return rtrim(app()->basePath('public/' . $path), '/');
            }
        }
        $serviceAccount = ServiceAccount::fromJsonFile(public_path().'/tabibevent-b5519e3c0e09.json');
                            $firebase = (new Factory)
                            ->withServiceAccount($serviceAccount)
                            ->withDatabaseUri('https://tabibevent.firebaseio.com/')
                            ->create();

        $database = $firebase->getDatabase();
        return $database;
    }

    public function index(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        
        $database=self::firebase_database();
        $newPost = $database
        ->getReference('surveys')
        ->getvalue();

        $surveys=[];
        foreach($newPost as $survey)
        {
            if($survey['parent_id'] ==  $request['event_id'])
            {
                $surveys[]=$survey;
            }
           
        }
        //  return ($surveys[0]);
        // echo '<pre>';
        return Helpers::Get_Response(200,'success','',[],$surveys);
    }

    public function add(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        
        $database=self::firebase_database();

        $updates=['surveys/'.$request['survey_id'].'/questions'.$request['question_id'].'/answers'.$request['answer_id']];
              $data=$database->getReference('surveys/'.$request['survey_id'])
                    ->getvalue();

      return Helpers::Get_Response(200,'success','',[],$data);
    }



}
