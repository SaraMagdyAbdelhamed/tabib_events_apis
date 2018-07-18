<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
 * Routes for resource user
 */
// $router->post('user_signup',   'UsersController@signup');
$router->group(['prefix' => 'api'], function () use ($router) {
    //user routes
   $router->get('user', 'UsersController@all');
    $router->get('user/{id}', 'UsersController@get');
    $router->post('user', 'UsersController@add');
    $router->put('user/{id}', 'UsersController@put');
    $router->delete('user/{id}', 'UsersController@remove');
//    $router->get('all_users',  ['uses' => 'UsersController@getAllUsers']);
    $router->post('user_signup',  ['uses' => 'UsersController@signup']);
   $router->post('login', 'UsersController@login');
   $router->post('verify_verification_code', ['uses' =>'UsersController@verify_verification_code']);
   $router->post('resend_verification_code', ['uses' =>'UsersController@resend_verification_code']);
   $router->post('forget_password', ['uses' =>'UsersController@forget_password']);
//    $router->post('social_login','UsersController@social_login');
//    $router->post('sms','UsersController@sms');
//    $router->post('all_interests','UsersController@all_interests');
//    $router->post('all_currencies','EventsController@all_currencies');
   $router->post('all_genders','GendersController@all_genders');
//    $router->post('event_categories','EventsController@event_categories');
//    $router->post('events[/{type}]',"EventsController@list_events");
//    $router->post("big_events[/{type}]","EventsController@big_events");
//    $router->post('current_month_events',"EventsController@current_month_events");
//    $router->post('nearby_events',          "EventsController@nearby_events");
//    $router->post("age_ranges","EventsController@age_ranges");
//    $router->post('event_details',"EventsController@event_details");
//    $router->post("event_posts","EventsController@event_posts");
//    $router->post('recommended_events[/{type}]','EventsController@recommended_events');
//    $router->post('trending_keywords',"EventsController@trending_keywords");
//    $router->post('events_search',"EventsController@search");
//    $router->post("get_post_replies","EventsController@get_post_replies");
//    $router->post('tweets',"EventsController@tweets_by_hashtags");

//    //Shops and Dines
//    $router->post("shops","ShopController@list_shops");
//    $router->post("offers","ShopController@list_offers");
//    $router->post("nearby_branches","ShopController@nearby_branches");
//    $router->post("shop_details","ShopController@shop_details");

//    //famous attractions
//   $router->post("famous_attractions","FamousAttractionsController@list_famous_attractions");
//   $router->post("nearby_famous_attractions","FamousAttractionsController@nearby_famous_attractions");
//   $router->post("famous_attractions_categories","FamousAttractionsController@famous_attractions_categories");
   $router->get('verify_email',  ['uses' => 'UsersController@verify_email','as'=>'verify']);
//      //countries
  $router->get('all_countries',  ['uses' => 'GeoCountriesController@getAllCountries']);
   //cities
  $router->get('all_cities',  ['uses' => 'GeoCitiesController@getAllCities']);
  $router->get('getcitycountry',  ['uses' => 'GeoCitiesController@getcitycountry']);
  $router->get('searchcitycountry',  ['uses' => 'GeoCitiesController@searchcitycountry']);
//     //fixed pages
   $router->get('fixed_pages', ['uses' =>'FixedPagesController@fixed_pages']);
   $router->get('getregioncity',  ['uses' => 'GeoRegionsController@getregioncity']);
//   //data existence
  $router->post('mail_existence', ['uses' =>'UsersController@mail_existence']);
  $router->post('mobile_existence', ['uses' =>'UsersController@mobile_existence']);

//   //test emails
//   $router->post("test_email",'UsersController@test_email');
//   $router->post("delete_user","UsersController@delete_user");

//   //list  notifications types
//   $router->get("notification_types","NotificationController@notification_types");

//   //contat us
//   $router->post("contact_us","UsersController@contact_us");

$router->get('specialization', 'SpecializationsController@all');
$router->get('specialization/{id}', 'SpecializationsController@get');
$router->post('specialization', 'SpecializationsController@add');
$router->put('specialization/{id}', 'SpecializationsController@put');
$router->delete('specialization/{id}', 'SpecializationsController@remove');

/**
 * Routes for resource sponsor-category
 */
$router->get('sponsor_category', 'SponsorCategoriesController@all');
$router->get('sponsor_category/{id}', 'SponsorCategoriesController@get');
$router->post('sponsor_category', 'SponsorCategoriesController@add');
$router->put('sponsor_category/{id}', 'SponsorCategoriesController@put');
$router->delete('sponsor_category/{id}', 'SponsorCategoriesController@remove');

/**
 * Routes for resource offer
 */
$router->get('offers', 'OffersController@all');
$router->get('offer/{id}', 'OffersController@get');
$router->post('offer', 'OffersController@add');
$router->put('offer/{id}', 'OffersController@put');
$router->delete('offer/{id}', 'OffersController@remove');
$router->get('increment_calls/{id}', 'OffersController@increment_calls');
$router->get('increment_views/{id}', 'OffersController@increment_views');

});


$router->group(['prefix' => 'api',  'middleware' => 'EventakomAuth'], function () use ($router) {

    //users routes
  $router->post('logout', 'UsersController@logout');
  $router->post('change_lang','UsersController@change_lang');
  $router->post('edit_profile',  'UsersController@edit_profile');



    //interests
//   $router->post('add_interests',['uses' =>'UsersController@add_interests']);
//   $router->post('add_user_interests', ['uses' =>'UsersController@add_user_interests']);
//   $router->post('edit_user_interests',['uses'=>'UsersController@edit_user_interests']);
//   $router->get('user_interests',['uses'=>'UsersController@user_interests']);



  //password section
  $router->post('change_password','UsersController@change_password');


  //Events Section
//   $router->post("add_event",              "EventsController@add_event");
//   $router->post("edit_event",             "EventsController@edit_event");
//   $router->post("delete_event",           "EventsController@delete_event");
//   $router->post('delete_event_post',      "EventsController@delete_event_post");
//   $router->post('delete_reply',           'EventsController@delete_reply');
//   $router->post('recommended_events[/{type}]','EventsController@recommended_events');
//   $router->post('add_user_going',         'EventsController@add_user_going');
//   $router->post('add_user_favourites',    'EventsController@add_user_favourites');
//   $router->post('add_user_calenders',     'EventsController@add_user_calenders');
//   $router->post('calender_events',        "EventsController@calender_events");
//   $router->post("my_events",              "EventsController@my_events");
//   $router->post("add_post",               "EventsController@add_post");
//   $router->post("add_post_reply",         "EventsController@add_post_reply");


//   //booking section
//   $router->post("book_events",            "EventsController@book_event");
//   //realted to shops and dine 
//   $router->post('add_shop_favourite',      "ShopController@add_shop_favourite");

//   //notifications
//   $router->get("user_notifications",       "NotificationController@user_notifications");
//   $router->get("mark_read/{id}",            "NotificationController@mark_read");

});



/**
 * Routes for resource geo-city
 */
$router->get('geo-city', 'GeoCitiesController@all');
$router->get('geo-city/{id}', 'GeoCitiesController@get');
$router->post('geo-city', 'GeoCitiesController@add');
$router->put('geo-city/{id}', 'GeoCitiesController@put');
$router->delete('geo-city/{id}', 'GeoCitiesController@remove');

/**
 * Routes for resource geo-country
 */
$router->get('geo-country', 'GeoCountriesController@all');
$router->get('geo-country/{id}', 'GeoCountriesController@get');
$router->post('geo-country', 'GeoCountriesController@add');
$router->put('geo-country/{id}', 'GeoCountriesController@put');
$router->delete('geo-country/{id}', 'GeoCountriesController@remove');

/**
 * Routes for resource geo-region
 */
$router->get('geo-region', 'GeoRegionsController@all');
$router->get('geo-region/{id}', 'GeoRegionsController@get');
$router->post('geo-region', 'GeoRegionsController@add');
$router->put('geo-region/{id}', 'GeoRegionsController@put');
$router->delete('geo-region/{id}', 'GeoRegionsController@remove');

/**
 * Routes for resource gender
 */
$router->get('gender', 'GendersController@all');
$router->get('gender/{id}', 'GendersController@get');
$router->post('gender', 'GendersController@add');
$router->put('gender/{id}', 'GendersController@put');
$router->delete('gender/{id}', 'GendersController@remove');

/**
 * Routes for resource fixed-page
 */
$router->get('fixed-page', 'FixedPagesController@all');
$router->get('fixed-page/{id}', 'FixedPagesController@get');
$router->post('fixed-page', 'FixedPagesController@add');
$router->put('fixed-page/{id}', 'FixedPagesController@put');
$router->delete('fixed-page/{id}', 'FixedPagesController@remove');

/**
 * Routes for resource specialization
 */





