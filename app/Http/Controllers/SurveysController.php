<?php namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    use Kreait\Firebase;
    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;
class SurveysController extends Controller {

    // const MODEL = "App\Survey";

    // use RESTActions;
   

    public function index()
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
        $serviceAccount = ServiceAccount::fromJsonFile(public_path().'/doctors-events-3b5e51a68748.json');
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://doctors-events.firebaseio.com/')
        ->create();

        $database = $firebase->getDatabase();

        $newPost = $database
        ->getReference('surveys')
        ->push([
        'title' => 'survey' ,
        'category' => 'Laravel'
        ]);
        // echo '<pre>';
        dd($newPost->getvalue());
    }

}
