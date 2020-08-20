<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;      
use App\Traits\GeneralSettingTrait;
use App\Models\Shop\ShopOrder;
use App\User;
use App\Models\Admin\CmsPage;
use App\Category;
use App\VendorCategory;
use Carbon\Carbon;
use App\FAQs;
use App\Camp;
use App\Session;
use App\Testimonial;
use App\ContactDetail;
use App\Course;
use App\CourseDate;
use App\Accordian;
use App\CampCategory;
use App\ChildcareVoucher;
use App\ChildrenDetail;
use App\ChildActivity;
use App\CoachProfile;
use App\Coupon;
use App\SetGoal;
use App\ParentCoachReq;
use App\CoachUploadPdf;
use Cart;
use App\CoachDocument;
use App\CampPrice;
use App\Vouchure;
use App\PlayerReport;
use Hash;
use Mail;
use App\Competition;
use App\MatchReport;
use Newsletter;
use App\TestScore;
use App\Wallet;
use App\WalletHistory;
use App\MatchStats;
use App\MatchGameChart;
use App\NewsletterSubscription;
use App\Models\Shop\ShopCartItems;
use App\Models\Products\ProductCategory;
use App\Traits\ProductCart\UserCartTrait;
use App\Traits\EmailTraits\EmailNotificationTrait;
use Notification;
use App\Notifications\MyFirstNotification;

class HomeController extends Controller
{
    use RegistersUsers;
    use GeneralSettingTrait;
    use EmailNotificationTrait;
    use UserCartTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function index_crop(){
      return view('crop_image');
    }

    public function imageCrop(Request $request){
      $image_file = $request->image;
      list($type, $image_file) = explode(';', $image_file);
        list(, $image_file)      = explode(',', $image_file);
        $image_file = base64_decode($image_file);
        $image_name= time().'_'.rand(100,999).'.png';
        $path = public_path('uploads/'.$image_name);

        file_put_contents($path, $image_file);
        return response()->json(['status'=>true]);
    }


    public function register()
    {
        if(Auth::check()){
              $url = url(route('request.messages')).'?type=logged';
              return redirect($url);
        }
        return view('auth.register2');
    }

    public function showCmsPage($slug) {
      $page = CmsPage::FindBySlugOrFail($slug);
      return view('cmspage')->with('page', $page);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        if(!empty($request->test)){
            return getUserNotifications();
            return \App\User::with('newVendorsBusinessMessages','newVendorsBusinessMessages.unReadMessages')->where('id',\Auth::user()->id)->first();
        }

      $slug = 'homepage';
      $categories = Category::where(['status'=> 1, 'parent'=> 0])->orderBy('label','ASC')->get();
      $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','home-page')->where('status','1')->get(); 

      return view('home', $this->getArrayValue($slug))->with('categories',$categories)->with('testimonial',$testimonial);
    }



#---------------------------------------------------------------------
# ajax register
#----------------------------------------------------------------------


public function userRegisterUpdate(Request $request,$token)
{

        $v= \Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'id_proof' => ['required', 'image']
            
        ],[
            'id_proof.image' => 'Please upload image format of document'
        ]);


          $user = User::where('role','vendor')
                  ->where('custom_token',$token);
                    


 
        if($v->fails()){
             return response()->json(['status' => 0,'errors' => $v->errors()]);
        }elseif($user->count() == 0){
             return response()->json(['status' => 0,'errors' => ['Token has been Expired.']]);
        }else{
            
            $status = $this->updateAccount($request,$user->first());
            $url = url(route('request.messages')).'?type=account-updated';
            return response()->json([
                'status' => 8,
                'redirectLink' => $url
            ]);

        }
}




#---------------------------------------------------------------------
# ajax register
#----------------------------------------------------------------------

public function updateAccount($request,$u)
{
    $id_proof = uploadFileWithAjax('videos/vendors/cover/',$request->file('id_proof'));
     
    $u->first_name = $request->first_name;
    $u->last_name = $request->last_name;
    $u->name = $request->first_name.' '.$request->last_name;
    $u->phone_number = $request->phone_number;
    $u->user_location = $request->location;
    $u->website_url = $request->website_url;
    $u->ein_bs_number = $request->ein_bs_number;
    $u->age = Carbon::parse($request->age)->format('Y-m-d');
    $u->id_proof = $id_proof;
    $u->status = 0;
    $u->custom_token = null;
    $u->updated_status = 1;
    if($u->save()) {

        $this->NewVendorEmailSuccess($u);
         return 1;
    }
}


#---------------------------------------------------------------------
# ajax register
#----------------------------------------------------------------------


public function userRegister(Request $request)
{
        $v= \Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'password' => ['required','min:6','confirmed']
        ]);


        // print_r($request->all());
        // die;

        if($v->fails()){
             return response()->json(['status' => 0,'errors' => $v->errors()]);
        }else{

            if(!empty($request->type)){
              $status = $this->saveNewVendor($request);
            }else{
              $status = $this->saveNewUser($request,'user');
            }
          

            return response()->json(['status' => 1,'message' => 'We sent you an activation code. Check your Email and click on the link to verify.']);

        }
}






#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function saveNewVendor($request)
{


    $id_proof = uploadFileWithAjax('videos/vendors/cover/',$request->file('id_proof'));
    $u = new \App\User;
    $u->first_name = $request->first_name;
    $u->last_name = $request->last_name;
    $u->name = $request->first_name.' '.$request->last_name;
    $u->email = $request->email;
    $u->user_location = $request->user_location;
    $u->phone_number = $request->phone_number;
    $u->user_location = $request->location;
    $u->latitude = $request->latitude;
    $u->longitude = $request->longitude;
    $u->website_url = $request->website_url;
    $u->ein_bs_number = $request->ein_bs_number;
    $u->age = Carbon::parse($request->age)->format('Y-m-d');
    $u->id_proof = $id_proof;
    $u->refer_data = $this->getReferAccount($request);
    $u->status = 0;
    $u->role = 'vendor';
    $u->password = \Hash::make($request->password);
    if($u->save() && $this->addBusinessCategories($request,$u->id) == 1) {

          $u->sendEmailVerificationNotification();
          $this->NewVendorEmailSuccess($u);
         return 1;
    }

}

#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------

public function getReferAccount($request)
{
  if(!empty($request->reference_business_name)):
  $category = \App\Category::where('id',$request->business_type);

   $arr = [

     'business_type' => $category->count() > 0 ? $category->first()->label : 'N/A',
     'reference_business_name' => $request->reference_business_name,
     'reference_email' => $request->reference_email,
     'reference_contact_number' => $request->reference_contact_number,
     'business_address' => $request->business_address
      
   ];
   return json_encode($arr);
 endif;
}

#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function addBusinessCategories($request,$user_id)
{
             foreach ($request->categories as $key => $value) {
                    
                 $parent = $this->categorySave($value,0,$user_id);
             }
             return 1;
}



#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------



public function categorySave($value,$parent=0,$user_id)
{
        $v= VendorCategory::where('parent',$parent)->where('category_id',$value)->where('user_id',$user_id);
        $id = 0;
        if($v->count() == 0){
            $vCate = new VendorCategory;
            $vCate->parent = $parent;
            $vCate->category_id = $value;
            $vCate->user_id = $user_id;
            $vCate->status = 1;
            $vCate->save();
            $id = $vCate->id;

        }else{
            $category = $v->first();
            $id = $category->id;
        }
        return $id;
}

#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function saveNewUser($request,$role="user")
{
    
            $user = \App\User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'role' => $role,
                'password' => \Hash::make($request->password),
            ]);

 
           $user->sendEmailVerificationNotification();
           return 1;
}





#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function userLogin(Request $request,$role="user")
{
       $v= \Validator::make($request->all(), [
           
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            
        ]);

        if($v->fails()){
             return response()->json(['status' => 0,'errors' => $v->errors()]);
        }else{

            $role = $this->login($request);
            return response()->json($role);

        }
}


#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function userLoginPopup(Request $request,$role="user")
{
       $v= \Validator::make($request->all(), [
           
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            
        ]);

        if($v->fails()){
             return response()->json(['status' => 0,'errors' => $v->errors()]);
        }else{

                    if (Auth::attempt(['email' => $request->email, 'password' => $request->password,'role' => 'user']))
                    {
                            if(Auth::check() && Auth::user()->email_verified_at){
                               if(Auth::user()->status == 1):
                                       $arr = [
                                             'status' => 1,
                                             'message' => 'Please wait... Redirecting to your dashboard.',
                                             'redirectLink' => url(route('user_dashboard')),
                                             'users' => Auth::user(),
                                             'upcoming_events' => Auth::user()->UpcomingUserEvents
                                        ];
                                else:

                                     Auth::logout();
                                     
                                         $arr = [
                                             'status' => 2,
                                             'message' => 'Your account is blocked by the Admin.'
                                           
                                        ];

                                endif;

                            } else {

                              Auth::logout();
                             
                                 $arr = [
                                     'status' => 2,
                                     'message' => 'Your account is not verified yet.'
                                   
                                ];
                            }

                   } else {
                    
                         $arr = [
                               'status' => 2,
                               'message' => 'Invalid Email | Password'
                             
                          ];
                  }
            return response()->json($arr);

        }
}

#-----------------------------------------------------------------------
#  save new user
#-----------------------------------------------------------------------


public function login($request)
{
    $arr =[];
      if (Auth::attempt(['email' => $request->email, 'password' => $request->password,'role' => 'vendor']))
        {

           // return Auth::user();
            if(Auth::check() && Auth::user()->email_verified_at){

                                if(Auth::user()->status == 1):
                                            $u = User::find(Auth::user()->id);
                                            $u->login_count = ($u->login_count + 1);
                                            $u->save();

                                      $arr = [
                                                'status' => 1,
                                                'message' => 'Please wait... Redirecting to your dashboard.',
                                                'redirectLink' => url(route('vendor_dashboard'))
                                            ];
                                else:

                                            Auth::logout();
                                            $arr = [
                                                'status' => 2,
                                                'message' => 'Your account is under verification process.',
                                                'redirectLink' => url(route('vendor_dashboard'))
                                            ];

                                endif;
                
                

            
            }else{
              Auth::logout();
             

                  $arr = [
                    'status' => 2,
                    'message' => 'Your account is not verified yet.',
                    'redirectLink' => url(route('vendor_dashboard'))
                ];
            }

        }elseif (Auth::attempt(['email' => $request->email, 'password' => $request->password,'role' => 'admin']))
        {
              
                $arr = [
                    'status' => 1,
                    'message' => 'Please wait... Redirecting to your dashboard.',
                    'redirectLink' => url(route('admin_dashboard'))
                ];

        }elseif (Auth::attempt(['email' => $request->email, 'password' => $request->password,'role' => 'user']))
        {

           
            if(Auth::check() && Auth::user()->email_verified_at){

                         $url = !empty($request->redirectLink) ? $request->redirectLink : url(route('user_dashboard'));

               

                             if(Auth::user()->status == 1):
                                            $this->TransferCartItemToUserTable();
                                            $arr = [
                                                'status' => 1,
                                                'message' => 'Please wait... Redirecting to your dashboard.',
                                                'redirectLink' => $url
                                            ];
                                else:

                                            Auth::logout();
                                            $arr = [
                                                'status' => 2,
                                                'message' => 'Your account is blocked by the Admin.',
                                                'redirectLink' =>  $url
                                            ];

                                endif;



            } else {

              Auth::logout();
             
                 $arr = [
                    'status' => 2,
                     'message' => 'Your account is not verified yet.'
                   
                ];
            }

        } else {
          
               $arr = [
                    'status' => 2,
                     'message' => 'Invalid Email | Password'
                   
                ];
        }

        return $arr;
}







#-------------------------------------------------------------------------
#
#-----------------------------------------------------------------------



public function requestMessages(Request $request)
{
    $type = !empty($request->type) ? $request->type : 1;
     return view('auth.requestMessages')
          ->with('type',$type);
}


#-------------------------------------------------------------------------
#
#-----------------------------------------------------------------------



public function about()
{
    return view('home.cms.about_us');
}



#-------------------------------------------------------------------------
#
#-----------------------------------------------------------------------



public function contact()
{
    return view('home.cms.contact_us');
}


 
#-------------------------------------------------------------------------
# email for email template testing
#-----------------------------------------------------------------------



public function email()
{
   return $this->VendorOrderSuccessOrderSuccess(11);
             $o = \App\Models\Order::find(10);
             $order = \App\Models\EventOrder::where('order_id',$o->id)
                               //->where('vendor_id',35)
                                ->where('type','order')->get();
    return view('emails.customEmail')->with('order',$order)->with('o',$o);
}

#-------------------------------------------------------------------------
# email for email template testing
#-----------------------------------------------------------------------


public function faq() {
    $faqs = FAQs::whereIn('type', ['user', 'vendor'])->get();
    return view('home.faq.faq')->with(['faqs' => $faqs]);
}

#-------------------------------------------------------------------------
# email for email template testing
#-----------------------------------------------------------------------



public function vendorUpdate($token)
{
   $user = User::where('role','vendor')
                  ->where('custom_token',$token);
   if($user->count() == 0){
    return redirect(route('request.messages').'?type=token-expired');
   }
  
   return view('auth.updateVendor')->with('user',$user->first());
}


/*-------------------------------------
|   CMS PAGES 
|--------------------------------------*/

/* Add Report */
public function add_report()
{
    return view('cms.addreport');
}

/********************************************
|
|   Course Management - Start Here
|
|********************************************/ 

/* Course Listing Page */
public function listing(Request $request)
{
   $slug = 'course-listing';

    if(!empty(request()->get('course'))){
      $course_name = request()->get('selected_course_name');
      $subtype = request()->get('subtype');
      $level = request()->get('level');

      //dd($course,$course_name,$age_group,$level);

      $course = \DB::table('courses')
                 ->where('title', '=', $course_name)
                 ->where('subtype', '=', $subtype)
                 // ->where('subtype', '=', $level)
                 ->where('status',1)
                 ->orderBy('sort','asc')->get();

    }else{
      $course = Course::orderBy('sort','asc')->where('status',1)->get();
    }

    $accordian = Accordian::where('page_title',$slug)->where('status','1')->orderBy('sort','asc')->get();  

    // Get subtype for tennis courses
    $subtype = ProductCategory::where('parent', 156)->where('subparent',0)->get();

    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','course-listing')->where('status','1')->get(); 
    return view('cms.course.listing',compact('testimonial','course','course_name','age_group','level','accordian','subtype'));
}

/* Football Course Listing Page */
public function football_listing(Request $request)
{
    $slug = 'course-listing/football';

    if(!empty(request()->get('course'))){
          $course_name = request()->get('selected_course_name');
          $subtype = request()->get('subtype');
          $level = request()->get('level');

          //dd($course,$course_name,$age_group,$level);

          $course = \DB::table('courses')
                     ->where('title', '=', $course_name)
                     ->where('subtype', '=', $subtype)
                     // ->where('subtype', '=', $level)
                     ->where('status',1)
                     ->orderBy('sort','asc')->get();

    }else{
        $course = Course::orderBy('sort','asc')->where('status',1)->where('type','157')->get();
    }

    $accordian = Accordian::where('page_title',$slug)->where('status','1')->orderBy('sort','asc')->get();

    // Get subtype for tennis courses
    $subtype = ProductCategory::where('parent', 156)->where('subparent',0)->get();

    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','course-listing/football')->where('status','1')->get(); 
    return view('cms.course.football-listing',compact('testimonial','course','course_name','age_group','level','accordian','subtype'));
}

/* Tennis Course Listing Page */
public function tennis_listing(Request $request)
{
    $slug = 'course-listing/tennis';

    if(!empty(request()->get('course'))){
      $course_name = request()->get('selected_course_name');
      $subtype = request()->get('subtype');
      $level = request()->get('level');

      $course = \DB::table('courses')
                 ->where('title', '=', $course_name)
                 ->where('subtype', '=', $subtype)
                 // ->where('subtype', '=', $level)
                 ->where('status',1)
                 ->where('type','156')
                 ->orderBy('sort','asc')->get();

    }else{
        $course = Course::orderBy('sort','asc')->where('status',1)->where('type','156')->get();
    }
    $accordian = Accordian::where('page_title',$slug)->where('status','1')->orderBy('sort','asc')->get();  

    // Get subtype for tennis courses
    $subtype = ProductCategory::where('parent', 156)->where('subparent',0)->get();

    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('status','1')->where('page_title','course-listing/tennis')->get(); 
    return view('cms.course.tennis-listing',compact('testimonial','course','course_name','age_group','level','accordian','subtype'));
}

/* School Course Listing Page */
public function school_listing(Request $request)
{
    $slug = 'course-listing/school';

    if(!empty(request()->get('course'))){
      $course_name = request()->get('selected_course_name');
      $subtype = request()->get('subtype');
      $level = request()->get('level');

      $course = \DB::table('courses')
                 ->where('title', '=', $course_name)
                 ->where('subtype', '=', $subtype)
                 // ->where('subtype', '=', $level)
                 ->where('status',1)
                 ->orderBy('sort','asc')->get();

    }else{
        $course = Course::orderBy('sort','asc')->where('status',1)->where('type','191')->get();
    }

    $accordian = Accordian::where('page_title',$slug)->where('status','1')->orderBy('sort','asc')->get();

    // Get subtype for tennis courses
    $subtype = ProductCategory::where('parent', 156)->where('subparent',0)->get();

    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','course-listing/school')->where('status','1')->get(); 
    return view('cms.course.school-listing',compact('testimonial','course','course_name','age_group','level','accordian','subtype'));
}

/* Course Detail Page */
public function course_detail($id)
{
    $decode_id = base64_decode($id);
    $course = Course::where('id','=',$decode_id)->first();
    $course_dates = CourseDate::where('course_id','=',$decode_id)->get();
    return view('cms.course.course-listing-detail',compact('course','course_dates'));
}

/* Tennis Landing Page */
public function tennis_landing()
{
    $providers = ChildcareVoucher::orderBy('id','desc')->get();
    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','tennis-landing')->where('status','1')->get();
    $accordian = Accordian::where('page_title','tennis-landing')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_download = Accordian::where('page_title','tennis-landing-download')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_parent_info = Accordian::where('page_title','tennis-landing-parent-info')->where('status',1)->orderBy('sort','asc')->get(); 
    return view('cms.course.tennis-landing',compact('providers','testimonial','accordian','accordian_download','accordian_parent_info'));
}

/* School Landing Page */
public function school_landing()
{
    $providers = ChildcareVoucher::orderBy('id','desc')->get();
    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','school-landing')->where('status','1')->get();
    $accordian = Accordian::where('page_title','school-landing')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_download = Accordian::where('page_title','school-landing-download')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_parent_info = Accordian::where('page_title','school-landing-parent-info')->where('status',1)->orderBy('sort','asc')->get(); 
    return view('cms.course.school-landing',compact('providers','testimonial','accordian','accordian_download','accordian_parent_info'));
}

/* Football Landing Page */
public function football_landing()
{
    $providers = ChildcareVoucher::orderBy('id','desc')->get();
    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','football-landing')->where('status','1')->get();
    $accordian = Accordian::where('page_title','football-landing')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_download = Accordian::where('page_title','football-landing-download')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_parent_info = Accordian::where('page_title','football-landing-parent-info')->where('status',1)->orderBy('sort','asc')->get(); 
    return view('cms.course.football-landing',compact('providers','testimonial','accordian','accordian_download','accordian_parent_info'));
}

/* Tennis Pro Page */
public function tennis_pro()
{
    return view('cms.course.tennis-pro');
}

/* Course Booking */
public function course_booking(Request $request)
{
  // dd($request->all());
  $course_id = $request->course_id;
  $child_id  = $request->child_id;
  $course    = Course::where('id',$course_id)->first(); 

  $course_cat = $course->type; 
  $course_season = $course->season;
  $cat = \DB::table('product_categories')->where('id',$course_cat)->first(); 

  if($cat->slug == 'tennis'){
      $early_bird_enable = getAllValueWithMeta('check_tennis_percentage', 'early-bird');
      $percentage = getAllValueWithMeta('tennis_percentage', 'early-bird');
  }elseif($cat->slug == 'football'){
      $early_bird_enable = getAllValueWithMeta('check_football_percentage', 'early-bird');
      $percentage = getAllValueWithMeta('football_percentage', 'early-bird');
  }elseif($cat->slug == 'schools'){
      $early_bird_enable = getAllValueWithMeta('check_school_percentage', 'early-bird');
      $percentage = getAllValueWithMeta('school_percentage', 'early-bird');
  }

    $early_bird_date = getAllValueWithMeta('early_bird_date', 'early-bird'); 
    $early_bird_time = getAllValueWithMeta('early_bird_time', 'early-bird');
    $endDate = strtotime(date('Y-m-d',strtotime($early_bird_date)).' 23:59:00');
    $currntD = strtotime(date('Y-m-d H:i:s'));

  $add_course = new ShopCartItems;
  $add_course->shop_type  = 'course';
  $add_course->quantity   = 1;
  $add_course->vendor_id  = 1;
  $add_course->product_id = $course_id;
  $add_course->user_id    = \Auth::user()->id;
  $add_course->course_season = $course_season;

if($currntD >= $endDate)
{   
    $add_course->price  = $course->price;
    $add_course->total  = $course->price;
}else{
  if($early_bird_enable == '1'){
    $cour_price = $course->price;
    $dis_price = $cour_price - (($cour_price) * ($percentage/100));

    $add_course->price  = $dis_price;
    $add_course->total  = $dis_price;
  }else{
    $add_course->price  = $course->price;
    $add_course->total  = $course->price;
  }
}

  $add_course->child_id = $child_id;

  if($add_course->save()){
    $output = 1;
  }else{
    $output = 0;
  }

    $data = array(
                'output'   => $output,
            );

    return response()->json($data);
  
} 

  /* Course Page Search */
  public function course_search(Request $request) 
  {
    if($request->ajax())
        {
            $query = $request->get('query'); 
            
            if($query != '')
            {
                $data = \DB::table('courses')
                ->select('courses.*')
                ->where( 'title', 'LIKE', '%' . $query . '%' )
                ->orderBy('courses.id','desc')->paginate (4)->setPath ( '' );
                
            }
            else
            {
                $data = \DB::table('courses')
                ->select('courses.*')
                ->orderBy('courses.id','desc')
                ->paginate(4); 
            }

            $total_row = $data->count(); 
            
            if($total_row > 0)
            {
                $list = '';
                foreach($data as $row)
                {
                    $list .= '<li><a href="'.\URL::to("/course-detail").'/'.base64_encode($row->id).'">'.$row->title.'</a></li>';
                }
            }
            else
            {
                $list = '';
            }
            
            $data = array(
                'total_data'   => $total_row,
                'table_list'   => $list,
            );

            echo json_encode($data);
        }  
  }

  /* Courses Filter */
  public function selectedCat(Request $request)
  {
        $cat_id = $request->selectedCat;
        $sub_cat = ProductCategory::where('parent',$cat_id)->where('subparent','0')->get();

        if(count($sub_cat) > 0)
            {
                $output = '<option value="">All</option>';

                foreach($sub_cat as $row)
                {
                    $output .= '<option value="'.$row->id.'">'.$row->label.'</option>';
                }

            }else{
                $output = '';
            }

            $data = array(
                'option'   => $output,
            );

            echo json_encode($data);
    }

/********************************************
|
|   Course Management - End Here
|
|********************************************/ 



/********************************************
|
|   Camp Management - Start Here
|
|********************************************/ 

/* Camp Listing Page */
public function camp_listing() 
{
    $camp_categories = CampCategory::orderBy('id','desc')->where('status','1')->get();
    $providers = ChildcareVoucher::orderBy('id','desc')->get();
    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','camp-listing')->where('status','1')->paginate(1);
    $accordian = Accordian::where('page_title','camp-listing')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_download = Accordian::where('page_title','camp-download')->where('status',1)->orderBy('sort','asc')->get(); 
    $accordian_parent_info = Accordian::where('page_title','camp-parent-info')->where('status',1)->orderBy('sort','asc')->get(); 
    return view('cms.camp.camp-listing',compact('camp_categories','providers','testimonial','accordian','accordian_download','accordian_parent_info'));
}

/* Camp Detail Page */
public function camp_detail($slug) 
{
    // $seesion_slug = Session::put('camp_slug',$slug);
    $camp_category = CampCategory::FindBySlugOrFail($slug); 
    $testimonial = Testimonial::select(['id','title', 'description','status','slug','image'])->where('page_title','camp-detail')->where('status','1')->get(); 
    return view('cms.camp.camp-detail')->with('camp_category',$camp_category)->with('testimonial',$testimonial);
}

/* Book a Camp Page */
public function book_a_camp($slug) 
{
    $camp = Camp::FindBySlugOrFail($slug); 
    $accordian_book_a_camp = Accordian::where('page_title','book-a-camp')->where('status',1)->orderBy('sort','asc')->get(); 
    $session = Session::where('status',1)->get();
    return view('cms.camp.book-a-camp',compact('camp','accordian_book_a_camp','session'));
}

/* Book a Camp Page */
public function submit_book_a_camp(Request $request) 
{  
    $camp_id = $request->camp_id; 
    $week = $request->week; 
    $child = $request->child_id;   

    $camp_price = CampPrice::where('camp_id',$camp_id)->first();

    $early_price = $camp_price->early_price; 
    $early_percent = $camp_price->early_percent;

    $lunch_price = $camp_price->lunch_price;
    $lunch_percent = $camp_price->lunch_price;

    $fullday_price = $camp_price->fullday_price;
    $fullday_percent = $camp_price->fullday_percent;

    $latepickup_price = $camp_price->latepickup_price;
    $latepickup_percent = $camp_price->latepickup_price;

    $morning_price = $camp_price->morning_price;
    $morning_seats = $camp_price->morning_seats;
    $morning_percent = $camp_price->morning_percent;

    $afternoon_price = $camp_price->afternoon_price;
    $afternoon_seats = $camp_price->afternoon_seats;
    $afternoon_percent = $camp_price->afternoon_percent;

    $arrNewSku = array();
    $incI = 0;
    $count_early = array();
    $count_late_pickup = array();
    $count_lunch_club = array();

    $camp_morning = array();
    $camp_noon = array();
    $camp_full = array();

      if(isset($week))
      {
        foreach($week as $arrKey => $arrData){ 

          if(isset($arrData['early_drop'])){  
            $arrNewSku[$incI]['early_drop'] = isset($arrData['early_drop']) ? $arrData['early_drop'] : '';
            $count_early[] = count($arrData['early_drop'])*$early_price;
          }

          if(isset($arrData['late_pickup'])){  
            $arrNewSku[$incI]['late_pickup'] = isset($arrData['late_pickup']) ? $arrData['late_pickup'] : '';
            $count_late_pickup[] = count($arrData['late_pickup'])*$latepickup_price;
          }

          if(isset($arrData['lunch'])){  
            $arrNewSku[$incI]['lunch'] = isset($arrData['lunch']) ? $arrData['lunch'] : '';
            $count_lunch_club[] = count($arrData['lunch'])*$lunch_price;
          }

          if(isset($arrData['camp']))
          {  
            $camp_data = array();;
            foreach ($arrData['camp'] as $sku){ 
              $camp_array = explode('-',$sku);
              $camp_data[] = $camp_array[2];
            }
            $camp_counts = array_count_values($camp_data);
            $camp_morning[] = (isset($camp_counts['mor']) ? $camp_counts['mor'] : 0)*$morning_price;
            $camp_noon[] = (isset($camp_counts['noon']) ? $camp_counts['noon'] : 0)*$afternoon_price;
            $camp_full[] = (isset($camp_counts['full']) ? $camp_counts['full'] : 0)*$fullday_price;
          }

            $incI++;
        }

        // dd($request->all(), $camp_morning,$camp_noon,$camp_full);

        $early_drop_price = isset($count_early) ? array_sum($count_early) : '';
        $late_pickup_price = isset($count_late_pickup) ? array_sum($count_late_pickup) : '';
        $lunch_club_price = isset($count_lunch_club) ? array_sum($count_lunch_club) : '';

        $morning_price = isset($camp_morning) ? array_sum($camp_morning) : '';
        $afternoon_price = isset($camp_noon) ? array_sum($camp_noon) : '';
        $fullweek_price = isset($camp_full) ? array_sum($camp_full) : '';

        // Add Prices 
        $add_price = $early_drop_price+$late_pickup_price+$lunch_club_price+$morning_price+$afternoon_price+$fullweek_price;

        $sel_week = json_encode($request->week);



        $add_course = new ShopCartItems;
        $add_course->shop_type  = 'camp';
        $add_course->quantity   = 1;
        $add_course->vendor_id  = 1;
        $add_course->product_id = $camp_id;
        $add_course->user_id    = \Auth::user()->id;
        $add_course->price      = $add_price;
        $add_course->total      = $add_price;
        $add_course->week       = $sel_week;
        $add_course->camp_price = $add_price;
        $add_course->child_id   = $child;

        if($add_course->save()){
          return \Redirect::back()->with('success',' Camp added to cart successfully!');
        }else{
          return \Redirect::back()->with('error',' Something went wrong!');
        }

    }
}

/********************************************
|
|   Camp Management - End Here
|
|********************************************/ 



/* Parent Register Form */
public function parent_register() 
{

    return view('cms.camp.parent-register');
}

/* Account Settings */
public function account_settings() {
    $user = User::where('id',\Auth::user()->id)->first(); 
    return view('coach.account-settings')->with('user',$user);
} 

/* Linked coaches in parent profile */
public function linked_coaches(){
    $requests = ParentCoachReq::where('parent_id',Auth::user()->id)->where('status',1)->get(); 
    return view('cms.my-family.linked-coach',compact('requests'));
} 

/* Update Account Settings */
public function update_account_settings(Request $request) { 
    $logined_user = \Auth::user()->role_id;

    $acc               =    User::find($request->user_id);
    $acc->role_id      =    $logined_user;
    $acc->name         =    $request->first_name.' '.$request->last_name;
    $acc->first_name   =    $request->first_name;
    $acc->last_name    =    $request->last_name;
    $acc->gender       =    $request->gender;
    $acc->date_of_birth=    $request->date_of_birth;
    $acc->address      =    $request->address;
    $acc->town         =    $request->town;
    $acc->postcode     =    $request->postcode;
    $acc->county       =    $request->county;
    $acc->country      =    $request->country;
    $acc->relation     =    $request->relation;
    $acc->email_verified_at = '2020-03-18 11:10:38';
    $acc->save();
    return \Redirect::back()->with('success',' Account Settings has been updated successfully!');
}

/* Requested by parent for coach */
public function request_by_parent() {
  return view('coach.parent-requests');
}

/* Dismiss notification request by coach */
public function dismiss_req_by_coach(Request $request) 
{
    $req = ParentCoachReq::find($request->id); 
    $req->dismiss_by_coach = '0';
    $req->save();

    return \Redirect::back()->with('success','Notification has been dismissed successfully');
} 

/* Dismiss notification request by parent */
public function dismiss_req_by_parent(Request $request) 
{
    $req = ParentCoachReq::find($request->id); 
    $req->dismiss_by_parent = '0';
    $req->save();

    return \Redirect::back()->with('success','Notification has been dismissed successfully');
}

/* Coach-Parent Linking */
public function parent_coach(Request $request)
{
  // dd($request->all());
  $check = ParentCoachReq::where('parent_id',$request->parent_id)->where('child_id',$request->child)->first();

  if(!empty($check))
  {
    $linked_coach = getUsername($check->coach_id);
    $coach = User::where('id',$request->coach_id)->first(); 
    $child = User::where('id',$request->child)->first(); 

    if($check->coach_id == $request->coach_id)
    { 
      if($check->status == 1)
      {
        $msg = 'Your player is alreday linked with "<b>'.$linked_coach.'</b>" coach.';
      }else{
        $msg = 'Your player is alreday requested to "<b>'.$linked_coach.'</b>" coach.';
      }
       
    }else{
      
      $msg = '"<b>'.$child['name'].'</b>" player is already linked "<b>'.$linked_coach. '</b>" coach.';
      $msg .= "<br/>";
      $msg .= 'If you want to link with <b>'.$coach['name']. '</b> coach then you unlink with <b>'.$linked_coach. '</b> coach.';
    }
    

    return \Redirect::back()->with('error',$msg);
  }else{

    $coach_parent = new ParentCoachReq;
    $coach_parent->coach_id = $request->coach_id;
    $coach_parent->parent_id = $request->parent_id;
    $coach_parent->child_id = $request->child;
    $coach_parent->status = 0;
    if($coach_parent->save()){
      return \Redirect::back()->with('success','Your request has been sent successfully!');
    }
  }
} 

/* Coach Profile */
public function coach_profile() {
  $logined_user = \Auth::user()->id;  
  $user = CoachProfile::where('coach_id',$logined_user)->first();
  return view('coach.profile',compact('logined_user', 'user'));
}

/* Update Coach Profile */
public function update_coach_profile(Request $request) { 

    $user_id = \Auth::user()->id; 

  if(!empty($request->profile_image))
  {
      $filename = $request->profile_image;
      if ($request->hasFile('profile_image')) {
          $profile_image = $request->file('profile_image');
          $filename = time().'.'.$profile_image->getClientOriginalExtension();  
          $destinationPath = public_path('/uploads');
          $img_path = public_path().'/uploads/'.$request->profile_image;
        //   if (file_exists($img_path)) {
        //     unlink($img_path);
        // }
          $profile_image->move($destinationPath, $filename);
      }
  }
      
    $acc                        =    CoachProfile::findOrNew($request->coach_profile_id);
    $acc->coach_id              =    isset($request->coach_id) ? $request->coach_id : '';
    $acc->profile_name          =    isset($request->profile_name) ? $request->profile_name : '';
    $acc->qualified_clubs       =    isset($request->qualified_clubs) ? $request->qualified_clubs : '';
    $acc->qualifications        =    isset($request->qualifications) ? $request->qualifications : '';
    $acc->personal_statement    =    isset($request->personal_statement) ? $request->personal_statement : '';

    if(!empty($request->profile_image))
    {
      $acc->image                 =    isset($filename) ? $filename : '';
    }
    $acc->save(); 
    return \Redirect::back()->with('success','Coach Profile has been updated successfully!');
}

/* Update Password */
public function updatePassword(Request $request) {  
        if(Hash::check($request->oldpassword, Auth::user()->password))
        {          
          $user_id = Auth::User()->id;
          $user = User::find($user_id); 
          $user->password = Hash::make($request->password);    
          $user->save();
          return \Redirect::back()->with('success','Password has been updated successfully');
        }
        else
        {  
          return \Redirect::back()->with('success','Please enter correct current password');
        }
    }

/*--------------------------------
|   Coach Qualifications
|---------------------------------*/
public function qualifications() 
{
  $logined_coach = \Auth::user()->id;
  $uploaded_documents = CoachDocument::where('coach_id',$logined_coach)->get();
  return view('coach.qualification',compact('uploaded_documents'));
}

/*--------------------------------
|   Add money to wallet
|---------------------------------*/
public function add_money_to_wallet()
{
    return view('wallet.add-money-to-wallet');
}

public function stripe_wallet(Request $request) 
{
    $stripe = SripeAccount();
    $pk = $stripe['pk'];
    $amount = $request->wallet_amount*100;
    $output = '
    <p>Add funds to your wallet with your payment card.</p>
    <script
         src="https://checkout.stripe.com/checkout.js" class="stripe-button new-main-button"
         data-key="'.$pk.'"
         data-amount="'.$amount.'"
         data-name="DRH Panel"
         data-class="DRH Panel"
         data-description="Shopping"
         data-email="{{Auth::user()->email}}"   
         data-currency="gbp"                           
         data-locale="auto">
    </script>';

    $data = array(
        'output' => $output,
    );

    echo json_encode($data); 
}

public function add_wallet_amt(Request $request) 
{   
    $user_id = $request->user_id;
    $check_wallet = Wallet::where('user_id',$user_id)->first();

    $walletHistory = WalletHistory::create($request->all()); 
    $walletHistory->type = 'credit';
    $walletHistory->save();

    if(!empty($check_wallet))
    {
        $creditWalletHistory = WalletHistory::where('type','credit')->where('user_id',$user_id)->get();
        $debitWalletHistory = WalletHistory::where('user_id',$user_id)->where('type','debit')->get();

        $wallet_amt1 = [];
        foreach($creditWalletHistory as $wh){
            $wallet_amt1[] = $wh->money_amount;
        }

        $wallet_amt2 = [];
        foreach($debitWalletHistory as $wh){
            $wallet_amt2[] = $wh->money_amount;
        }

        $total_credit_amt = array_sum($wallet_amt1);
        $total_debit_amt = array_sum($wallet_amt2);

        $wallet_amt = $total_credit_amt - $total_debit_amt;
        Wallet::where('user_id',$user_id)->update(array('money_amount' => $wallet_amt));

    }else{
        $wallet = Wallet::create($request->all()); 
        $wallet->save(); 
    } 

    return \Redirect::back()->with('success',' Amount has been added successfully in wallet!');
}   

/*--------------------------------
|   Coach Reports
|---------------------------------*/
public function coach_report() 
{
    $player_id = request()->get('coach_player_id');  

    $season_id = request()->get('season_id');
    $course_id = request()->get('course_id');
    $user_id = request()->get('player_id');

    //dd($player_id,$season_id,$course_id,$user_id);

    // Filter for complex report
    if(!empty($player_id))
    {   
        $player_rep = PlayerReport::where('type','complex')->where('player_id',$player_id)->where('status',1)->orderBy('id','desc')->first();
    }else{
        $player_rep = '';
    }

    // Filter for simple report
    if(!empty($season_id) && !empty($course_id) && !empty($user_id))
    {
        $player_report = PlayerReport::where('type','simple')->where('season_id',$season_id)->where('course_id',$course_id)->where('player_id',$user_id)->where('status',1)->orderBy('id','desc')->first();
    }else{
        $player_report = '';
    }

    return view('coach.report',compact('player_rep','player_report','season_id','course_id','user_id'));
}

/*---------------------------------
|   Save Simple Report
|---------------------------------*/
public function save_simple_report(Request $request)
{
  if(!empty($request->season_id) && !empty($request->course_id) && !empty($request->player_id))
  {
    $check_report = PlayerReport::where('type','simple')->where('season_id',$request->season_id)->where('player_id',$request->player_id)->where('course_id',$request->course_id)->get();

    $date = Carbon::now();

    $rp = $request->input('rp');  

    if(!empty($rp))
    {
        $update_new_rp = PlayerReport::where('id',$rp)->update(array('status' => 1));
        $new_rp = PlayerReport::where('id',$rp)->first();

        $old_rp = PlayerReport::where('course_id',$new_rp->course_id)->where('season_id',$new_rp->season_id)->where('coach_id',$new_rp->coach_id)->where('player_id',$new_rp->player_id)->where('type',$new_rp->type)->orderBy('id','asc')->first();
        $old_rp->delete();

        return \Redirect::back()->with('success','Report overriden successfully.'); 
    }
    else{

        if(count($check_report)>0)
        {
            $report = new PlayerReport; 
            $report->coach_id = \Auth::user()->id;
            $report->season_id = $request->season_id;
            $report->player_id = $request->player_id;
            $report->course_id = $request->course_id;
            $report->type = $request->rp_type;
            $report->date = $date;
            $report->term = isset($request->term) ? $request->term : '';
            $report->feedback = isset($request->feedback) ? $request->feedback : '';
            $report->selected_options = isset($request->selected_options) ? json_encode($request->selected_options) : '';
            $report->status = 0;
            $report->save();

            $override_url = url()->current().'?rp='.$report->id;
            
            return \Redirect::back()->with('error','This report has already been submitted to the player. If you submit again, previous save report data will be overridden.<a href="'.$override_url.'"> <b> <u>OVERRIDE</u></b></a>'); 
        }else{

            $date = Carbon::now();

                $report = new PlayerReport; 
                $report->coach_id = \Auth::user()->id;
                $report->season_id = $request->season_id;
                $report->player_id = $request->player_id;
                $report->course_id = $request->course_id;
                $report->type = $request->rp_type;
                $report->date = $date;
                $report->term = isset($request->term) ? $request->term : '';
                $report->feedback = isset($request->feedback) ? $request->feedback : '';
                $report->selected_options = isset($request->selected_options) ? json_encode($request->selected_options) : '';
                $report->status = 1;
                $report->save();

            return redirect('/user/coach-reports')->with('success','Report generated successfully for '.getUsername($request->player_id).' under '.getCourseName($request->course_id).' course'); 
            
        }
    }
  }else{
    return redirect('/user/coach-reports')->with('error','Season, course & player are required fields & you missed one of those fields.'); 
  }
} 


/*---------------------------------
|   Save Complex Report
|---------------------------------*/
public function save_complex_report(Request $request)
{
    $date = Carbon::now();  
    $current_date = $date->toDateTimeString();

    if(!empty($request->report_id))
    {
      $report = PlayerReport::find($request->report_id);
      $report->coach_id = \Auth::user()->id;
      $report->player_id = $request->exist_player_id; 
      $report->type = $request->type;
      $report->date = $current_date;
      $report->feedback = isset($request->feedback) ? $request->feedback : '';
      $report->selected_options = isset($request->selected_options) ? json_encode($request->selected_options) : '';

      $report->save();

      return redirect('/user/coach-reports')->with('success','Report updated successfully for '.getUsername($request->exist_player_id)); 

    }else{

      $report = new PlayerReport; 
      $report->coach_id = \Auth::user()->id;
      $report->player_id = $request->player_id;
      $report->type = $request->type;
      $report->date = $current_date;
      $report->feedback = isset($request->feedback) ? $request->feedback : '';
      $report->selected_options = isset($request->selected_options) ? json_encode($request->selected_options) : '';  
      $report->save();

      return redirect('/user/coach-reports')->with('success','Report generated successfully for '.getUsername($request->player_id)); 
    } 
}

/*-------------------------------------
|   Report Popup - Simple
|-------------------------------------*/
public function sim_report_popup(Request $request)
{
    $player_id = $request->player_id;
    $report_type = $request->report_type;

    if(!empty($player_id))
    {
        $user = User::where('id',$player_id)->first();
    }else{
        $user = '';
    }

    $player_name = isset($user->name) ? $user->name : '';
    $player_dob = isset($user->date_of_birth) ? date("d/m/Y", strtotime($user->date_of_birth)) : ''; 

    $data = array(
        'player_name' => $player_name,
        'player_dob'  => $player_dob,
    );

    echo json_encode($data);   
}

/*-------------------------------------
|   Report Popup - Complex
|-------------------------------------*/
public function report_popup(Request $request)
{
    $exist_player_id = $request->exist_player_id;
    $player_id = $request->player_id;
    $report_type = $request->report_type;

    if(!empty($exist_player_id))
    {
        $user = User::where('id',$exist_player_id)->first();
    }
    elseif(!empty($player_id))
    {
        $user = User::where('id',$player_id)->first();
    }else{
        $user = '';
    }

    $player_name = isset($user->name) ? $user->name : '';
    $player_dob = isset($user->date_of_birth) ? date("d/m/Y", strtotime($user->date_of_birth)) : ''; 

    $data = array(
        'player_name' => $player_name,
        'player_dob'  => $player_dob,
    );

    echo json_encode($data);   
}

/*----------------------------------------------------------
|   Get listing of players on the basis of purchased course
|----------------------------------------------------------*/
public function get_player_from_course($course_id)
{
    $shop = ShopCartItems::where('shop_type','course')->where('product_id',$course_id)->get();

    if(count($shop) > 0)
    {
      $output = '<option value="" selected disabled>Select Player</option>';

      foreach($shop as $sh)
      {
        $output .= '<option value="'.$sh->child_id.'">'.getUsername($sh->child_id).'</option>';
      }
    }else{
        $output = '<option value="">No data exists</option>';
    }

    $data = array(
        'option'   => $output,
    );

    echo json_encode($data);
    
} 

/*----------------------------------------------------------
|   Get listing of courses on the basis of selected season
|----------------------------------------------------------*/
public function get_course_from_season($season_id)
{
    $course = \DB::table('courses')
            ->leftjoin('shop_cart_items', 'courses.id', '=', 'shop_cart_items.product_id')
            ->select('courses.*', 'shop_cart_items.product_id')
            ->where('shop_cart_items.shop_type','course')
            ->where('courses.linked_coach',Auth::user()->id)
            ->where('season',$season_id)
            ->groupBy('shop_cart_items.product_id')
            ->get(); 

    // $course = Course::where('season',$season_id)->get();

    if(count($course) > 0)
    {
      $output = '<option value="" selected disabled>Select Course</option>';

      foreach($course as $sh)
      {
        $output .= '<option value="'.$sh->id.'">'.getCourseName($sh->id).'</option>';
      }
    }else{
        $output = '<option value="">No data exists</option>';
    }

    //dd($output);

    $data = array(
        'option'   => $output,
    );

    echo json_encode($data);
    
} 

/*-------------------------------------------
|   Coach - Upload Invoice PDF - Start Here
|--------------------------------------------*/
public function upload_inv_index() 
{
  $req = CoachUploadPdf::where('coach_id',Auth::user()->id)->get();
  return view('coach.upload-invoice.index',compact('req'));
}

public function upload_inv_accept() 
{
  $req = CoachUploadPdf::where('coach_id',Auth::user()->id)->where('status',1)->get();
  return view('coach.upload-invoice.index',compact('req'));
}

public function upload_inv_not_approve() 
{
  $req = CoachUploadPdf::where('coach_id',Auth::user()->id)->where('status',0)->get(); 
  return view('coach.upload-invoice.index',compact('req'));
}

public function upload_inv_pending() 
{
  $req = CoachUploadPdf::where('coach_id',Auth::user()->id)->where('status',2)->get();
  return view('coach.upload-invoice.index',compact('req'));
}

public function upload_inv_add() 
{
  $logined_user = \Auth::user()->id;
  return view('coach.upload-invoice.add',compact('logined_user'));
}

public function upload_inv_save(Request $request) 
{
  $this->validate($request,[
      'invoice_name' => 'required|max:255',
      'invoice_document' => "required|mimetypes:application/pdf"
  ]);

  if(!empty($request->invoice_document))
  {
      $filename = $request->invoice_document;
      if ($request->hasFile('invoice_document')) {
          $invoice_document = $request->file('invoice_document');
          $filename = time().'.'.$invoice_document->getClientOriginalExtension();  
          $destinationPath = public_path('/uploads');
          $invoice_document_path = public_path().'/uploads/'.$request->invoice_document;
          $invoice_document->move($destinationPath, $filename);
      }
  }

    $acc = new CoachUploadPdf;
    $acc->coach_id = $request->coach_id;
    $acc->invoice_name = isset($request->invoice_name) ? $request->invoice_name : '';
    $acc->status = 2;

    if(!empty($request->invoice_document))
    {
      $acc->invoice_document = isset($filename) ? $filename : '';
    }
    $acc->save();

  return redirect('user/upload-invoice')->with('success','Invoice PDF uploaded successfully.');
}


/*----------------------------------------
|   Coach - Upload Invoice PDF - End Here
|-----------------------------------------*/



/*--------------------------------
|   Delete User Record
|--------------------------------*/
public function delete_coach_document($id) {
    $user = CoachDocument::find($id);
    $user->delete();
    return \Redirect::back()->with('flash_message',' Document has been deleted successfully!');
}

/*--------------------------------
|   Save Qualifications
|---------------------------------*/
public function save_qualifications(Request $request) 
{
  $data = $request->all();  
  $user = \Auth::user()->id;
 
    if($request->hasFile('upload_document'))
    {
        foreach ($data['document_name'] as $number => $value){    
        
        $string = str_random(5);
        $image[$number] = request()->upload_document[$number];  
        $filename[$number] = time().$string.'.'.$image[$number]->getClientOriginalExtension();
        $destinationPath[$number] = public_path('/uploads/coach-document');  
        $image[$number]->move($destinationPath[$number], $filename[$number]);

          $co                  =  CoachDocument::findOrNew($user);
          $co->coach_id        =  $user;  
          $co['document_name'] =  isset($data['document_name'][$number]) ? $data['document_name'][$number] : '' ;
          $co['document_type'] =  isset($data['document_type'][$number]) ? $data['document_type'][$number] : '';
          $co['expiry_date']   =  isset($data['expiry_date'][$number]) ? $data['expiry_date'][$number] : '';
          $co['notification']   =  isset($data['notification'][$number]) ? $data['notification'][$number] : ''; 
          $co['upload_document'] = isset($filename[$number]) ? $filename[$number] : '';
          $co->save(); 

        }
    }

  return \Redirect::back()->with('flash_message','Document uploaded successfully. Please wait for admin approval.');
}

/*--------------------------------
|   Coach Qualifications
|---------------------------------*/
public function coach_player() 
{
  $player = ParentCoachReq::where('coach_id',Auth::user()->id)->where('status',1)->get();
  return view('coach.player',compact('player'));
}

/*----------------------------------------
|   Delete Family Member
|----------------------------------------*/
public function delete_family_member($id) {
    $acc = User::find($id);
    $acc->delete();
    
    $user = User::where('id',\Auth::user()->id)->first(); 
    $children = User::where('role_id',4)->where('parent_id', '=', \Auth::user()->id)->get(); 
    return view('cms.my-family.my-family',compact('children','user'))->with('success','Family Member has been deleted successfully!');
}

/* My Family Page */
public function my_family() {

    $logined_user = \Auth::user()->id;
    $user = User::where('id',$logined_user)->first(); 
    $children = User::where('role_id',4)->where('parent_id', '=', $logined_user)->get(); 
    return view('cms.my-family.my-family',compact('children','user'));
} 

/* Add Family Member */
public function add_family_member() {
    $activities = ChildActivity::orderBy('id','asc')->get();  
    return view('cms.my-family.add-family-member',compact('activities'));
} 

/* Copy Address */
public function copy_address() {
    $id = \Auth::user()->id;
    $user = User::where('id',$id)->first();
    $address = $user['address'];
    $town = $user['town'];
    $postcode = $user['postcode'];
    $county = $user['county'];
    $country = $user['country'];

    $data = array(
        'address'   => $address,
        'town'      => $town,
        'postcode'  => $postcode,
        'county'    => $county,
        'country'   => $country,
    );

    echo json_encode($data);
} 

/* medical_info_to_next */
public function medical_info_to_next(Request $request) {

$child_id = $request->child_id; 
$user_id = $request->user_id;

// $filename = $request->profile_image;
//   if ($request->hasFile('profile_image')) {
//       $profile_image = $request->file('profile_image');
//       $filename = time().'.'.$image->getClientOriginalExtension();  
//       $destinationPath = public_path('/uploads');
//       $img_path = public_path().'/uploads/'.$request->profile_image;
//       $profile_image->move($destinationPath, $filename);
//   }

if(!empty($request->user_id))
  {
    $add_family               =    User::find($user_id);
    $add_family->name         =    $request->first_name.' '.$request->last_name;
    $add_family->first_name   =    $request->first_name;
    $add_family->last_name    =    $request->last_name;
    $add_family->gender       =    $request->gender;
    $add_family->date_of_birth=    $request->date_of_birth;
    $add_family->address      =    $request->address;
    $add_family->town         =    $request->town;
    $add_family->postcode     =    $request->postcode;
    $add_family->county       =    $request->county;
    $add_family->country      =    $request->country;
    $add_family->parent_id    =    \Auth::user()->id; 
    $add_family->relation     =    $request->relation;
    $add_family->type         =    $request->form_type;
    $add_family->book_person  =    $request->book_person;
    // $add_family->filename     =    isset($filename) ? $filename : '';
    $add_family->tennis_club  =    isset($request->tennis_club) ? $request->tennis_club : '';
    $add_family->save(); 
  }else{
    $add_family               =    new User;
    $add_family->role_id      =    $request->role_id;
    $add_family->name         =    $request->first_name.' '.$request->last_name;
    $add_family->first_name   =    $request->first_name;
    $add_family->last_name    =    $request->last_name;
    $add_family->gender       =    $request->gender;
    $add_family->date_of_birth=    $request->date_of_birth;
    $add_family->address      =    $request->address;
    $add_family->town         =    $request->town;
    $add_family->postcode     =    $request->postcode;
    $add_family->county       =    $request->county;
    $add_family->country      =    $request->country;
    $add_family->parent_id    =    \Auth::user()->id; 
    $add_family->relation     =    $request->relation;
    $add_family->type         =    $request->form_type;
    $add_family->book_person  =    $request->book_person;
    // $add_family->filename     =    isset($filename) ? $filename : '';
    $add_family->tennis_club  =    isset($request->tennis_club) ? $request->tennis_club : '';
    $add_family->email_verified_at = '';
    $add_family->save(); 
}
    if(!empty($child_id)){
      $mem_detail = ChildrenDetail::find($child_id); 
      $mem_detail->core_lang = isset($request->core_lang) ? $request->core_lang : '';
      $mem_detail->primary_language = isset($request->primary_language) ? $request->primary_language : '';
      $mem_detail->school = isset($request->school) ? $request->school : '';
      $mem_detail->preferences = isset($request->preferences) ? $request->preferences : '';

      $mem_detail->beh_need = isset($request->beh_need) ? $request->beh_need : '';
      $mem_detail->beh_info = isset($request->beh_info) ? $request->beh_info : '';
      $mem_detail->em_first_name = isset($request->em_first_name) ? $request->em_first_name : '';
      $mem_detail->em_last_name = isset($request->em_last_name) ? $request->em_last_name : '';
      $mem_detail->em_phone = isset($request->em_phone) ? $request->em_phone : '';
      $mem_detail->em_email = isset($request->em_email) ? $request->em_email : '';
      $mem_detail->correct_info = isset($request->correct_info) ? $request->correct_info : '';
      $mem_detail->save();

    }else{
      $mem_detail = ChildrenDetail::create($request->all()); 
      $mem_detail->parent_id = $add_family->parent_id;
      $mem_detail->child_id = $add_family->id;
      $mem_detail->save();
    }
      

      $data = array(
          'mem_type'        => $add_family->type,
          'mem_detail_id'   => $mem_detail->id,
      );

      return response()->json($data);

} 

/* child_cont_to_next */ 
public function child_cont_to_next(Request $request) {  //dd($request->all());

      $child_id = $request->child_id;

      if(!empty($child_id))
      {
          $mem_detail                   =    ChildrenDetail::find($child_id);
          $mem_detail->con_first_name   =    $request->con_first_name; 
          $mem_detail->con_last_name    =    $request->con_last_name;
          $mem_detail->con_phone        =    $request->con_phone;
          $mem_detail->con_email        =    $request->con_email;
          $mem_detail->con_relation     =    $request->con_relation;
          $mem_detail->con_if_other     =    $request->con_if_other;
          $mem_detail->save();
      }else{
          $mem_detail                   =    ChildrenDetail::find($request->mem_id);
          $mem_detail->con_first_name   =    $request->con_first_name; 
          $mem_detail->con_last_name    =    $request->con_last_name;
          $mem_detail->con_phone        =    $request->con_phone;
          $mem_detail->con_email        =    $request->con_email;
          $mem_detail->con_relation     =    $request->con_relation;
          $mem_detail->con_if_other     =    $request->con_if_other;
          $mem_detail->save();
      }

      $data = array(
          'mem_detail_id'   => $mem_detail->id,
      );

      echo json_encode($data);
}

/* med_beh_to_next */ 
public function med_beh_to_next(Request $request) {

    $child_id = $request->child_id;

    if(!empty($child_id))
    {
      $mem_detail                       =    ChildrenDetail::find($child_id);
      $mem_detail->med_cond             =    $request->med_cond; 
      $mem_detail->med_cond_info        =    $request->med_cond_info;
      $mem_detail->allergies            =    $request->allergies;
      $mem_detail->allergies_info       =    $request->allergies_info;
      $mem_detail->pres_med             =    $request->pres_med;
      $mem_detail->pres_med_info        =    $request->pres_med_info;
      $mem_detail->med_req              =    $request->med_req; 
      $mem_detail->med_req_info         =    $request->med_req_info;
      $mem_detail->allergies            =    $request->allergies;
      $mem_detail->allergies_info       =    $request->allergies_info;
      $mem_detail->toilet               =    $request->toilet;
      $mem_detail->special_needs        =    $request->special_needs;
      $mem_detail->special_needs_info   =    $request->special_needs_info;
      $mem_detail->situation            =    $request->situation;
      $mem_detail->save();
    }else{
      $mem_detail                       =    ChildrenDetail::find($request->mem_id);
      $mem_detail->med_cond             =    $request->med_cond; 
      $mem_detail->med_cond_info        =    $request->med_cond_info;
      $mem_detail->allergies            =    $request->allergies;
      $mem_detail->allergies_info       =    $request->allergies_info;
      $mem_detail->pres_med             =    $request->pres_med;
      $mem_detail->pres_med_info        =    $request->pres_med_info;
      $mem_detail->med_req              =    $request->med_req; 
      $mem_detail->med_req_info         =    $request->med_req_info;
      $mem_detail->allergies            =    $request->allergies;
      $mem_detail->allergies_info       =    $request->allergies_info;
      $mem_detail->toilet               =    $request->toilet;
      $mem_detail->special_needs        =    $request->special_needs;
      $mem_detail->special_needs_info   =    $request->special_needs_info;
      $mem_detail->situation            =    $request->situation;
      $mem_detail->save();
    }
      $data = array(
          'mem_detail_id'   => $mem_detail->id,
      );

      echo json_encode($data);
}

/* Complete Registration - Family Member */
public function complete_registration(Request $request) 
{
  $child_id = $request->child_id; 

    if(!empty($child_id))
    {
      $mem_detail             =    ChildrenDetail::find($child_id);
      $mem_detail->media      =    $request->media; 
      $mem_detail->confirm    =    $request->confirm;
      $mem_detail->save();
    }else{
      $mem_detail             =    ChildrenDetail::find($request->mem_id);
      $mem_detail->media      =    $request->media; 
      $mem_detail->confirm    =    $request->confirm;
      $mem_detail->save();
    }
      $data = array(
          'confirm'   => $mem_detail->confirm,
      );

      return response()->json($data);
}

/* Edit Family Member */
public function edit_family_member($id) {
    $user_id = base64_decode($id);
    $user = User::where('id',$user_id)->where('role_id',4)->first(); 
    $activities = ChildActivity::orderBy('id','asc')->get(); 
    return view('cms.my-family.edit-family-member')->with('user',$user)->with('activities',$activities);
} 

/* Update Family Member */
public function update_family_member(Request $request) 
{
    $family               =    User::find($request->user_id);
    $family->role_id      =    $request->role_id;
    $family->name         =    $request->first_name.' '.$request->last_name;
    $family->first_name   =    $request->first_name;
    $family->last_name    =    $request->last_name;
    $family->gender       =    $request->gender;
    $family->date_of_birth=    $request->date_of_birth;
    $family->address      =    $request->address;
    $family->town         =    $request->town;
    $family->postcode     =    $request->postcode;
    $family->county       =    $request->county;
    $family->country      =    $request->country;
    $family->relation     =    $request->relation;
    $family->save();

    if($request->form_type == 'child')
    {
      ChildrenDetail::where('child_id',$family->id)->delete();
      $child = ChildrenDetail::create($request->all()); 
      $child->parent_id = $family->parent_id;
      $child->child_id = $family->id;
      $child->save();
    }

    if($request->form_type == 'child')
    {
      return redirect('user/my-family')->with('success','Child Details updated successfully.');
    }else{
      return redirect('user/my-family')->with('success','Adult Details updated successfully.');
    }
    
} 

/* Parent Notifications */
public function parent_notifications(){
  $req = ParentCoachReq::where('parent_id',Auth::user()->id)->where('dismiss_by_parent',NULL)->get();
  return view('cms.my-family.notifications',compact('req'));
} 

/* Coach Listing Page */
public function coach_listing(){
  $coach = User::where('role_id',3)->where('updated_status', 1)->get();
  return view('cms.my-family.coach-listing',compact('coach'));
}

/* Coach Detail Page */ 
public function coach_detail($id){
  $coach_id = base64_decode($id);
  $coach = User::where('id',$coach_id)->first();
  return view('cms.my-family.coach-detail',compact('coach'));
}

/* Reject status */
public function reject_request(Request $request)
{
  //dd($request->all());
  $id = $request->request_id;
  $req = ParentCoachReq::find($id);
  $req->status = 2;
  $req->reason_of_rejection = $request->reason_of_rejection;
  $req->save();

  return \Redirect::back()->with('success','Parent request has been rejected successfully!');
} 

/* Undo rejection status */
public function undo_reject_request(Request $request){
  
  $id = $request->id;
  $req = ParentCoachReq::find($id);
  $req->status = 0;
  $req->reason_of_rejection = NULL;
  $req->save();

  return \Redirect::back()->with('success','Action undo successfully!');
} 

/* Parent request status */
public function parent_req_status(Request $request)
{
  $req = ParentCoachReq::where('coach_id',Auth::user()->id)->where('child_id',$request->child_id)->where('parent_id',$request->parent_id)->first();
  
  $req_data = ParentCoachReq::find($req->id);
  $req_data->status = $request->status;
  $req_data->save();

  $parent_id = $request->parent_id;
  $user = User::where('id',$parent_id)->first();
  $parent_name = $user['name'];
  $parent_email = $user['email'];

  $user1 = User::where('id',Auth::user()->id)->first();
  $coach_name = $user1['name'];
  $coach_email = $user1['email'];

  $status = ($req_data->status == 1) ? 'Accepted' : 'Rejected';

  // Mail to parent
    \Mail::send('emails.coach.parent-request', ['parent_name' => $parent_name,'parent_email' => $parent_email,'coach_name' => $coach_name,'coach_email' => $coach_email,'status'=>$status] , 
             function($message) use($parent_email){
                 $message->to($parent_email);
                 $message->subject('Subject : '.'Request to Coach');
               });

  $data = array(
      'output'   => $req_data,
    );

    return response()->json($req_data);
}

/* Unlink Coach */
public function unlink_coach(Request $request)
{
  ParentCoachReq::where('id',$request->id)->delete();
  return \Redirect::back()->with('success',' Coach has been unlinked successfully!');
} 

/* My Bookings Page */
public function my_bookings(){
  $logined_user_id = \Auth::user()->id; 
  $shop = \DB::table('shop_orders')->where('user_id',$logined_user_id)->orderBy('id','desc')->get();
  return view('cms.my-family.my-booking',compact('shop'));
} 

/* Booking Detail Page */
public function booking_detail($id){
  $user_id = base64_decode($id); 
  $order = \DB::table('shop_orders')->where('id',$user_id)->orderBy('id','asc')->first();
  return view('cms.my-family.booking-detail',compact('order'));
} 

/* Reports Page - Parent Dashboard*/
public function player_report_listing(){
  $logined_user_id = \Auth::user()->id; 
  $children = User::where('parent_id',$logined_user_id)->orderBy('id','asc')->get();
  return view('cms.my-family.player-report-listing',compact('children'));
} 

/* My Bookings Page */
public function player_report_detail($id){
  $id = base64_decode($id); 
  $report = PlayerReport::where('id',$id)->orderBy('id','asc')->first();
  return view('cms.my-family.player-report-detail')->with('report',$report);
} 

/* Cancel Booking */
public function cancel_booking($id) {
    $booking = ShopOrder::find($id); 
    $booking->status = 2;
    $booking->save();

    return \Redirect::back()->with('success',' Booking has been cancelled successfully!');
}

/*-----------------------------------------------
|   Order PDF
|-----------------------------------------------*/
function get_order_data($order_id)
{
    // Get Invoice notes
    $orders = \DB::table('shop_orders')->where('id',$order_id)->orderBy('id','DESC')->first(); 
    return $orders;
}

function order_pdf($order_id)
{
    $orderID = base64_decode($order_id);
    $pdf = \App::make('dompdf.wrapper'); 
    $pdf->loadHTML($this->convert_order_data_to_html($orderID));
    return $pdf->stream();
}

function convert_order_data_to_html($order_id)
{
    $orders = $this->get_order_data($order_id); 
    $extra = getAllValueWithMeta('service_fee_amount', 'global-settings');
    $order_price = $orders->amount - $extra;

    $shipping_address = json_decode($orders->shipping_address);  
    $billing_address = json_decode($orders->billing_address); 
    $user_id = $orders->user_id;
    $user_details = User::where('id',$user_id)->first();

    if(!empty($orders->provider_id))
    {
      $provider = ChildcareVoucher::where('id',$orders->provider_id)->first();
    }
    $provider_name = isset($provider->provider_name) ? $provider->provider_name : '';
    
    $output = '<title>INVOICE</title> 
    <style>
    @page {
          margin: 0.5cm;
          }
</style>
        <table width="100%" style="border-collapse:collapse;">
       
        <tbody>
        <tr>
            <td colspan="4">
                <table style="table-layout: fixed;width: 100%;border-collapse: collapse;"">

                        <tr>
                            <td  align="center"><img src="http://49.249.236.30:8654/dominic-new/public/uploads/1584078701website_logo.png" width="130px;" style="margin-bottom: 15px;">
                            </td>
                        </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <table style="table-layout: fixed;width: 100%;border-collapse: collapse;border:1px solid #011c49;margin-bottom: 20px;"> 
                    <tbody>
                        <tr>
                            <td colspan="2" style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Order Details</td>
                        </tr>
                        <tr>
                            <td>
                                <p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Order ID : </strong>'.$orders->orderID.' </p>
                            </td>
                            <td>
                                <p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Order Date : </strong>'.$orders->created_at.' </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Order Amount : </strong>£'.$order_price.' </p>
                            </td>
                            <td>
                                <p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Payment Method : </strong>'.$orders->payment_by.' </p>
                            </td>
                        </tr>
                        <tr>
                            <td>';
                        if(!empty($provider_name)){
                            $output .= '<p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Provider Name : </strong>'.$provider_name.' </p>';
                        }

                        if(!empty($orders->transaction_details))
                        {
                            $transaction_details = explode(',',$orders->transaction_details); 
                            $tr_data = [];

                            foreach($transaction_details as $tr)
                            {
                                $tr1 = explode('- ',$tr); 
                                $tr0 = $tr1[0];
                                $tr1 = '&pound;'.$tr1[1];   
                                 $tr_data[] = $tr0.'- '.$tr1;
                            }
                            $payment_details = implode(',', $tr_data); 

                            $output .= '<p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Payment Details : </strong>'.$payment_details.' </p>';
                        }

                        $output .= '</td>
                            <td>
                                <p style="padding: 0 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"></p>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </td>
        </tr> 
            <tr>
                <td colspan="4">
                    <table style="table-layout: fixed;width: 100%;border-collapse: collapse;border:1px solid #011c49;margin-bottom: 20px;"> 
                        <tbody>
                            <tr>
                                <td colspan="3" style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">User Details</td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="padding: 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Name : </strong>'.$user_details->name.' </p>
                                </td>
                                <td>
                                    <p style="padding: 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Email : </strong>'.$user_details->email.' </p>
                                </td>
                                <td>
                                    <p style="padding: 10px;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Phone No. : </strong>'.$user_details->phone_number.' </p>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </td>
            </tr>';

            $order_ID = $orders->orderID;
            $cart_items = \DB::table('shop_cart_items')->where('orderID',$order_ID)->get();
            $shop_type = array(); 
            foreach($cart_items as $sh){
                $shop_type[] = $sh->shop_type;
            }

            if (in_array('product', $shop_type, TRUE)){ 

            $output .= '<tr>
                <td colspan="4">
                    <table style="table-layout: fixed;width: 100%;border-collapse: collapse;border:1px solid #011c49;margin-bottom: 20px;">
                        <tbody>
                            <tr>
                                <td style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Shipping Address</td>
                                <td style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Billing Address</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #011c49;">
                                    <p style="padding:15px 10px 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Name : </strong>'.$shipping_address->name.'</p>
                                    <p style="padding: 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Email : </strong>'.$shipping_address->email.'</p>
                                    <p style="padding: 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Phone : Number</strong>'.$shipping_address->phone_number.'</p>
                                    <p style="padding: 5px 10px 25px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Address : </strong>'.$shipping_address->address.', '.$shipping_address->country.', '.$shipping_address->state.', '.$shipping_address->city.', Zipcode- '.$shipping_address->zipcode.'</p>
                                </td>
                                <td style="border:1px solid #011c49;">
                                    <p style="padding:15px 10px 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Name : </strong>'.$billing_address->name.'</p>
                                    <p style="padding: 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Email : </strong>'.$billing_address->email.'</p>
                                    <p style="padding: 5px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Phone : Number</strong>'.$billing_address->phone_number.'</p>
                                    <p style="padding:  5px 10px 25px 10px;margin:0;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"><strong>Address : </strong>'.$billing_address->address.', '.$billing_address->country.', '.$billing_address->country.', '.$billing_address->state.', Zipcode- '.$billing_address->zipcode.'</p>
                                </td>
                            </tr>
                        </tbody> 
                    </table>
                </td>
            </tr>';

            }

            $output .= '<tr>
                <td colspan="4">
                    <table style="table-layout: fixed;width: 100%;border-collapse: collapse;border:1px solid #011c49;">
                        <tbody>
                            <tr>
                                <td style="background-color: #3f4d67;width:16%;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Order Type</td>
                                <td style="background-color: #3f4d67;width:28%;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Item Purchased</td>
                                <td style="background-color: #3f4d67;width:18%;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Participant</td>
                                <td style="background-color: #3f4d67;width:28%;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Details</td>
                                <td style="background-color: #3f4d67;width:10%;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Price</td>
                            </tr>';

                            $orderID = $orders->orderID;
                            $cart = \DB::table('shop_cart_items')->where('orderID',$orderID)->get();

                            foreach($cart as $ca){ 
                            if($ca->shop_type == 'product')
                            {
                                $product = \DB::table('products')->where('id',$ca->product_id)->first();

                                $variation = \App\Models\Products\ProductAssignedVariation::find($ca->variant_id);

                            $output .= '<tr>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$ca->shop_type.'</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$product->name.'</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;"></td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">';

                                if($product->product_type == 1)
                                {
                                    foreach($variation->hasVariationAttributes as $v)
                                    {
                                        $output .= $v->parentVariation->variations->name.': 
                                              <b class="bText">'.$v->parentVariation->name.'</b><br/>';
                                    }
                                }

                            $output .= '</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">&pound;'.$ca->total.'</td>
                            </tr>';

                            }
                            elseif($ca->shop_type == 'course')
                            {
                                $child_id = $ca->child_id;
                                $user = \DB::table('users')->where('id',$child_id)->first();
                                $course = \DB::table('courses')->where('id',$ca->product_id)->first();

                            $output .= '<tr>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$ca->shop_type.'</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$course->title.'</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$user->name.'</td>
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$course->term;   

                            $output .= '<td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">&pound;'.$ca->total.'</td>
                            </tr>';
                            }
                            elseif($ca->shop_type == 'camp')
                            {
                                $child_id = $ca->child_id;
                                $user = \DB::table('users')->where('id',$child_id)->first();
                                $camp = \DB::table('camps')->where('id',$ca->product_id)->first();
                                $week = json_decode($ca->week);

                            $output .= '<tr><td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$ca->shop_type.'</td>
                                <td style="padding:  10px;color: #000;border-bottom:1px solid #3f4d67;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$camp->title.'</td>
                                <td style="padding:  10px;color: #000;border-bottom:1px solid #3f4d67;font-size: 15px;font-family: "Open Sans", sans-serif;">'.$user->name.'</td>';

                            $output .='<td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">';   

                            foreach($week as $number=>$number_array)
                            {
                                foreach($number_array as $data=>$user_data)
                                {
                                  foreach($user_data as $data1=>$user_data1){
                                   
                                      $split = explode('-',$user_data1);
                                      $get_session = $split[2];
                                    
                                    if($get_session == 'early'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Early Drop Off<br/>';
                                    }
                                    elseif($get_session == 'mor'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Morning<br/>';
                                    }
                                    elseif($get_session == 'noon'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Afternoon<br/>';
                                    }
                                    elseif($get_session == 'lunch'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Lunch Club<br/>';
                                    }
                                    elseif($get_session == 'late'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Late Pickup<br/>';
                                    }
                                    elseif($get_session == 'full'){
                                    $output .= '<p>'.$number.' - '.$data1.' - Full Day<br/>';
                                    }
                                    
                                  }
                                
                                }

                              }

                            $output .= '
                                <td style="padding:  10px;border-bottom:1px solid #3f4d67;color: #000;font-size: 15px;font-family: "Open Sans", sans-serif;">&pound;'.$ca->total.'</td>
                            </tr>';
                            }
                        }

                        $output .= '</tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td></td>
                <td></td> 
                <td colspan="2">
                    <table style="table-layout: fixed;width:100%; border-collapse: collapse;border:1px solid #011c49; margin-top: 20px; ">
                        <tbody>
                            <tr>
                                <td style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Cart Subtotal</td>
                                <td style="padding:  10px;color: #000;font-size: 17px;font-family: "Open Sans", sans-serif;">£ '.$order_price.'</td>
                            </tr>';

                            if($extra > 0){
                            $output .= '<tr>
                                <td style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 17px;font-family: "Open Sans", sans-serif;">Service Fee</td>
                                <td style="padding:  10px;color: #000;font-size: 17px;font-family: "Open Sans", sans-serif;">+ &pound;'.$extra.'</td>
                            </tr>';
                        }
                            $output .= '<tr>
                                <td style="background-color: #3f4d67;padding: 10px;color: #fff;font-size: 19px;font-family: "Open Sans", sans-serif;"> Order Total </td>
                                <td style="padding:  10px;color: #000;font-size: 19px;font-family: "Open Sans", sans-serif;"> £ <strong>'.$orders->amount.'</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>';
 
        $output .= '</table>';
        return $output;
    '</div>';
}


/********************************************
|
|   Camp Management - Start Here
|
|********************************************/

/* Goals Template Page */
public function set_goals()
{
    return view('cms.setgoals');
}

/* Badges Page */
public function badges()
{
    $user_id = request()->get('user_id');
    $season_id = request()->get('season_id');
    $course_id = request()->get('course_id');

    //dd($user_id,$season_id,$course_id);

    $term = request()->get('term');
    $stage = request()->get('stage');

    //dd($term,$stage);

    if(!empty($user_id) && !empty($season_id)){
      $user_badge = \DB::table('user_badges')->where('user_id',$user_id)->where('season_id',$season_id)->first();
      $shop = \DB::table('shop_cart_items')->where('shop_type','course')->where('product_id',$course_id)->where('child_id',$user_id)->where('orderID','!=',NULL)->where('order_id','!=',NULL)->orderBy('id','asc')->first();
    }
    elseif(!empty($user_id))
    {
      $user_badge = \DB::table('user_badges')->where('user_id',$user_id)->first();
      $shop = \DB::table('shop_cart_items')->where('shop_type','course')->where('child_id',$user_id)->where('orderID','!=',NULL)->where('order_id','!=',NULL)->orderBy('id','asc')->first();
    }
    elseif(!empty($season_id))
    {
      $user_badge = \DB::table('user_badges')->where('season_id',$season_id)->first();
      $shop = \DB::table('shop_cart_items')->where('shop_type','course')->where('course_season',$season_id)->where('orderID','!=',NULL)->where('order_id','!=',NULL)->orderBy('id','asc')->first();
    }
    else
    {
      $user_badge = \DB::table('user_badges')->orderBy('user_id','desc')->first();
      $shop = \DB::table('shop_cart_items')->where('shop_type','course')->where('child_id','!=',NULL)->where('orderID','!=',NULL)->where('order_id','!=',NULL)->orderBy('id','asc')->first();
    }

    if(!empty($term) && empty($stage) || !empty($term) && $stage==NULL){
        $user_badge1 = \DB::table('user_badges')->where('season_id',$term)->paginate(1);
    }
    elseif(!empty($term) && !empty($stage)){
        $course = Course::where('season',$term)->where('subtype',$stage)->first();

        if(!empty($course))
        {
            $shop_data = ShopCartItems::where('course_season',$course->season)->where('shop_type','course')->where('product_id',$course->id)->where('orderID','!=',NULL)->first();

            // dd($shop_data);

            if(!empty($shop_data))
            {
              $child_id = $shop_data->child_id;  
              $user_badge1 = \DB::table('user_badges')->where('user_id',$child_id)->where('season_id',$term)->paginate(10); 
            }else{
              $user_badge1 = '';
            }
            
        }else{
            $user_badge1 = '';
        }
    }
    elseif(!empty($stage) && empty($term)){
        $course = Course::where('subtype',$stage)->first();

        if(!empty($course))
        {
            $shop_data = ShopCartItems::where('course_season',$course->season)->where('shop_type','course')->where('product_id',$course->id)->where('orderID','!=',NULL)->first();

            $child_id = $shop_data->child_id;  

            $user_badge1 = \DB::table('user_badges')->where('user_id',$child_id)->paginate(10);
        }
        
    }else{
        $user_badge1 = \DB::table('user_badges')->paginate(10);
    }

    $purchase_course = \DB::table('shop_cart_items')->where('shop_type','course')->where('child_id','!=',NULL)->where('orderID','!=',NULL)->where('order_id','!=',NULL)->paginate(10);
    $testimonial = Testimonial::where('page_title','badges')->where('status',1)->get();

    $goal_player = request()->get('goal_player');
    $goal_type = request()->get('goal_type'); 

    if(!empty($goal_player) && !empty($goal_type))
    {
      $user_goal = SetGoal::where('player_id',$goal_player)->where('parent_id',Auth::user()->id)->get();
    }elseif(empty($goal_player) && empty($goal_type)){
      $user_goal = '';
    }

    return view('cms.badges',compact('purchase_course','testimonial','shop','user_id','course_id','season_id','user_badge','user_badge1','goal_player','goal_type','user_goal'));
}

/*----------------------------------------
|   Badges Filter
|-----------------------------------------*/
public function selectedSeason(Request $request)
{ 
    $season = $request->selectedSeason; 
    $shop = ShopCartItems::where('course_season',$request->selectedSeason)->where('orderID','!=',NULL)->get(); 

    if(count($shop) > 0)
    {
      // $output = '<option value="">All</option>';
      $output = '';

      foreach($shop as $sh)
      {
        $output .= '<option value="'.$sh->product_id.'">'.getCourseName($sh->product_id).'</option>';
      }
    }else{
        $output = '<option value="">No data exists</option>';
    }

    $data = array(
        'option'   => $output,
    );

    echo json_encode($data);
}

/*----------------------------------------
| BEGINNER GOALS - Save Goal Data
|----------------------------------------*/
public function save_goal(Request $request)
{
  $goal_data = $request->all(); 
  $check_goal = SetGoal::where('player_id',$request->goal_player_name)->where('goal_type',$request->pl_goal_type)->get();

  if(!empty($request->goal_player_name) && !empty($request->pl_goal_type))
  { 
    if(count($check_goal)>0)
    {
      if(!empty($request->goal))
      {
        foreach($request->goal as $key=>$goalValue)
        { 
          foreach($goalValue as $goaldata=>$value)
          {
              $get_coach_id = ParentCoachReq::where('child_id',$request->goal_player_name)->where('status',1)->first();

              $coach_id = isset($get_coach_id) ? $get_coach_id->coach_id : '';

              // dd($request->all(),$key,$value,$coach_id);
            if($value != null)
            {
              SetGoal::where('parent_id',$request->parent_id)->where('player_id',$request->goal_player_name)->where('goal_type',$request->pl_goal_type)->where('goal_id',$goaldata)->update(array('parent_comment' => $value, 'coach_id' => $coach_id));
            }
          }
          
        }
        return \Redirect::back()->with('success','Goal data updated successfully.');
      } 

    }else{
      if(!empty($request->goal))
      {
        foreach($request->goal as $key=>$goalValue)
        {
          foreach($goalValue as $goaldata=>$value)
          {
              $get_coach_id = ParentCoachReq::where('child_id',$request->goal_player_name)->where('status',1)->first();

              $set_goal = new SetGoal;
              $set_goal->goal_id = $goaldata;
              $set_goal->parent_comment = $value;
              $set_goal->player_id = $request->goal_player_name;
              $set_goal->goal_type = $request->pl_goal_type;
              $set_goal->parent_id = $request->parent_id;
              $set_goal->coach_id = isset($get_coach_id) ? $get_coach_id->coach_id : '';
              $set_goal->goal_date = date("d F Y",strtotime("tomorrow"));
              $set_goal->save();
          }
          
        }
        return \Redirect::back()->with('success','Goal data added successfully.');
      }
    }
  }else{
    return \Redirect::back()->with('error','Please select player.');
  } 
}

/*----------------------------------------------------
| BEGINNER GOALS - Add comment on goals - By Coach 
|-----------------------------------------------------*/
public function save_comment_by_coach(Request $request)
{
    foreach($request->goal as $key=>$goalValue)
    { 
      if($goalValue != null)
      {
        SetGoal::where('parent_id',$request->parent_id)->where('player_id',$request->goal_player_name)->where('goal_type',$request->pl_goal_type)->where('goal_id',$key)->update(array('coach_comment' => $goalValue)); 
      }
    }
    return \Redirect::back()->with('success','Comments added successfully.');
}

/*----------------------------------------
| ADVANCED GOALS - Save Goal Data
|----------------------------------------*/
public function advanced_goal(Request $request)
{
  $goal_data = $request->all(); 
  $check_goal = SetGoal::where('player_id',$request->goal_player_name)->where('goal_type','advanced')->where('goal_type',$request->pl_goal_type)->get();
  // dd($check_goal);

  if(!empty($request->goal_player_name) && !empty($request->pl_goal_type))
  { 
    if(count($check_goal)>0)
    {
      foreach($request->ad_goal as $key=>$goalValue)
      { 
        foreach($goalValue as $goaldata=>$value)
        {
          $get_coach_id = ParentCoachReq::where('child_id',$request->goal_player_name)->where('status',1)->first();

          $coach_id = isset($get_coach_id) ? $get_coach_id->coach_id : '';

          // dd($goaldata,$request->all(),$key,$value,$coach_id);
          if($value != null)
          {
            SetGoal::where('parent_id',$request->parent_id)->where('player_id',$request->goal_player_name)->where('goal_type',$request->pl_goal_type)->where('goal_id',$goaldata)->update(array('parent_comment' => $value, 'coach_id' => $coach_id));
          }
        }
      }
      return \Redirect::back()->with('success','Goal data updated successfully.');
    }
    else
    {
      if(!empty($request->ad_goal))
      {
        foreach($request->ad_goal as $key=>$goalValue)
        {
          foreach($goalValue as $goaldata=>$value)
          {
              $get_coach_id = ParentCoachReq::where('child_id',$request->goal_player_name)->where('status',1)->first();

              $datetime = new DateTime('tomorrow');
              $goal_date = $datetime->format('d F Y');

              $set_goal = new SetGoal;
              $set_goal->goal_id = $goaldata;
              $set_goal->parent_comment = $value;
              $set_goal->player_id = $request->goal_player_name;
              $set_goal->goal_type = $request->pl_goal_type;
              $set_goal->parent_id = $request->parent_id;
              $set_goal->advanced_type = $key;
              $set_goal->coach_id = isset($get_coach_id) ? $get_coach_id->coach_id : '';
              $set_goal->goal_date = date("d F Y",strtotime("tomorrow"));
              $set_goal->save();
          }
          
        }
        return \Redirect::back()->with('success','Goal data added successfully.');
      }
    }
  }else{
        return \Redirect::back()->with('error','Please select player.');
      }
}

/*----------------------------------------------------
| ADVANCED GOALS - Add comment on goals - By Coach 
|-----------------------------------------------------*/
public function save_ad_coach_comment(Request $request)
{
  foreach($request->coach_comment as $key=>$goalValue)
  { 
    SetGoal::where('parent_id',$request->parent_id)->where('player_id',$request->goal_player_name)->where('goal_type',$request->pl_goal_type)->where('advanced_type',$key)->update(array('coach_comment' => $goalValue)); 
  }
  return \Redirect::back()->with('success','Comments added successfully.');
}

/*----------------------------------------------------
| Coach Section - List of goals set by player's parent
|-----------------------------------------------------*/
public function goal_list()
{
  $goals = SetGoal::where('coach_id',Auth::user()->id)->groupBy(['player_id', 'goal_type'])->get();
  return view('coach.goals.goal-listing',compact('goals'));
}

/*---------------------------------------------
| Coach Section - Commenting on goals by coach
|---------------------------------------------*/
public function goal_detail($goal_type,$id)
{ 
  $get_goal = SetGoal::where('id',$id)->first();
  $goals_data = SetGoal::where('parent_id',$get_goal->parent_id)->where('player_id',$get_goal->player_id)->where('goal_type',$goal_type)->get();
  return view('coach.goals.goal-detail',compact('get_goal','goals_data'));
}

/*----------------------------------------
|   Update tennis club
|-----------------------------------------*/
public function update_tennis_club($tennis_club,$user_id,$shop_id) 
{   
    $ten_club = User::find($user_id);
    $ten_club->tennis_club = $tennis_club;
    $ten_club->save();

    $data = array(
        'sort_no'   => $ten_club,
    );

    echo json_encode($data);
}

/* Update User Profile */
public function update_user_profile(Request $request)
{
  if(!empty($request->image))
  {
      $filename = $request->image;
      if ($request->hasFile('image')) {
          $image = $request->file('image');
          $filename = time().'.'.$image->getClientOriginalExtension();  
          $destinationPath = public_path('/uploads');
          $img_path = public_path().'/uploads/'.$request->image;
          $image->move($destinationPath, $filename);
      }
  }

  $user = User::find($request->user_id);
  $user->profile_image = isset($filename) ? $filename : '';
  $user->save();

  return \Redirect::back()->with('success','Profile picture updated successfully.');
} 

/* Contact Us Page */
public function contact_us()
{
    return view('cms.contact-us');
}

/* Save contact form details */
public function save_contact_us(Request $request)
{
    $contact = ContactDetail::create($request->all()); 
    $contact->save;

    $participant_name = isset($contact->participant_name) ? $contact->participant_name : '';
    $participant_dob = isset($contact->participant_dob) ? $contact->participant_dob : '';
    $participant_gender = isset($contact->participant_gender) ? $contact->participant_gender : '';
    $parent_name = isset($contact->parent_name) ? $contact->parent_name : '';
    $parent_email = isset($contact->parent_email) ? $contact->parent_email : '';
    $parent_telephone = isset($contact->parent_telephone) ? $contact->parent_telephone : '';
    $class = isset($contact->class) ? $contact->class : '';
    $type = isset($contact->type) ? $contact->type : '';
    $subject = isset($contact->subject) ? $contact->subject : '';
    $contact_message = isset($contact->message) ? $contact->message : '';

    // Admin Email
    $admin_email = getAllValueWithMeta('admin_email', 'general-setting'); 

    // Mail to admin
    \Mail::send('emails.courses.admin', ['participant_name'=>$participant_name, 'participant_dob'=>$participant_dob, 'participant_gender'=>$participant_gender, 'parent_name'=>$parent_name, 'parent_email'=>$parent_email, 'parent_telephone'=>$parent_telephone, 'class'=>$class, 'type'=>$type, 'contact_message'=>$contact_message, 'subject'=>$subject] , 

         function($message) use($admin_email){
            $message->to($admin_email);
            $message->subject('Subject : Book a Free Taster Class');
    });

    // Mail to parent
    \Mail::send('emails.contact-us.parent', ['name' => $contact->parent_email] , 
             function($message) use($contact){
                 $message->to($contact->parent_email);
                 $message->subject('Subject : '.'Book a Free Taster Class');
               });


    return view('cms.success');
} 

  /* Success Page */
  public function success_page(Request $request){
    return view('cms.success');
  } 

  /*-------------------------------------
  |   Coupon code
  |--------------------------------------*/
  public function submit_coupon(Request $request)
  {
    $coupon_code = $request->coupon_code;
    $check_coupon = Coupon::where('coupon_code',$coupon_code)->first();
    $shop_voucher = ShopCartItems::where('user_id',Auth::user()->id)->where('voucher_code',$coupon_code)->first();

    //dd($coupon_code,$check_coupon,$shop_voucher);  

    $output = '';
  	if($request->ajax())
    {
      if($check_coupon == '' && $shop_voucher == '')
      {
        $output = '<div class="alert alert-danger" role="alert">Coupon not found.</div>';
      }
      elseif($check_coupon == ''){
        // If coupon not found
        // $output = '<div class="alert alert-danger" role="alert">Coupon not found.</div>'; 

        if(!empty($shop_voucher)){
            $check_voucher = Vouchure::where('id',$shop_voucher->voucher_id)->first(); 
        
            if($shop_voucher == '')
            {
                // If coupon & voucher not found
                $output .= '<div class="alert alert-danger" role="alert">Coupon not found.</div>'; 

            }else{
                // If coupon found
                $todayDate = date("Y-m-d"); 

                if($todayDate >= $check_voucher->end_date)
                {
                  $output .= '<div class="alert alert-danger" role="alert">Voucher has expired.</div>';

                }else if($todayDate <= $check_voucher->start_date)
                {
                  $output .= '<div class="alert alert-danger" role="alert">You can use this Voucher after '.$check_voucher->start_date.'<div>';
                }else
                {
                  // Cart Items
                  $user_id = \Auth::check() && Auth::user()->role == "user" ? Auth::user()->id : 0;
                  $userCart = \DB::table('shop_cart_items')->where('user_id',$user_id)->where('voucher_code',NULL)->where('type','cart')->get();

                  $count_userCart = \DB::table('shop_cart_items')->where('user_id',$user_id)->where('voucher_code',NULL)->where('type','order')->where('type','cart')->count();

                  $voucher_code = $request->coupon_code;
                  $check_voucher = Vouchure::where('id',$shop_voucher->voucher_id)->first(); 
                  $selected_products = explode(',',$check_voucher->products);
                  $selected_courses = explode(',', $check_voucher->courses);
                  $selected_camps = explode(',', $check_voucher->camps);

                  //dd($check_voucher->courses);

                  if(!empty($userCart))
                  {
                   // if($count_userCart <= $check_voucher->uses)
                   // {
                    foreach($userCart as $cart)
                    {
                       if($cart->shop_type == 'product' && in_array($cart->product_id,$selected_products))
                       { 
                        if(!empty($check_voucher->products))
                        {
                            $prod = ShopCartItems::where('user_id',$user_id)->where('orderID', '=', NULL)->where('product_id',$cart->product_id)->first();

                            if($prod->discount_code != $coupon_code)
                            { 
                              $pr = ShopCartItems::find($prod->id);
                              $pr->discount_code = $request->coupon_code;
                              $pr->discount_price = $check_voucher->flat_discount;
                              $pr->save();

                              $result = '<div class="alert alert-success" role="alert">Coupon applied successfully.</div>';

                            }else{
                              $result = '<div class="alert alert-danger" role="alert">Coupon already applied.</div>';
                            }
                        }

                      }elseif($cart->shop_type == 'course' && in_array($cart->product_id,$selected_courses))
                      {
                        if(!empty($check_voucher->courses)){

                            $prod = ShopCartItems::where('user_id',$user_id)->where('orderID', '=', NULL)->where('product_id',$cart->product_id)->first();

                            if($prod->discount_code != $coupon_code)
                            { 
                              $pr = ShopCartItems::find($prod->id);
                              $pr->discount_code = $request->coupon_code;
                              $pr->discount_price = $check_voucher->flat_discount;
                              $pr->save();

                              $result = '<div class="alert alert-success" role="alert">Coupon applied successfully.</div>';

                            }else{
                              $result = '<div class="alert alert-danger" role="alert">Coupon already applied.</div>';
                            }
                        }
                      }elseif($cart->shop_type == 'camp' && in_array($cart->product_id,$selected_camps))
                      {
                        if(!empty($check_voucher->camps))
                        {
                            $prod = ShopCartItems::where('user_id',$user_id)->where('orderID', '=', NULL)->where('product_id',$cart->product_id)->first();

                            if($prod->discount_code != $coupon_code)
                            { 
                              $pr = ShopCartItems::find($prod->id);
                              $pr->discount_code = $request->coupon_code;
                              $pr->discount_price = $check_voucher->flat_discount;
                              $pr->save();

                              $result = '<div class="alert alert-success" role="alert">Coupon applied successfully.</div>';

                            }else{
                              $result = '<div class="alert alert-danger" role="alert">Coupon already applied.</div>';
                            }
                        }
                      }
                    }
                  }
                // }else{
                //     $result = '<div class="alert alert-danger" role="alert">Coupon limit completed.</div>'; 
                // }
                $output .= $result;
                }
            }
        }

      }else{
        // If coupon found
        $todayDate = date("Y-m-d"); 

        if($todayDate >= $check_coupon->end_date)
        {
          $output .= '<div class="alert alert-danger" role="alert">Coupon has expired.</div>';

        }else if($todayDate <= $check_coupon->start_date)
        {
          $output .= '<div class="alert alert-danger" role="alert">You can use this coupon after '.$check_coupon->start_date.'<div>';
        }else
        {
          // Cart Items
          $user_id = \Auth::check() && Auth::user()->role == "user" ? Auth::user()->id : 0;
          $userCart = \DB::table('shop_cart_items')->where('user_id',$user_id)->where('type','cart')->get();

          $coupon_code = $request->coupon_code;
          $check_coupon = Coupon::where('coupon_code',$coupon_code)->first();
          $selected_products = explode(',',$check_coupon->products);
          $selected_courses = explode(',', $check_coupon->courses);

          if(!empty($userCart))
          {
            foreach($userCart as $cart)
            {
               if(in_array($cart->product_id,$selected_products))
               {
                $prod = ShopCartItems::where('user_id',$user_id)->where('orderID', '=', NULL)->where('product_id',$cart->product_id)->first();

                if($prod->discount_code != $coupon_code)
                { 
                  $pr = ShopCartItems::find($prod->id);
                  $pr->discount_code = $request->coupon_code;
                  $pr->discount_price = $check_coupon->flat_discount;
                  $pr->save();

                  $result = '<div class="alert alert-success" role="alert">Coupon applied successfully.</div>';

                }else{
                  $result = '<div class="alert alert-danger" role="alert">Coupon already applied.</div>';
                }

              }elseif(in_array($cart->product_id,$selected_courses)){

                $prod = ShopCartItems::where('user_id',$user_id)->where('orderID', '=', NULL)->where('product_id',$cart->product_id)->first();

                if($prod->discount_code != $coupon_code)
                { 
                  $pr = ShopCartItems::find($prod->id);
                  $pr->discount_code = $request->coupon_code;
                  $pr->discount_price = $check_coupon->flat_discount;
                  $pr->save();

                  $result = '<div class="alert alert-success" role="alert">Coupon applied successfully.</div>';

                }else{
                  $result = '<div class="alert alert-danger" role="alert">Coupon already applied.</div>';
                }
              }
            }
          }
          $output .= $result;
          
         }

      }                                     
    }

    $data = array(
                'output'   => $output,
            );


    return response()->json($data);

  }


/*--------------------------------
|   Childcare Voucher
|---------------------------------*/ 
public function save_childcare_voucher(Request $request)
{
    $provider_id = $request->provider;

    $billing_address = $request->session()->get('shopBillingAddress');
    $shipping_address = $request->session()->get('shippingAddress');

    $shop_cart_items = ShopCartItems::where('user_id',\Auth::user()->id)->where('type','cart')->where('orderID',NULL)->get(); 

    $amount = array();
    foreach($shop_cart_items as $co)
    {
        $amount[] = $co->total; 
    }
    $total_amount = array_sum($amount);

    foreach($shop_cart_items as $item)
    {
        $sci = ShopCartItems::find($item->id);
        $sci->type = 'order';
        $sci->orderID = '#DRHSHOP'.strtotime(date('y-m-d h:i:s'));
        $sci->save();

        $so = new ShopOrder;
        $so->provider_id = $provider_id;
        $so->user_id = \Auth::user()->id;
        $so->payment_by = 'Childcare';
        $so->amount = $total_amount;
        $so->billing_address = $billing_address;
        $so->shipping_address = $shipping_address;
        $so->orderID = $sci->orderID;
        $so->status = 1;
        
        if($so->save()){

            ShopCartItems::where('orderID', $so->orderID)->update(array('orderID' => $so->orderID));

              if(Auth::user()->createOrderFromCart($so))
                {
                    \Session::forget('shippingAddress');
                    \Session::forget('shopBillingAddress');

                    return redirect()->route('shop.checkout.thankyou', ['order_id' => $so->id]);  

                    $this->ShopProductOrderPlacedForVendorSuccess($so->id);
                    $this->ShopProductOrderPlacedSuccess($so->id);
                    //$this->AdminOrderSuccessOrderSuccess($o->id);

                }
        }
    }

}

/*--------------------------------
|   Wallet
|---------------------------------*/ 
public function save_wallet(Request $request)
{
    $wallet = Wallet::where('user_id',Auth::user()->id)->first(); 
    $wallet_amount = $wallet->money_amount; 

    $billing_address = $request->session()->get('shopBillingAddress');
    $shipping_address = $request->session()->get('shopBillingAddress');

    $shop_cart_items = ShopCartItems::where('user_id',\Auth::user()->id)->where('type','cart')->where('orderID',NULL)->get(); 

    $amount = array();
    foreach($shop_cart_items as $co)
    {
        $amount[] = $co->total; 
    }
    $total_amount = array_sum($amount);

    if($total_amount <= $wallet_amount)
    {
        foreach($shop_cart_items as $item)
        {
            $sci = ShopCartItems::find($item->id);
            $sci->type = 'order';
            $sci->orderID = '#DRHSHOP'.strtotime(date('y-m-d h:i:s'));  
            $sci->save();

            $so = new ShopOrder;
            $so->user_id = \Auth::user()->id;
            $so->payment_by = 'Wallet';
            $so->amount = $total_amount;
            $so->billing_address = $billing_address;
            $so->shipping_address = $shipping_address;
            $so->orderID = $sci->orderID;
            $so->status = 1;

            if($so->save()){

                $remaining_wallet_money = $wallet_amount - $total_amount;
                Wallet::where('user_id',Auth::user()->id)->update(array('money_amount' => $remaining_wallet_money));

                $walletHistory = WalletHistory::create($request->all()); 
                $walletHistory->user_id = \Auth::user()->id; 
                $walletHistory->type = 'debit';
                $walletHistory->money_amount = $total_amount;
                $walletHistory->save();

                ShopCartItems::where('orderID', $so->orderID)->update(array('orderID' => $so->orderID));

                  if(Auth::user()->createOrderFromCart($so))
                    {
                        \Session::forget('shippingAddress');
                        \Session::forget('shopBillingAddress');

                        return redirect()->route('shop.checkout.thankyou', ['order_id' => $so->id]);  

                        $this->ShopProductOrderPlacedForVendorSuccess($so->id);
                        $this->ShopProductOrderPlacedSuccess($so->id);
                        //$this->AdminOrderSuccessOrderSuccess($o->id);

                    }
            }
        }
    }elseif($total_amount > $wallet_amount){
        $remaining_wallet_money = $total_amount - $wallet_amount;

        $transaction_details = 'Wallet- '.$wallet_amount.', Stripe - '.$remaining_wallet_money;

        foreach($shop_cart_items as $item)
        {
            $sci = ShopCartItems::find($item->id);
            $sci->type = 'order';
            $sci->orderID = '#DRHSHOP'.strtotime(date('y-m-d h:i:s'));  
            $sci->save();

            $so = new ShopOrder;
            $so->user_id = \Auth::user()->id;
            $so->payment_by = 'Wallet & Stripe';
            $so->transaction_details = $transaction_details;
            $so->amount = $total_amount;
            $so->billing_address = $billing_address;
            $so->shipping_address = $shipping_address;
            $so->orderID = $sci->orderID;
            $so->status = 1;

            if($so->save()){

                $remaining_wallet_money = $wallet_amount - $total_amount;
                Wallet::where('user_id',Auth::user()->id)->update(array('money_amount' => $remaining_wallet_money));

                $walletHistory = WalletHistory::create($request->all()); 
                $walletHistory->user_id = \Auth::user()->id; 
                $walletHistory->type = 'debit';
                $walletHistory->money_amount = $total_amount;
                $walletHistory->save();

                ShopCartItems::where('orderID', $so->orderID)->update(array('orderID' => $so->orderID));

                  if(Auth::user()->createOrderFromCart($so))
                    {
                        \Session::forget('shippingAddress');
                        \Session::forget('shopBillingAddress');

                        return redirect()->route('shop.checkout.thankyou', ['order_id' => $so->id]);  

                        $this->ShopProductOrderPlacedForVendorSuccess($so->id);
                        $this->ShopProductOrderPlacedSuccess($so->id);
                        //$this->AdminOrderSuccessOrderSuccess($o->id);

                    }
            }
        }
    }

}

/*---------------------------------
|   Newsletter
|----------------------------------*/
public function newsletter_integration(Request $request)
{
    $entered_email = $request->email;
    $check_email = NewsletterSubscription::where('email',$request->email)->first();
    $existing_email = isset($check_email) ? $check_email->email : '';

    if(!empty($check_email))
    {
      return view('newsletter_success',compact('existing_email'))->with('error',$entered_email. ' email is already subscribed.');
    }
    else{
      $newsletter = NewsletterSubscription::create($request->all()); 
      $newsletter->status = 1;
      $newsletter->save();

      $user_email = $newsletter->email;

      // Mail to user
      \Mail::send('emails.newsletter.subscribe', ['user_email' => $user_email,'id' => $newsletter->id] , 
           function($message) use($user_email){
               $message->to($user_email);
               $message->subject('Subject : '.'Subscribe By Admin');
             });

      return view('newsletter_success',compact('entered_email'))->with('success',$entered_email. ' email is subscribed successfully.');
    }
}

/*---------------------------------
|   Unsubscribe user
|----------------------------------*/
public function unsubscribe_newsletter($id)
{ 
    $unsubscribed_user = NewsletterSubscription::where('id',$id)->first();
    $unsubscribed_user->status = 0;
    $unsubscribed_user->unsubscribed_by = $id;
    $unsubscribed_user->save();

    $user_email = $unsubscribed_user->email;

    // Mail to user
    \Mail::send('emails.newsletter.unsubscribe', ['user_email' => $user_email] , 
         function($message) use($user_email){
             $message->to($user_email);
             $message->subject('Subject : '.'Unsubscribe By User');
           });

    return \Redirect::back()->with('success',$user_email.' email is unsubscribed successfully.');

}

/*---------------------------------
|   Save Competition Report
|---------------------------------*/
public function add_competition(Request $request)
{ 
    $user_role = Auth::user()->role_id;
    $comp_id = isset($request->comp_id) ? $request->comp_id : '';

    $coach_user = User::where('id',$request->coach_id)->first(); 
    if(!empty($coach_user))
    {
      if($coach_user->role_id == 2)
      {
        $parent_id = $request->coach_id;
      }elseif($coach_user->role_id == 3)
      {
        $coach_id = $request->coach_id;
      }
    }

    $parent_user = User::where('id',$request->parent_id)->first(); 
    if(!empty($parent_user))
    {
      if($parent_user->role_id == 2)
      {
        $parent_id = $request->parent_id;
      }elseif($parent_user->role_id == 3)
      {
        $coach_id = $request->parent_id;
      }
    }

    if(!empty($comp_id))
    {
        $comp = Competition::find($comp_id);
        $comp->player_id = $request->player_id;
        $comp->comp_type = $request->comp_type;
        $comp->comp_date = $request->comp_date;
        $comp->comp_venue = $request->comp_venue;
        $comp->comp_name = $request->comp_name;
        $comp->parent_id = isset($parent_id) ? $parent_id : '0';
        $comp->coach_id = isset($coach_id) ? $coach_id : '0';
        $comp->save();

        $comp_id = base64_encode($comp->id);

        return redirect('/user/reports/comp/'.$comp_id)->with('success',' Competition updated successfully!');

    }else{

        if(!empty($request->player_id))
        {
            $comp = Competition::create($request->all()); 
            $comp->parent_id = isset($parent_id) ? $parent_id : '0';
            $comp->coach_id = isset($coach_id) ? $coach_id : '0';
            $comp->save();

            $comp_id = base64_encode($comp->id);

            return redirect('/user/reports/comp/'.$comp_id)->with('success',' Competition added successfully!');
        }else{
            return \Redirect::back()->with('error',' Please select player!');
        } 
    }

        
    
}

public function comp_data($id)
{
    $comp = Competition::where('id',$id)->first();
    return view('coach.match-report')->with('comp',$comp)->with('success',' Competition added successfully!');
}

/*---------------------------------
|   Save Match Report
|---------------------------------*/
public function add_match(Request $request)
{
  $data = $request->all();  

    if(!empty($request->match_id))
    {
        if(!empty($request->player_id) && !empty($request->comp_id))
        {
            $match = MatchReport::find($request->match_id); 
            $match->comp_id = $request->comp_id;
            $match->player_id = $request->player_id;
            $match->opponent_name = $request->opponent_name;
            $match->start_date = $request->start_date;
            $match->surface_type = $request->surface_type;
            $match->condition = $request->condition;
            $match->result = $request->result;
            $match->score = $request->score;
            $match->wht_went_well = $request->wht_went_well;
            $match->wht_could_better = $request->wht_could_better;
            $match->other_comments = $request->other_comments;
            $match->save();

            if($request->hasFile('match_chart'))
            {
                foreach ($data['match_chart'] as $number => $value)
                {    
                  $string = str_random(5);
                  $image[$number] = request()->match_chart[$number];  
                  $filename[$number] = time().$string.'.'.$image[$number]->getClientOriginalExtension();
                  $destinationPath[$number] = public_path('/uploads/game-charts');  
                  $image[$number]->move($destinationPath[$number], $filename[$number]);

                  $gc                 =   new MatchGameChart;
                  $gc->comp_id        =   $match->comp_id;
                  $gc->match_id       =   $match->id;
                  $gc->player_id      =   $match->player_id;
                  $gc['image']        =   isset($filename[$number]) ? $filename[$number] : '';
                  $gc->save(); 

                }
            }

            $comp_id = base64_encode($match->comp_id);

            return redirect('/user/reports/comp/'.$comp_id)->with('success','Match updated successfully!');
        }

    }else{

        if(!empty($request->player_id) && !empty($request->comp_id))
        {
            $match = MatchReport::create($request->all());
            $match->save();
   
            if(isset($data) && !(empty($data['match_chart'])))
            {
                if($request->hasFile('match_chart'))
                {
                    foreach ($data['match_chart'] as $number => $value){    
                    
                    $string = str_random(5);
                    $image[$number] = request()->match_chart[$number];  
                    $filename[$number] = time().$string.'.'.$image[$number]->getClientOriginalExtension();
                    $destinationPath[$number] = public_path('/uploads/game-charts');  
                    $image[$number]->move($destinationPath[$number], $filename[$number]);

                      $gc                 =   new MatchGameChart;
                      $gc->comp_id        =   $match->comp_id;
                      $gc->match_id       =   $match->id;
                      $gc->player_id      =   $match->player_id;
                      $gc['image']        =   isset($filename[$number]) ? $filename[$number] : '';
                      $gc->save(); 

                    }
                }

            }

            $comp_id = base64_encode($match->comp_id);

            return redirect('/user/reports/comp/'.$comp_id)->with('success','Match added successfully!');
        }else{
            return \Redirect::back()->with('error',' Please add competition first!');
        }
    }
    
}

/*--------------------------------------------------
| Remove game chart  
|--------------------------------------------------*/
public function remove_game_chart($comp_id,$match_id,$player_id,$chart_id)
{
  $compID = base64_decode($comp_id);
  $matchID = base64_decode($match_id);
  $playerID = base64_decode($player_id);
  $chartID = base64_decode($chart_id);

  $game_chart = MatchGameChart::where('id',$chartID)->where('comp_id',$compID)->where('match_id',$matchID)->where('player_id',$playerID)->first();
  $game_chart->delete();

  $image_path = "/uploads/game-charts".$game_chart->image;
  \File::delete($image_path);

  return \Redirect::back()->with('success','Game Chart has been deleted successfully.');
}

/*--------------------------------------------------
|   List of cometitions created by particular user 
|---------------------------------------------------*/
public function competition_list()
{
    $user_role = \Auth::user()->role_id; 
    if($user_role == '3')
    {
        $competitions = Competition::where('coach_id',Auth::user()->id)->paginate(10);
    }
    elseif($user_role == '2'){
        $competitions = Competition::where('parent_id', Auth::user()->id)->paginate(10);
    }

    return view('matches.competition',compact('competitions'));
}

/*--------------------------------------
|   Matches under competitions
|--------------------------------------*/
public function matches_under_comp($id)
{
    $comp_id = base64_decode($id);
    $matches = MatchReport::where('comp_id',$comp_id)->paginate(10);
    return view('matches.matches',compact('comp_id','matches')); 
}

/*--------------------------------------
|   Match Stats
|--------------------------------------*/
public function match_stats($comp_id,$match_id)
{
    return view('matches.stats',compact('comp_id','match_id'));
}

/*--------------------------------------
| Save match stats data 
|--------------------------------------*/
public function save_match_stats(Request $request)
{
    $match_stats = MatchStats::create($request->all()); 
    $match_stats->competition_id = $request->competition_id;
    $match_stats->tp_won = $request->tp_won;
    $match_stats->save();

    $data = json_encode($request->all());
    $stats_calculation = statsCalculation($data);

    $comp_id = base64_encode($request->competition_id);

    return redirect('/user/competitions/'.$comp_id)->with('success','Match Stats data added successfully.');
}

/*--------------------------------------
| View Match Stats
|---------------------------------------*/
public function view_match_stats($comp_id,$match_id)
{
    $stats = MatchStats::where('competition_id',$comp_id)->where('match_id',$match_id)->first();
    if(!empty($stats))
    {
        $stats_calculation = statsCalculation($stats);
    }else{
        $stats_calculation = '';
    }
    return view('matches.view-stats',compact('stats_calculation','comp_id','match_id'));
}

}
