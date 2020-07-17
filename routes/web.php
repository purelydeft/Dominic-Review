<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
  sudo kill -9 `sudo lsof -t -i:9001`

*/


Route::get('/', function () {
    return view('welcome');
});

/* Register as coach */
Route::get('/register/coach','Auth\RegisterController@regsiter_coach')->name('register-as-coach');

Route::any('selectedCat','HomeController@selectedCat')->name('selectedCat');

Route::get('image-crop', 'HomeController@imageCrop');
Route::post('image-crop', 'HomeController@imageCropPost');

error_reporting(E_ALL);

#===============================================
#
#   CMS PAGES - DRH HTML PAGES
#
#===============================================
Route::any('add-report','HomeController@add_report')->name('add-report');
Route::any('set-goals','HomeController@set_goals')->name('set-goals');
Route::any('contact','HomeController@contact_us')->name('contact-us');
Route::any('save-contact-us','HomeController@save_contact_us')->name('save-contact-us');
Route::any('success','HomeController@success_page')->name('success_page');

// Course Section 
Route::any('course-listing','HomeController@listing')->name('listing');
Route::any('course-listing/football','HomeController@football_listing')->name('football-listing');
Route::any('course-listing/tennis','HomeController@tennis_listing')->name('tennis-listing');
Route::any('course-listing/school','HomeController@school_listing')->name('school-listing');
Route::any('course-detail/{id}','HomeController@course_detail')->name('course_detail');

Route::any('tennis-landing','HomeController@tennis_landing')->name('tennis_landing');
Route::any('school-landing','HomeController@school_landing')->name('school_landing');
Route::any('football-landing','HomeController@football_landing')->name('football_landing');
Route::any('tennis-pro','HomeController@tennis_pro')->name('tennis_pro');

// Camp Section
Route::any('camp-listing','HomeController@camp_listing')->name('camp-listing');
Route::any('camp-detail/{slug}','HomeController@camp_detail')->name('camp_detail');
Route::any('parent-register','HomeController@parent_register')->name('parent-register');
Route::any('book-a-camp/{slug}','HomeController@book_a_camp')->name('book-a-camp');
Route::any('submit-book-a-camp','HomeController@submit_book_a_camp')->name('submit-book-a-camp');

Route::any('coach-listing','HomeController@coach_listing')->name('coach-listing');
Route::any('coach/detail/{id}','HomeController@coach_detail')->name('coach-detail');

// My Family Section
Route::group(['middleware' => ['UserAuth'],'prefix' => 'user'], function() 
{
    Route::any('account-settings','HomeController@account_settings')->name('account_settings');
    Route::any('update-account-settings','HomeController@update_account_settings')->name('update_account_settings');
    Route::any('my-family','HomeController@my_family')->name('my-family');
    Route::any('family-member/add','HomeController@add_family_member')->name('add-family-member');
    Route::any('family-member/save','HomeController@save_family_member')->name('save-family-member');
    Route::any('family-member/edit/{id}','HomeController@edit_family_member')->name('edit-family-member');
    Route::any('family-member/update','HomeController@update_family_member')->name('update-family-member');
    Route::any('family-member/delete/{id}','HomeController@delete_family_member')->name('delete_family_member');
    Route::any('my-bookings','HomeController@my_bookings')->name('my-bookings');
    Route::any('booking/detail/{id}','HomeController@booking_detail')->name('booking-detail');
    Route::any('order/cancel/{id}','HomeController@cancel_booking')->name('cancel-booking');
    Route::any('order/invoice/download/{id}','HomeController@order_pdf')->name('order-inv-pdf');
    Route::any('parent-req-status','HomeController@parent_req_status')->name('parent_req_status');
    Route::any('course_booking','HomeController@course_booking')->name('course_booking');

    // Player reports in parent dashboard
    Route::get('player-reports','HomeController@player_report_listing')->name('player_report_listing');
    Route::any('player-report/{report_id}','HomeController@player_report_detail')->name('player_report_detail');

    // Copy Address
    Route::any('copy_address','HomeController@copy_address')->name('copy_address');

    // Add Family Mamber
    Route::any('medical_info_to_next','HomeController@medical_info_to_next')->name('medical_info_to_next');
    Route::any('child_cont_to_next','HomeController@child_cont_to_next')->name('child_cont_to_next');
    Route::any('med_beh_to_next','HomeController@med_beh_to_next')->name('med_beh_to_next');
    Route::any('complete_registration','HomeController@complete_registration')->name('complete_registration');

    // Childcare voucher
    Route::any('save_childcare_voucher','HomeController@save_childcare_voucher')->name('save_childcare_voucher');
});

// Coupon code
Route::any('submit-coupon','HomeController@submit_coupon')->name('submit-coupon');

#===============================================
#
#   COURSE PAGE SEARCH
#
#===============================================
Route::get('course_search','HomeController@course_search')->name('course_search');


#===============================================
#
#       Routes Files - Start Here
#
#===============================================
require __DIR__.'/routing/user/checkout.php';
require __DIR__.'/routing/home/routes.php';
require __DIR__.'/routing/home/ajax.php';


require __DIR__.'/routing/shop/routes.php';
require __DIR__.'/routing/shop/ajax.php';


require __DIR__.'/routing/admin/routes.php';
require __DIR__.'/routing/vendor/routes.php';
require __DIR__.'/routing/user/ajax.php';
require __DIR__.'/routing/user/routes.php';
#===============================================
#
#       Routes Files - End Here
#
#===============================================



/* Emails */
Route::get('/emailss',function(){
  $msg = \App\Models\Vendors\ChatMessage::find(8);
 return view('emails.chat.quote')->with('msg',$msg);
 });


Route::get('/demoo',function(){

$ups = upsArray();
$accessKey = $ups['UPS_ACCESS_KEY'];
$userId = $ups['UPS_USER_ID'];
$password = $ups['UPS_PASSWORD'];

$address = new \Ups\Entity\Address();
$address->setAttentionName('Test Test');
$address->setBuildingName('Test');
$address->setAddressLine1('Address Line 1');
$address->setAddressLine2('Address Line 2');
$address->setAddressLine3('Address Line 3');
$address->setStateProvinceCode('NYw');
$address->setCity('New Yorkwse');
$address->setCountryCode('USs');
$address->setPostalCode('100df00');

$xav = new \Ups\AddressValidation($accessKey, $userId, $password);
$xav->activateReturnObjectOnValidate(); //This is optional
try {
     $response = $xav->validate($address, $requestOption = \Ups\AddressValidation::REQUEST_OPTION_ADDRESS_VALIDATION, $maxSuggestion = 15);
     dd($response);
} catch (Exception $e) {
    var_dump($e);
}
});



#======================================================================

Route::get('/get-shipping-rate',function(){

	$ups = upsArray();
$accessKey = $ups['UPS_ACCESS_KEY'];
$userId = $ups['UPS_USER_ID'];
$password = $ups['UPS_PASSWORD'];
$rate = new Ups\Rate(
	$accessKey,
	$userId,
	$password
);

try {
    $shipment = new \Ups\Entity\Shipment();

    $shipperAddress = $shipment->getShipper()->getAddress();
    $shipperAddress->setPostalCode('99205');

    $address = new \Ups\Entity\Address();
    $address->setPostalCode('99205');
    $shipFrom = new \Ups\Entity\ShipFrom();
    $shipFrom->setAddress($address);

    $shipment->setShipFrom($shipFrom);

    $shipTo = $shipment->getShipTo();
    $shipTo->setCompanyName('Test Ship To');
    $shipToAddress = $shipTo->getAddress();
    $shipToAddress->setPostalCode('99205');

    $package = new \Ups\Entity\Package();
    $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
    $package->getPackageWeight()->setWeight(10);
    
    // if you need this (depends of the shipper country)
    $weightUnit = new \Ups\Entity\UnitOfMeasurement;
    $weightUnit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_KGS);
    $package->getPackageWeight()->setUnitOfMeasurement($weightUnit);

    $dimensions = new \Ups\Entity\Dimensions();
    $dimensions->setHeight(10);
    $dimensions->setWidth(10);
    $dimensions->setLength(10);

    $unit = new \Ups\Entity\UnitOfMeasurement;
    $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

    $dimensions->setUnitOfMeasurement($unit);
    $package->setDimensions($dimensions);

    $shipment->addPackage($package);

    //var_dump($rate->getRate($shipment));
} catch (Exception $e) {
    var_dump($e);
}
});