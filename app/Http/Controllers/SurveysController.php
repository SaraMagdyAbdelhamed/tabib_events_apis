<?php namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    use Kreait\Firebase;
    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;
    use App\Libraries\Helpers;
    use App\Survey;
    use App\SurveyQuestion;
    use App\SurveyQuestionAnswer;
    use App\SurveyAnswerUser;
    use App\User;
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
        $api=$request->header('access-token');
        $request = (array)json_decode($request->getContent(), true);
        
        $database=self::firebase_database();

        // $updates=['surveys/'.$request['survey_id'].'/questions'.$request['question_id'].'/answers'.$request['answer_id']];
              $data=$database->getReference('surveys/'.$request['survey_id'])
                    ->getvalue();
        $questions=[];
     foreach($data['questions'] as $key => $question)
     {
        if($question['id'] == $request['question_id'])
        {
            foreach($question['answers'] as $key1 => $answer )
            {
                if($answer['id'] == $request['answer_id'])
                {
                    $question['answers'][$key1]['number_of_selections']=$answer['number_of_selections']+1;
                    //  dd($answer);
                }
                // dd($answer);
            }
            // dd($question);
        }
        $questions[$key]=$question;
     }
     $updates=['surveys/'.$request['survey_id'].'/questions'=>$questions];
              $data2=$database->getReference()
                    ->update($updates);
     $data1=$database->getReference('surveys/'.$data['id'])
                    ->getvalue();

    $survey=Survey::where('firebase_id',$request['survey_id'])->first();
    $question=SurveyQuestion::where('survey_id',$survey->id)->where('firebase_id',$request['question_id'])->first();
    $answer=SurveyQuestionAnswer::where('survey_id',$survey->id)->where('question_id',$question->id)->where('firebase_id',$request['answer_id'])->first();
    $answer->update([
        "number_of_selections"=>$answer->number_of_selections+1
    ]);
    $user=User::where('api_token',$api)->first();
    SurveyAnswerUser::create([
        "answer_id"=>$answer->id,
        "user_id"=>$user->id
    ]);
    //  dd($questions);

      return Helpers::Get_Response(200,'success','',[],$data1);
    }



}
