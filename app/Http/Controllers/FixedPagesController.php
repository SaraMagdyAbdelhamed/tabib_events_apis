<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use App\FixedPage;
class FixedPagesController extends Controller {

    const MODEL = "App\FixedPage";

    use RESTActions;
    public function fixed_pages(Request $request)
    {

        $pages = FixedPage::all();
     $lang_id = $request->input('lang_id');

        if ($pages) {
                foreach($pages as $page){
        $page->body = strip_tags($page->body);
        $page->body =Helpers::CleanStriptagText($page->body);
                    if( $lang_id == 1){
         $page->name =  $page->name;
         $page->body =  $page->body;
                  }elseif( $lang_id == 2){
                $pagename =  Helpers::localization('fixed_pages', 'name', $page->id, $lang_id );
                $pagebody =  Helpers::localization('fixed_pages', 'body', $page->id, $lang_id );
               $pagebody =Helpers::CleanStriptagText($pagebody);
                if($pagename == "Error"){$page->name =  $page->name;
                }else{
                    $page->name = $pagename;
                }
                 if($pagebody == "Error"){$page->body =  $page->body;
                }else{
                    $page->body = $pagebody;
                }
            }



        }

            return Helpers::Get_Response(200, 'success', '', '', array($pages));
        } else {

            return Helpers::Get_Response(400, 'error', trans('No pages found'), $validator->errors(), []);

        }


    }

}
