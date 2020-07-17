@extends('inc.homelayout')
@section('title', 'DRH|Register')
@section('content')
@php $country_code = DB::table('country_code')->get(); @endphp
<style>
   #child_section, #medical_info, #child_contacts, #medical_beh, #media_consent, #primary_lang, #beh_info, #med_cond_info, #med_con_button, #pres_med_info, #allergy_button, #allergies_info, #special_needs_info{display: none;}
</style>
<div class="account-menu acc_sub_menu">
   <div class="container">
      <div class="menu-title">
         <span>Account</span> menu
      </div>
      <nav>
         <ul>
            <li><a href="{{ route('my-family') }}" class="{{ \Request::route()->getName() === 'my-family' ? 'active' : '' || \Request::route()->getName() === 'add-family-member' ? 'active' : '' || \Request::route()->getName() === 'edit-family-member' ? 'active' : '' }}">My family</a></li>
            <li><a href="{{ route('my-bookings') }}" class="{{ \Request::route()->getName() === 'my-bookings' ? 'active' : '' }}">My Bookings</a></li>
            <li><a href="{{ route('badges') }}" class="{{ \Request::route()->getName() === 'my-bookings' ? 'active' : '' }}"> DRH Tennis Pro</a></li>
            <li><a href="{{ route('linked_coaches') }}" class="{{ \Request::route()->getName() === 'linked_coaches' ? 'active' : '' }}">My Coaches</a></li>
            <li><a href="{{ route('parent_notifications') }}" class="{{ \Request::route()->getName() === 'parent_notifications' ? 'active' : '' }}">Notifications <span class="notification-icon"></span></a></li>
            <li><a href="" class="">Settings</a></li>
            <li><a href="{{ route('logout') }}" class="{{ \Request::route()->getName() === 'logout' ? 'active' : '' }}">Logout</a></li>
         </ul>
      </nav>
   </div>
</div>

<section class="register-sec">
   <div class="container">
      <div class="row justify-content-center">
         <div class="col-md-12">
            <form id="add-family-mem" class="register-form" method="POST">
               @csrf
                <input type="hidden" id="user_id_data" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="role_id" value="4">
               <div class="form-partition">
                  <div class="card-header">{{ __('Register a new family member') }}</div>
                  <div class="row">
                     <div class="form-radios" style="margin: 10px 0;">
                        <div class="col-sm-12">
                           <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Is this person an adult or a child?</p>
                           <div class="cstm-radio">
                              <input type="radio" name="type" data-type="adult" id="check_adult" {{$user->type == 'adult' ? 'checked' : ''}}>
                              <label for="adult">Adult</label>
                           </div>
                           <div class="cstm-radio">
                              <input type="radio" name="type" data-type="child" id="check_child" {{$user->type == 'child' ? 'checked' : ''}}>
                              <label for="child">Child</label>
                           </div>
                        </div>
                     </div>
                     <input type="hidden" class="form_type" id="form_type" name="form_type" value="{{$user->type}}">
                     <!-- First Name -->
                     <div class="form-group row">
                        <label for="first_name" class="col-md-12 col-form-label text-md-right">{{ __('First Name') }}</label>
                        <div class="col-md-12">
                           <input id="first_name" type="text" class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}" name="first_name" value="{{ $user->first_name }}" required>
                           @if ($errors->has('first_name'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('first_name') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Last Name -->
                     <div class="form-group row">
                        <label for="last_name" class="col-md-12 col-form-label text-md-right">{{ __('Last Name') }}</label>
                        <div class="col-md-12">
                           <input id="last_name" type="text" class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}" name="last_name" value="{{ $user->last_name }}" required>
                           @if ($errors->has('last_name'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('last_name') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Gender -->
                     <div class="form-group row gender-opt signup-gender-op">
                        <label for="gender" class="col-md-12 col-form-label text-md-right ">{{ __('Gender') }}</label>
                        <div class="col-md-12 det-gender-opt">
                           <input type="radio" id="male" name="gender_type" value="male" {{$user->gender == 'male' ? 'checked' : ''}}>
                           <label for="male">Male</label><br>
                           <input type="radio" id="female" name="gender_type" value="female" {{$user->gender == 'female' ? 'checked' : ''}}>
                           <label for="female">Female</label><br>
                           <input type="hidden" id="gen" name="gender" value="{{$user->gender}}">
                           <div id="select_gender"></div>
                           @if ($errors->has('gender'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('gender') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Date of Birth -->
                     <div class="form-group row">
                        <label for="date_of_birth" class="col-md-12 col-form-label text-md-right">{{ __('Date Of Birth') }}</label>
                        <div class="col-md-12">
                           <input id="date_of_birth" type="date" class="form-control{{ $errors->has('date_of_birth') ? ' is-invalid' : '' }}" name="date_of_birth" value="{{ $user->date_of_birth }}" required>
                           @if ($errors->has('date_of_birth'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('date_of_birth') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Address -->
                     <div class="form-group row address-detail">
                        <label for="address" class="col-md-12 col-form-label text-md-right">{{ __('Address (Number & Street)') }}</label>
                        <div class="col-md-12">
                           <input id="address" type="text" class="paste_address form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" name="address" value="{{ $user->address }}" required>
                           @if ($errors->has('address'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('address') }}</strong>
                           </span>
                           @endif
                           <div class="copy_address">
                              <a href="javascript:void(0);">Copy address of account holder</a>
                           </div>
                        </div>
                     </div>
                     <!-- Town -->
                     <div class="form-group row">
                        <label for="town" class="col-md-12 col-form-label text-md-right">{{ __('Town') }}</label>
                        <div class="col-md-12">
                           <input id="town" type="text" class="paste_town form-control{{ $errors->has('town') ? ' is-invalid' : '' }}" name="town" value="{{ $user->town }}" required>
                           @if ($errors->has('town'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('town') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Postcode -->
                     <div class="form-group row">
                        <label for="postcode" class="col-md-12 col-form-label text-md-right">{{ __('Postcode') }}</label>
                        <div class="col-md-12">
                           <input id="postcode" type="text" class="paste_postcode form-control{{ $errors->has('postcode') ? ' is-invalid' : '' }}" name="postcode" value="{{ $user->postcode }}" required>
                           @if ($errors->has('postcode'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('postcode') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- County -->
                     <div class="form-group row">
                        <label for="county" class="col-md-12 col-form-label text-md-right">{{ __('County') }}</label>
                        <div class="col-md-12">
                           <input id="county" type="text" class="paste_county form-control{{ $errors->has('county') ? ' is-invalid' : '' }}" name="county" value="{{ $user->county }}" required>
                           @if ($errors->has('county'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('county') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>
                     <!-- Country -->
                     <div class="form-group row">
                        <label for="country" class="col-md-12 col-form-label text-md-right">{{ __('Country') }}</label>
                        <div class="col-md-12">
                           <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                           <select id="country" name="country" class="paste_country form-control cstm-select-list">
                              <option value="{{$user->country}}">{{$user->country}}</option>
                              @foreach($country_code as $name)
                              <option value="{{$name->countryname}}">{{$name->countryname}}</option>
                              @endforeach
                           </select>
                           @if ($errors->has('country'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('country') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>

                     <!-- Tennis Club -->
                     <div class="form-group row">
                        <label for="county" class="col-md-12 col-form-label text-md-right">{{ __('Tennis Club') }}</label>
                        <div class="col-md-12">
                           <input id="tennis_club" type="text" class="paste_county form-control{{ $errors->has('tennis_club') ? ' is-invalid' : '' }}" name="tennis_club" value="{{ $user->tennis_club }}" required>
                           @if ($errors->has('tennis_club'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('tennis_club') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>

                     <!-- Relationship -->
                     <div class="form-group row">
                        <label for="relation" class="col-md-12 col-form-label text-md-right">{{ __('What is the relationship of the account holder to this person?') }}</label>
                        <div class="col-md-12">
                           <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                           <select id="relation" name="relation" class="form-control cstm-select-list">
                              <option selected="" disabled="" value="">Please Choose</option>
                              <option value="Mother" {{$user->relation == 'Mother' ? 'selected' : ''}}>Mother</option>
                              <option value="Father" {{$user->relation == 'Father' ? 'selected' : ''}}>Father</option>
                              <option value="Grandparent" {{$user->relation == 'Grandparent' ? 'selected' : ''}}>Grandparent</option>
                              <option value="Guardian" {{$user->relation == 'Guardian' ? 'selected' : ''}}>Guardian</option>
                              <option value="Spouse" {{$user->relation == 'Spouse' ? 'selected' : ''}}>Spouse/Partner</option>
                           </select>
                           @if ($errors->has('relation'))
                           <span class="invalid-feedback" role="alert">
                           <strong>{{ $errors->first('relation') }}</strong>
                           </span>
                           @endif
                        </div>
                     </div>

                     <!-- Profile Picture -->
                     <!-- <div class="form-group">
                           <div class="col-sm-12">
                              <label>Profile Picture</label>
                              <input type="file" name="profile_image" id="selImage" accept="image/*" onchange="ValidateSingleInput(this, 'image_src')">
                              @if ($errors->has('profile_image'))
                              <div class="error">{{ $errors->first('profile_image') }}</div>
                              @endif
                           </div>
                        </div>
                        @if(!empty($user->profile_image))
                        <img id="profile_image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$user->profile_image }}" />
                        @else
                        <img id="profile_image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/images').'/default.jpg' }}" />
                        @endif -->

                     <!-- Selection Section - Start -->
                     <div class="form-group row f-g-full">
                        <br/><br/>
                        <div class="form-radios">
                           <div class="col-sm-7">
                              <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Are you planning to book this person onto a coaching course or holiday camp with DRH Sports?</p>
                           </div>
                           <div class="col-sm-5">
                              <div class="cstm-radio"> 
                                 <input type="radio" name="book_person_type" id="book_person_yes" {{$user->book_person == 'book_person_yes' ? 'checked' : ''}}> 
                                 <label for="book_person_yes">Yes</label> 
                              </div>
                              <div class="cstm-radio"> 
                                 <input type="radio" name="book_person_type" id="book_person_no" {{$user->book_person == 'book_person_no' ? 'checked' : ''}}> 
                                 <label for="book_person_no">No</label> 
                              </div>
                              <input type="hidden" name="book_person" id="book_person" value="{{$user->book_person}}">
                           </div>
                        </div>
                     </div>
                     <!-- Selection Section - End -->
                  </div>
               </div>

                @php
                    $user_id = $user->id;
                    $child_detail = DB::table('children_details')->where('child_id',$user_id)->first(); 
                @endphp
                <input type="hidden" id="child_id" name="child_id" value="{{ $child_detail->id }}">

            @if(isset($child_detail))
               <!-- Child Information - Start -->
               @if($user->type == 'child' && $user->book_person == 'book_person_yes')
               <div class="form-partition" class="register-form" id="child_section" style="display: block;">
                  <div class="row">
                     <div class="form-group-wrap">
                        <h4>Child Information</h4>
                        <div class="form-group row">
                           <div class="form-radios" style="margin: 10px 0;">
                              <div class="col-sm-12">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Is English your child's primary language?</p>
                                 <div class="cstm-radio">
                                    <input type="radio" name="language" id="p-l-english-yes" {{$user->core_lang == 'p-l-english-yes' ? 'checked' : ''}}>
                                    <label for="p-l-english-yes">Yes</label>
                                 </div>
                                 <div class="cstm-radio">
                                    <input type="radio" name="language" id="p-l-english-no" {{$user->core_lang == 'p-l-english-no' ? 'checked' : ''}}>
                                    <label for="p-l-english-no">No</label>
                                 </div>
                                 <input type="hidden" name="core_lang" id="core_lang" value="{{isset($child_detail->core_lang) ? $child_detail->core_lang : ''}}">
                              </div>
                           </div>
                        </div>
                        <div class="form-group row" id="primary_lang">
                           <label class="col-md-12 col-form-label text-md-right">What is your child's primary language?</label>
                           <div class="col-md-12">
                              <input id="child-school" type="text" class="form-control" name="primary_language" value="{{isset($child_detail->primary_language) ? $child_detail->primary_language : ''}}">
                           </div>
                        </div>
                        <div class="form-group row f-g-full">
                           <label class="col-md-12 col-form-label text-md-right">Which school does your child attend?</label>
                           <div class="col-md-12">
                              <input id="child-school" type="text" class="form-control" name="school" value="{{isset($child_detail->school) ? $child_detail->school : ''}}">
                           </div>
                        </div>
                        <div class="form-group-wrap">
                           <h4>Please tell us about your child's sporting and activity interests.</h4>
                           <div class="form-wrap-container">
                              <p>Please tick the sports or activities this child likes and dislikes the most</p>
                              <table>
                                 <thead>
                                    <th></th>
                                    <th>Like</th>
                                    <th>Dislike</th>
                                    <th>Not Sure</th>
                                 </thead>
                                 <!-- Football -->
                                 <tbody>

                                  @foreach($activities as $ac)
                                    <tr class="activity" id="{{$ac->ac_title}}">
                                       <th scope="row">{{$ac->ac_title}}</th>
                                       <td>
                                          <div class="cstm-radio">
                                             <input type="radio" name="{{$ac->ac_title}}" value="0" id="f-like" />
                                             <label for="f-like">&nbsp;</label>
                                          </div>
                                       <td>
                                          <div class="cstm-radio">
                                             <input type="radio" name="{{$ac->ac_title}}" value="1" id="f-dislike" />
                                             <label for="f-dislike">&nbsp;</label>
                                          </div>
                                       </td>
                                       <td>
                                          <div class="cstm-radio">
                                             <input type="radio" name="{{$ac->ac_title}}" value="2" id="f-not-sure" />
                                             <label for="f-not-sure">&nbsp;</label>
                                          </div>
                                       </td>
                                    </tr>
                                    <!-- <input type="hidden" class="append_title" name="{{$ac->ac_title}}" id="{{$ac->ac_title}}"> -->
                                  @endforeach
                                 </tbody>
                              </table>
                              <!-- <a href="javascript:void(0);" id="child_info_to_next" class="cstm-btn" style="margin: 10px 0;">Go to next section</a> -->
                              <button id="child_info_to_next" class="cstm-btn" style="margin: 10px 0;">Go to next section</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               @endif
               <!-- Child Information - End -->

               <!-- Medical Info - Start-->
               @if($user->type == 'adult' && $user->book_person == 'book_person_yes')
               <div class="form-partition" class="register-form" id="medical_info" style="display: block;">
                  <div class="row">
                     <div class="form-group-wrap">
                        <h4>Medical information and emergency contacts</h4>
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Do you have any illness, injuries or medical conditions that would be helpful for the coach to be aware of?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio">
                                    <input type="radio" name="beh_need_type" id="illness-or-injury-yes" {{$child_detail->beh_need == 'illness-or-injury-yes' ? 'checked' : ''}}>
                                    <label for="illness-or-injury-yes">Yes</label>
                                 </div>
                                 <div class="cstm-radio">
                                    <input type="radio" name="beh_need_type" id="illness-or-injury-no" {{$child_detail->beh_need == 'illness-or-injury-no' ? 'checked' : ''}}>
                                    <label for="illness-or-injury-no">No</label>
                                 </div>
                                 <input type="hidden" name="beh_need" id="beh_need" value="{{isset($child_detail->beh_need) ? $child_detail->beh_need : ''}}">
                              </div>
                           </div>
                        </div>
                        @if(!empty($child_detail->beh_info))
                        <div class="form-group col-md-12 f-g-full label-textarea" id="beh_info" style="display: block;">
                           <label>Please give more detail as to the name, type and nature of the illness or injury so that the coach may better understand your needs.</label>
                           <textarea name="beh_info" id="beh_info_data">{{isset($child_detail->beh_info) ? $child_detail->beh_info : ''}}</textarea>
                        </div>
                        @else
                        <div class="form-group col-md-12 f-g-full label-textarea" id="beh_info">
                           <label>Please give more detail as to the name, type and nature of the illness or injury so that the coach may better understand your needs.</label>
                           <textarea name="beh_info" id="beh_info_data"></textarea>
                        </div>
                        @endif
                     
                        <div class="col-sm-12">
                           <p style="font-weight: 500; margin-right: 15px;">Please provide details for the person designated as your emergency contact.</p>
                        </div>
                        <div class="form-group row">
                           <label class="col-md-12 col-form-label text-md-right">contact 1 - first name:</label>
                           <div class="col-md-12">
                              <input id="em_first_name" type="text" class="form-control" name="em_first_name" value="{{isset($child_detail->em_first_name) ? $child_detail->em_first_name : ''}}">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label class="col-md-12 col-form-label text-md-right">contact 1 - surname:</label>
                           <div class="col-md-12">
                              <input id="em_last_name" type="text" class="form-control" name="em_last_name" value="{{isset($child_detail->em_last_name) ? $child_detail->em_last_name : ''}}">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label class="col-md-12 col-form-label text-md-right">contact 1 - tel number:</label>
                           <div class="col-md-12">
                              <input id="em_phone" type="tel" class="form-control" name="em_phone" value="{{isset($child_detail->em_phone) ? $child_detail->em_phone : ''}}">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label class="col-md-12 col-form-label text-md-right">contact 1 - email:</label>
                           <div class="col-md-12">
                              <input id="em_email" type="email" class="form-control" name="em_email" value="{{isset($child_detail->em_email) ? $child_detail->em_email : ''}}" >
                           </div>
                        </div>
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">I confirm that the information given above is accurate and correct to the best of my knowledge at the time of registration. I also confirm that if any of the details change, I will amend the form to reflect these changes.</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="correct_info_type" id="confirm_accurate_yes" {{$child_detail->correct_info == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="correct_info_type" id="confirm_accurate_no" {{$child_detail->correct_info == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="correct_info" id="correct_info" value="{{isset($child_detail->correct_info) ? $child_detail->correct_info : ''}}">
                              </div>
                           </div>
                           <div class="col-sm-12" style="margin-top: 15px;">
                              <p><b style="display: block;">Please note:</b>
                                 You may be asked to confirm the above details are all correct before being able to complete future bookings.
                              </p>
                              <!-- <a href="javascript:void(0);" id="medical_info_to_next" class="cstm-btn" style="margin: 10px 0;">Complete registration</a> -->

                              <button id="medical_info_to_next" class="cstm-btn" style="margin: 10px 0;">Go to next section</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               @endif

            </form>
            <!-- Medical Info - End-->

            <input type="hidden" name="mem_id" id="mem_id">

            <!-- Child Contacts - Start -->
            <form method="POST" class="register-form" id="child-contacts">
               @csrf

               @php
                  $count = 1;
               @endphp
               <div class="form-partition" id="child_contacts">
                  <div class="row">
                     <div class="form-group-wrap">
                        <h4>Child contacts</h4>
                        <div class="form-wrap-container">
                           <h5>Contacts and designated adults for activity pick up/drop off</h5>
                           <p><b style="display: block;">Please note:</b>
                              All information including payment and booking information, notices about upcoming events and notifications from linked coaches will be sent to the account holder email address.
                           </p>
                           <p>If anyone other than the contacts below acts as the pick up/drop off for this child, we will need consent given by the account holder via email to <a href="javascript:void(0);">info@drhsports.co.uk</a></p>
                        </div>

                        <div class="child-contact-container" id="sec_contact">
                           <input type="hidden" id="noOfContact" value="{{$count+1}}">

                        <!-- Contact - 1 -->
                        <div id="contact_section" class="contact_section[{{$count}}]">
                           <div class="col-sm-12">
                              <h5 style="width: 100%;">Contact {{$count}}:</h5>
                              <p>This is the adult we expect to be the main person picking up and dropping off this child from the activity.</p>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count}} - first name:</label>
                              <div class="col-md-12">
                                 <input id="con_first_name" type="text" class="form-control" name="con_first_name[]" value="{{isset($child_detail->con_first_name) ? $child_detail->con_first_name : ''}}">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count}} - surname:</label>
                              <div class="col-md-12">
                                 <input id="con_last_name" type="text" class="form-control" name="con_last_name[]" value="{{isset($child_detail->con_last_name) ? $child_detail->con_last_name : ''}}">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count}} - tel number:</label>
                              <div class="col-md-12">
                                 <input id="con_phone" type="tel" class="form-control" name="con_phone[]" value="{{isset($child_detail->con_phone) ? $child_detail->con_phone : ''}}">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count}} - email:</label>
                              <div class="col-md-12">
                                 <input id="con_email" type="email" class="form-control" name="con_email[]" value="{{isset($child_detail->con_email) ? $child_detail->con_email : ''}}" >
                              </div>
                           </div>
                           <div class="form-group row">
                              <label for="relation" class="col-md-12 col-form-label text-md-right">What is this persons relationship to the child?</label>
                              <div class="col-md-12">
                                 <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                                 <select id="con_relation" name="con_relation[]" class="form-control cstm-select-list">
                                    <option selected="" disabled="" value="">Please Choose</option>
                                    <option value="Mother" {{$child_detail->con_relation == 'Mother' ? 'selected' : ''}}>Mother</option>
                                    <option value="Father" {{$child_detail->con_relation == 'Father' ? 'selected' : ''}}>Father</option>
                                    <option value="Grandparent" {{$child_detail->con_relation == 'Grandparent' ? 'selected' : ''}}>Grandparent</option>
                                    <option value="Guardian" {{$child_detail->con_relation == 'Guardian' ? 'selected' : ''}}>Guardian</option>
                                    <option value="Spouse" {{$child_detail->con_relation == 'Spouse' ? 'selected' : ''}}>Spouse/Partner</option>
                                 </select>
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">If you choose other who are they?</label>
                              <div class="col-md-12">
                                 <input id="con_if_other" type="text" class="form-control" name="con_if_other[]" value="{{isset($child_detail->con_email) ? $child_detail->con_if_other : ''}}">
                              </div>
                           </div>
                        </div>

                        <!-- Contact -2 -->
                        <div id="contact_section" class="contact_section[{{$count+1}}]">
                           <div class="col-sm-12">
                              <h5 style="width: 100%;">Contact {{$count+1}}:</h5>
                              <p>This is the adult we expect to be the main person picking up and dropping off this child from the activity.</p>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count+1}} - first name:</label>
                              <div class="col-md-12">
                                 <input id="con_first_name" type="text" class="form-control" name="con_first_name[{{$count}}]" value="">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count+1}} - surname:</label>
                              <div class="col-md-12">
                                 <input id="con_last_name" type="text" class="form-control" name="con_last_name[{{$count+1}}]" value="">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count+1}} - tel number:</label>
                              <div class="col-md-12">
                                 <input id="con_phone" type="tel" class="form-control" name="con_phone[{{$count+1}}]" value="">
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">contact {{$count+1}} - email:</label>
                              <div class="col-md-12">
                                 <input id="con_email" type="email" class="form-control" name="con_email[{{$count+1}}]" value="" >
                              </div>
                           </div>
                           <div class="form-group row">
                              <label for="relation" class="col-md-12 col-form-label text-md-right">What is this persons relationship to the child?</label>
                              <div class="col-md-12">
                                 <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                                 <select id="con_relation" name="con_relation[{{$count+1}}]" class="form-control cstm-select-list">
                                    <option selected="" disabled="" value="">Please Choose</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Father">Father</option>
                                    <option value="Grandparent">Grandparent</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Spouse">Spouse/Partner</option>
                                 </select>
                              </div>
                           </div>
                           <div class="form-group row">
                              <label class="col-md-12 col-form-label text-md-right">If you choose other who are they?</label>
                              <div class="col-md-12">
                                 <input id="con_if_other" type="text" class="form-control" name="con_if_other[{{$count+1}}]" value="" >
                              </div>
                           </div>
                        </div>

                        </div>
                        <div class="child-contact-buttons">
                           <a href="javascript:void(0);" onclick="addcontact();" class="additional_contact cstm-btn">Add an additional contact <i class="fas fa-plus"></i></a>
                           <!-- <a href="javascript:void(0);" id="child_cont_to_next" class="cstm-btn">Go to next section</a> -->
                           <button id="child_cont_to_next" class="cstm-btn">Go to next section</button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
            <!-- Child Contacts - End -->

            <!-- Medical Behavioural - Start -->
            <form method="POST" class="register-form" id="med-beh">
               @csrf
               @php $count = 1; @endphp
               <div class="form-partition" id="medical_beh">
                  <div class="row">
                     <h4>Medical & behavioural conditions</h4>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Does your child have any <b>medical conditions</b> that we should be aware of?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="med_cond_type" id="confirm_accurate_yes" {{$child_detail->med_cond == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="med_cond_type" id="confirm_accurate_no" {{$child_detail->med_cond == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="med_cond" id="med_cond" value="{{isset($child_detail->med_cond) ? $child_detail->med_cond : ''}}">
                              </div>
                           </div>
                        </div>
                        @if(!empty($child_detail->med_cond_info))
                        <div id="sec_med_con" style="display: block;">
                           <input type="hidden" id="noOfMed" value="{{$count}}">
                           <div class="form-group col-md-12 f-g-full label-textarea" id="med_cond_info" style="display: block;">
                              <label>Please state the name of the medical condition and describe how it affects this child.</label>
                              <textarea spellcheck="false" name="med_cond_info[{{$count}}]" id="med_con_data">{{$child_detail->med_cond_info}}</textarea>
                           </div>
                        </div>
                        <div class="child-contact-buttons" id="med_con_button">
                           <a href="javascript:void(0);" onclick="addmedical();" class="cstm-btn">Add another medical condition <i class="fas fa-plus"></i></a>
                        </div>
                        @else
                        <div id="sec_med_con">
                           <input type="hidden" id="noOfMed" value="{{$count}}">
                           <div class="form-group col-md-12 f-g-full label-textarea" id="med_cond_info">
                              <label>Please state the name of the medical condition and describe how it affects this child.</label>
                              <textarea spellcheck="false" name="med_cond_info[{{$count}}]" id="med_con_data"></textarea>
                           </div>
                        </div>
                        <div class="child-contact-buttons" id="med_con_button">
                           <a href="javascript:void(0);" onclick="addmedical();" class="cstm-btn">Add another medical condition <i class="fas fa-plus"></i></a>
                        </div>
                        @endif
                        
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Does your child have any <b>allergies</b> that we should be aware of?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="allergies_type" id="confirm_accurate_yes" {{$child_detail->allergies == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="allergies_type" id="confirm_accurate_no" {{$child_detail->allergies == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="allergies" id="allergies" value="{{isset($child_detail->allergies) ? $child_detail->allergies : ''}}">
                              </div>
                           </div>
                        </div>
                        @if(!empty($child_detail->allergies_info))
                        <div id="sec_all" style="display: block;">
                           <input type="hidden" id="noOfAllergy" value="{{$count}}">
                           <div class="form-group col-md-12 f-g-full label-textarea" id="allergies_info" style="display: block;">
                              <label>Please state the name of the allergy and describe how it affects this child.</label>
                              <textarea spellcheck="false" name="allergies_info[{{$count}}]" id="allergies_data">{{isset($child_detail->allergies_info) ? $child_detail->allergies_info : ''}}</textarea>
                           </div>
                        </div>
                    
                        <div class="child-contact-buttons"id="allergy_button">
                           <a href="javascript:void(0);" onclick="addallergy();" class="cstm-btn">Add another allergy <i class="fas fa-plus"></i></a>
                        </div>
                        @else
                        <div id="sec_all">
                           <input type="hidden" id="noOfAllergy" value="{{$count}}">
                           <div class="form-group col-md-12 f-g-full label-textarea" id="allergies_info">
                              <label>Please state the name of the allergy and describe how it affects this child.</label>
                              <textarea spellcheck="false" name="allergies_info[{{$count}}]" id="allergies_data"></textarea>
                           </div>
                        </div>
                    
                        <div class="child-contact-buttons"id="allergy_button">
                           <a href="javascript:void(0);" onclick="addallergy();" class="cstm-btn">Add another allergy <i class="fas fa-plus"></i></a>
                        </div>
                        @endif
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Will your child need to take any prescribed medication during the coaching course or holiday camp</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="pres_med_type" id="confirm_accurate_yes" {{$child_detail->pres_med == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="pres_med_type" id="confirm_accurate_no" {{$child_detail->pres_med == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">no</label> 
                                 </div>
                                 <input type="hidden" name="pres_med" id="pres_med" value="{{isset($child_detail->pres_med) ? $child_detail->pres_med : ''}}">
                              </div>
                           </div>
                        </div>
                        @if(!empty($child_detail->pres_med_info))
                        <div class="form-group col-md-12 f-g-full label-textarea" id="pres_med_info" style="display: block;">
                           <label>Please state the name of the medication along with how and when this might be administered.</label>
                           <textarea spellcheck="false" name="pres_med_info" id="pres_med_data">{{isset($child_detail->pres_med_info) ? $child_detail->pres_med_info : ''}}</textarea>
                        </div>
                        @else
                        <div class="form-group col-md-12 f-g-full label-textarea" id="pres_med_info">
                           <label>Please state the name of the medication along with how and when this might be administered.</label>
                           <textarea spellcheck="false" name="pres_med_info" id="pres_med_data"></textarea>
                        @endif
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Does this child have any additional medical requirements that we may need to be aware of?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="med_req_type" id="confirm_accurate_yes" {{$child_detail->med_req == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="med_req_type" id="confirm_accurate_no" {{$child_detail->med_req == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="med_req" id="med_req" value="{{isset($child_detail->med_req) ? $child_detail->med_req : ''}}">
                              </div>
                           </div>
                        </div>
                        <div class="form-group col-md-12 f-g-full label-textarea" id="med_req_info">
                           <textarea spellcheck="false" id="med_req_data" name="med_req_info">{{isset($child_detail->med_req_info) ? $child_detail->med_req_info : ''}}</textarea>
                        </div>
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Is this child toilet trained and able to go to the toilet without assistance from an adult?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="toilet_type" id="confirm_accurate_yes" {{$child_detail->toilet == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="toilet_type" id="confirm_accurate_no" {{$child_detail->toilet == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="toilet" id="toilet" value="{{isset($child_detail->toilet) ? $child_detail->toilet : ''}}">
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="form-group-wrap">
                        <div class="col-sm-12">
                           <h5>Behavioural, learning difficulties and/or other disability matters</h5>
                        </div>
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Are there any behavioural and/or special needs we need to consider to help your child to settle, participate in and enjoy their activity?</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="special_needs_type" id="confirm_accurate_yes" {{$child_detail->special_needs == 'confirm_accurate_yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="special_needs_type" id="confirm_accurate_no" {{$child_detail->special_needs == 'confirm_accurate_no' ? 'checked' : ''}}> <label for="confirm_accurate_no">No</label> 
                                 </div>
                                 <input type="hidden" name="special_needs" id="special_needs" value="{{isset($child_detail->special_needs) ? $child_detail->special_needs : ''}}">
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group col-md-12 f-g-full label-textarea" id="special_needs_info">
                           <label>Please provide more information</label>
                           <textarea spellcheck="false" name="special_needs_info" id="special_needs_data">{{isset($child_detail->special_needs_info) ? $child_detail->special_needs_info : ''}}</textarea>
                        </div>
                     </div>
                     <div class="form-group-wrap">
                        <!-- <h4>Participant medical information</h4> -->
                        <div class="form-group col-md-12 f-g-full label-textarea">
                           <label>Are there any strategies you would prefer us to use to manage situations where a child may feel anxious?</label>
                           <textarea spellcheck="false" id="situation" name="situation">{{isset($child_detail->situation) ? $child_detail->situation : ''}}</textarea>
                        </div>
                        <div class="child-contact-buttons">
                           <!-- <a href="javascript:void(0);" id="med_beh_to_next" class="cstm-btn">Go to next section</a> -->
                           <button id="med_beh_to_next" class="cstm-btn">Go to next section</button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
            <!-- Medical Behavioural - End -->

            <!-- Media Consent - Start -->
            <form method="POST" class="register-form" id="media-consent">
               @csrf
               <div class="form-partition" id="media_consent">
                  <div class="row">
                     <div class="form-group-wrap">
                        <h4>Media Consent</h4>
                        <div class="form-wrap-container">
                           <p>During our holiday camps and non school coaching classes, we may take photos/videos of the activity to use solely for promotional purposes.</p>
                        </div>
                        <div class="form-radios">
                           <div class="col-sm-7">
                              <p style="display: inline-block; font-weight: 500; margin-right: 15px;">Do you give consent for this child to be included in photos and videos to be used for promotional purposes?</p>
                           </div>
                           <div class="col-sm-5">
                              <div class="cstm-radio">
                                 <input type="radio" name="media_type" id="consent-yes" {{$child_detail->media == 'consent-yes' ? 'checked' : ''}}>
                                 <label for="consent-yes">Yes</label>
                              </div>
                              <div class="cstm-radio">
                                 <input type="radio" name="media_type" id="consent-no" {{$child_detail->media == 'consent-no' ? 'checked' : ''}}>
                                 <label for="consent-no">No</label>
                              </div>
                              <input type="hidden" name="media" id="media" value="{{isset($child_detail->media) ? $child_detail->media : ''}}">
                           </div>
                        </div>
                        <div class="form-group row f-g-full">
                           <div class="form-radios">
                              <div class="col-sm-7">
                                 <p style="display: inline-block; font-weight: 500; margin-right: 15px;">I confirm that the information given above is accurate and correct to the best of my knowledge at the time of registration. I also confirm that if any of the details change, I will amend the form to reflect these changes.</p>
                              </div>
                              <div class="col-sm-5">
                                 <div class="cstm-radio"> 
                                    <input type="radio" name="confirm_type" id="confirm_type" result="yes" value="yes" {{$child_detail->confirm == 'yes' ? 'checked' : ''}}> <label for="confirm_accurate_yes">Yes</label> 
                                 </div>
                                 <input type="hidden" name="confirm" id="confirm" value="{{isset($child_detail->confirm) ? $child_detail->confirm : ''}}">
                              </div>
                           </div>
                           <div class="col-sm-12" style="margin-top: 15px;">
                              <p><b style="display: block;">Please note:</b>
                                 You may be asked to confirm the above details are all correct before being able to complete future bookings.
                              </p>
                              <!-- <a href="javascript:void(0);" class="cstm-btn" style="margin: 10px 0;">Complete registration</a> -->
                              <button id="completed" id="complete_registration" class="cstm-btn" style="margin: 10px 0;">Submit</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
            <!-- Media Consent - End -->
        @endif

         </div>
         <!--<div class="form-button">
            <div class="form-group row mb-0">
               <div class="col-md-12 form-btn">
                  <button type="submit" class="cstm-btn">
                  {{ __('Register') }}
                  </button>
               </div>
            </div>
            </div>
         </form>-->
         <div class="delete-child-container">
            <h2>Delete Child</h2>
            <a href="{{url('user/family-member/delete')}}/{{$user->id}}" class="cstm-btn" onclick="return confirm('Are you sure you want to delete this family member?')">
            I confirm i want to delete this child
            </a>
         </div>
      </div>
   </div>
   </div>
</section>
@endsection

<!-- Course Dates Management -->
<script type="text/javascript">

      function addmedical(){
            var number = parseInt($("#noOfMed").val());  
            var newnumber =number+1;                        
            $("#noOfMed").val(newnumber);

            var mainHtml='<div class="form-group col-md-12 f-g-full label-textarea" id="med_cond_info" style="display:block;"><label>Please state the name of the medical condition and describe how it affects this child.</label><textarea spellcheck="false" name="med_cond_info['+newnumber+']" id="med_con_data"></textarea></div>';

            $("#sec_med_con").append(mainHtml);
      }

      function addallergy(){
            var numb = parseInt($("#noOfAllergy").val());  
            var newnumb =numb+1;                        
            $("#noOfAllergy").val(newnumb);

            var mainHtml='<div class="form-group col-md-12 f-g-full label-textarea" id="allergies_info"><label>Please state the name of the allergy and describe how it affects this child.</label><textarea spellcheck="false" name="allergies_info['+newnumb+']" id="allergies_data"></textarea></div>';

            $("#sec_all").append(mainHtml);
      }

      function addcontact(){
            var num = parseInt($("#noOfContact").val());  
            var newnum = num+1;                       
            $("#noOfContact").val(newnum);

            var mainHtml='<div id="contact_section" class="contact_section['+newnum+']"><div class="col-sm-12"><h5 style="width: 100%;">Contact '+newnum+':</h5><p>This is the adult we expect to be the main person picking up and dropping off this child from the activity.</p></div><div class="form-group row"><label class="col-md-12 col-form-label text-md-right">contact '+newnum+' - first name:</label><div class="col-md-12"><input id="con_first_name" type="text" class="form-control" name="con_first_name['+newnum+']" value=""></div></div>';

            mainHtml+='<div class="form-group row"><label class="col-md-12 col-form-label text-md-right">contact '+newnum+' - surname:</label><div class="col-md-12"><input id="con_last_name" type="text" class="form-control" name="con_last_name['+newnum+']" value=""></div></div>';

            mainHtml+='<div class="form-group row"><label class="col-md-12 col-form-label text-md-right">contact '+newnum+' - tel number:</label><div class="col-md-12"><input id="con_phone" type="tel" class="form-control" name="con_phone['+newnum+']" value=""></div></div>';

            mainHtml+='<div class="form-group row"><label class="col-md-12 col-form-label text-md-right">contact '+newnum+' - email:</label><div class="col-md-12"><input id="con_email" type="email" class="form-control" name="con_email['+newnum+']" value="" ></div></div>';

            mainHtml+='<div class="form-group row"><label for="relation" class="col-md-12 col-form-label text-md-right">What is this persons relationship to the child?</label><div class="col-md-12"><link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"><select id="con_relation" name="con_relation['+newnum+']" class="form-control cstm-select-list"><option selected="" disabled="" value="">Please Choose</option><option value="Mother">Mother</option><option value="Father">Father</option><option value="Grandparent">Grandparent</option><option value="Guardian">Guardian</option><option value="Spouse">Spouse/Partner</option></select></div></div>';

            mainHtml+='<div class="form-group row"><label class="col-md-12 col-form-label text-md-right">If you choose other who are they?</label><div class="col-md-12"><input id="con_if_other" type="text" class="form-control" name="con_if_other['+newnum+']" value="" ></div></div></div>';

            $("#sec_contact").append(mainHtml);

            var contact_count = $("#noOfContact").val();
            if(contact_count >= '4')
            {
               $('.additional_contact').css('display','none');
            }
      }

</script>