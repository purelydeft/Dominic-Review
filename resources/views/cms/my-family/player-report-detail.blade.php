@extends('inc.homelayout')
@section('title', 'DRH|Register')
@section('content')
@php
$country_code = DB::table('country_code')->get();
$notification = DB::table('parent_coach_reqs')->where('coach_id',Auth::user()->id)->where('status',NULL)->count();
$user = DB::table('users')->where('role_id',3)->where('id',Auth::user()->id)->first();
@endphp
<style>
div#report_detail_cont {
    border: 3px solid #be298d;
    padding: 20px;
}
.cst_active{
    color:green;
}
.cst_in-active{
    color:red;
}
</style>
<div class="account-menu acc_sub_menu d-print-none">
    <div class="container">
        <div class="menu-title">
            <span>Account</span> menu
        </div>
        <nav>
            <ul>
                @php
                $user_id = \Auth::user()->role_id;
                @endphp

                @if($user_id == '2')
                
                    @include('inc.parent-menu')

                @elseif($user_id == 3)

                <li><a href="{{ route('coach_profile') }}" class="{{ \Request::route()->getName() === 'coach_profile' ? 'active' : '' }}">My Profile</a></li>
                <li><a href="{{ route('coach_report') }}" class="{{ \Request::route()->getName() === 'coach_report' ? 'active' : '' }}">Reports</a></li>
                <!-- <li><a href="{{ route('qualifications') }}" class="{{ \Request::route()->getName() === 'qualifications' ? 'active' : '' }}">Qualifications</a></li> -->
                @if(!empty($user))
                @if($user->enable_inovice == 1)
                <li><a href="{{ route('upload_invoice') }}" class="{{ \Request::route()->getName() === 'upload_invoice' ? 'active' : '' || \Request::route()->getName() === 'add_upload_invoice' ? 'active' : '' }}">Invoices</a></li>
                @endif
                @endif
                <li><a href="{{ route('coach_player') }}" class="{{ \Request::route()->getName() === 'coach_player' ? 'active' : '' }}">My Players</a></li>
                <li><a href="{{ route('my-bookings') }}" class="{{ \Request::route()->getName() === 'my-bookings' ? 'active' : '' }}">My Bookings</a></li>
                <li><a href="{{ route('request_by_parent') }}" class="{{ \Request::route()->getName() === 'request_by_parent' ? 'active' : '' }}">Notifications <span class="notification-icon">({{$notification}})</span></a></li>
                <li><a href="{{ route('account_settings') }}" class="{{ \Request::route()->getName() === 'account_settings' ? 'active' : '' }}">Settings</a></li>
                <li><a href="{{ route('logout') }}" class="{{ \Request::route()->getName() === 'logout' ? 'active' : '' }}">Logout</a></li>
                @endif
            </ul>
        </nav>
    </div>
</div>
<div class="print_logo">
    <img src="http://49.249.236.30:8654/dominic-new/public/uploads/1584078701website_logo.png">
</div>
<section class="member section-padding report_detail_page">
    <div class="container">
        <div class="pink-heading">
            <h2>Your Report</h2>
        </div>
        <div class="col-md-12">
            @if(!empty($report)> 0)


            <div class="player-report-table tbl_shadow">
                <div class="report-table-wrap report-tab-sec report-tab-one player_rp_detail">

                    @if($report->type == 'simple')
                    <div class="col-md-12 report_row">
                     
                        <table class="table table-bordered  cst-reports" style="width:100%">
                          <tr>
                            <th><p><b>Date</b></p></th>
                            <th><p><b>Player Name</b></p></th> 
                            <th><p><b>Report Type</b></p></th>
                            <th><p><b>Coach Name</b></p></th>
                            <th><p><b>Season</b></p></th>
                            <th><p><b>Course</b></p></th>
                          </tr>
                          <tr>
                            <td>@php echo date("d/m/Y", strtotime($report->date)); @endphp</td>
                            <td>@php echo getUsername($report->player_id); @endphp</td>
                            <td>@if($report->type == 'simple') End of Term Report @elseif($report->type == 'complex') Player Report @endif</td>
                            <td>@php echo getUsername($report->coach_id); @endphp</td>
                            <td>@if($report->type == 'simple') @php echo getSeasonname($report->season_id); @endphp @else - @endif</td>
                            <td>@if($report->type == 'simple') @php echo getCourseName($report->course_id); @endphp @else - @endif</td>
                          </tr>
                        </table>
                        
                    </div>
                    @endif
                    <br/>
                    <div id="report_detail_cont">
                        <p class="report-2-cont">{!! getAllValueWithMeta('report_detail', 'report') !!}</p>
                    </div>
                    <br/><br/>

                    @if($report->type == 'complex')

                    <div class="col-md-12 report_row">
                        <table class="table table-bordered  cst-reports">
                            <tbody>
                                <tr>
                                    <th>
                                        <p><b>Player Name</b></p>
                                    </th>
                                    <td><h4><strong>@php echo getUsername($report->player_id); @endphp</strong></h4></td>
                                </tr>
                                <tr>
                                    <th>
                                        <p><b>Report Date</b></p>
                                    </th>
                                    <td>@php echo date("d/m/Y", strtotime($report->date)); @endphp</td>
                                </tr>
                                <tr>
                                    <th>
                                        <p><b>Report Type</b></p>
                                    </th>
                                    <td>@if($report->type == 'simple') End of Term Report @elseif($report->type == 'complex') Player Report @endif</td>
                                </tr>

                                <tr>
                                    <th>
                                        <p><b>Coach Name</b></p>
                                    </th>
                                    <td>@php echo getUsername($report->coach_id); @endphp</td>
                                </tr>
                                <tr>
                                    <th>
                                        <p><b>Coach Feedback</b></p>
                                    </th>
                                    <td>{{isset($report->feedback) ? $report->feedback : ''}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @endif

                    @if($report->type == 'simple')
                    <div class="row">
                            @php
                                $report_questions = DB::table('report_questions')->get();
                                @endphp
                                @foreach($report_questions as $ques)
                                @php
                                $options = DB::table('report_question_options')->where('report_question_id',$ques->id)->get();
                                $player_rp = DB::table('player_reports')->where('player_id',$report->player_id)->where('season_id',$report->season_id)->where('course_id',$report->course_id)->first();  
                                @endphp

                                @if($player_rp)

                                @php 
                                    $selected_options = json_decode($player_rp->selected_options);
                                    $cat_option=[];
                                @endphp

                                @foreach($selected_options as $opt)
                                @php 
                                    $sel_data = explode('-',$opt);
                                    $cat_option[] =  $sel_data[0].'-'.$sel_data[1];
                                @endphp
                                @endforeach

                                    <div class="col-md-6">
                                    <div class="inner-form-box">
                                        <p class="top-heading">{{$ques->title}}</p>
                                        <div class="form-wrap">
                                            @foreach($options as $op)
                                            @php
                                                $all_option = $ques->id.'-'.$op->id; 
                                                $all_opt_arr = explode(' ',$all_option);
                                            @endphp
                                            

                                            @if(in_array($all_option,$cat_option)) 
                                                <div class="form-check">
                                                    <span class="cst_active cc_cursor"><i class="fas fa-check-circle"></i></span>
                                                    <!-- <input class="form-check-input" name="selected_options[]" type="checkbox" value="{{$ques->id}}-{{$op->id}}" disabled checked id="defaultCheck"> -->
                                                    <label class="form-check-label" for="defaultCheck1">
                                                        {{$op->option_title}}
                                                    </label>
                                                </div>
                                            @else
                                                <div class="form-check">
                                                    <span class="cst_in-active cc_cursor"><i class="fas fa-times-circle"></i></span>
                                                    <!-- <input class="form-check-input" name="selected_options[]" type="checkbox" value="{{$ques->id}}-{{$op->id}}"  disabled id="defaultCheck"> -->
                                                    <label class="form-check-label" for="defaultCheck1">
                                                        {{$op->option_title}}
                                                    </label>
                                                </div>
                                            @endif

                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                @else
                                    
                                <div class="col-md-6">
                                    <div class="inner-form-box">
                                        <p class="top-heading">{{$ques->title}}</p>
                                        <div class="form-wrap">
                                            @foreach($options as $op)
                                        
                                            <div class="form-check">
                                                <input class="form-check-input" name="selected_options[]" type="checkbox" value="{{$ques->id}}-{{$op->id}}"  id="defaultCheck">
                                                <label class="form-check-label" for="defaultCheck1">
                                                    {{$op->option_title}}
                                                </label>
                                            </div>

                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                @endif

                                @endforeach
                        </div>
                    
                    <br/>
                    <div id="report_detail_cont">
                        <h5 style="text-align: center;">Coach Feedback</h5>
                        <p class="report-2-cont">{{isset($report->feedback) ? $report->feedback : ''}}</p>
                    </div>
                    <br/>

                    <!-- <div id="report_detail_cont">
                        <h5 style="text-align: center;">Test Data For That Course</h5>
                        <p class="report-2-cont">{{isset($report->test_score_data) ? $report->test_score_data : ''}}</p>
                    </div> -->
                    </br/>
                    @endif

                    @php 
                        $season_id = $report->season_id;
                        $user_id = $report->player_id;
                        $course_id = $report->course_id;
                    @endphp
                    @if(!empty($season_id) && !empty($user_id) && !empty($course_id))
                    @php 
                        $test_score = DB::table('test_scores')->where('test_cat_id','!=',NULL)->where('season_id',$season_id)->where('user_id',$user_id)->where('course_id',$course_id)->get();
                    @endphp
                    <br/>

                    @if(count($test_score)>0)
                    <div class="pink-heading">
                        <h2>Test Score Data</h2>
                    </div>
                    <!-- <label>Test Score Data:</label> -->
                    <div class="table-layout">
                        <table class="table table-bordered rp_test_score">
                            <thead>
                              <tr>
                                  @if(count($test_score)> 0)
                                    <th class="rp_player_name" rowspan="2">Player Name</th>
                                  @endif

                                  @if(count($test_score)> 0)
                                  @foreach($test_score as $arr)
                                    <th>@php echo getTestCatname($arr->test_cat_id); @endphp</th>
                                  @endforeach
                                  @endif
                                  </tr>

                                  <tr>
                                  @if(count($test_score)> 0)
                                  @foreach($test_score as $arr)
                                    <th>@php echo getTestname($arr->test_id); @endphp</th>
                                  @endforeach
                                  @endif
                              </tr>
                            </thead>

                            <tbody>

                            @php
                              $test_score1 = DB::table('test_scores')->where('course_id',$course_id)->where('test_cat_id','!=',NULL)->where('season_id',$season_id)->where('user_id',$user_id)->groupBy('user_id')->get(); 
                            @endphp
                            @foreach($test_score1 as $arr)
                            <tr>
                                <td>@php $user = DB::table('users')->where('id',$arr->user_id)->first(); @endphp {{$user->name}}</td>
                                @php
                                  $test_score12 = DB::table('test_scores')->where('user_id',$user->id)->where('course_id',$course_id)->where('test_cat_id','!=',NULL)->where('season_id',$season_id)->groupBy('test_id')->get(); 
                                @endphp
                                @foreach($test_score12 as $arr)
                                    <td>{{$arr->test_score}}</td>
                                @endforeach
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @endif

                   <!--  <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <button type="submit" class="cstm-btn d-print-none">submit Report</button>
                            </div>
                        </div>
                    </div> -->

                    @if($report->type == 'simple')
                    <!-- <div class="m-b-table">
                        <table class="table table-bordered  cst-reports">                            
                            <div class="pink-heading">
                                <h2>Player Report Data</h2>
                            </div>
                            <tbody>
                                @php
                                $selected_options = json_decode($report->selected_options);
                                @endphp
                                @foreach($selected_options as $op)
                                @php
                                $op = explode('-',$op);
                                $cat = $op[0];
                                $option = $op[1];
                                @endphp
                                <tr>
                                    <th>
                                        <p><b style="color:#35486b">@php echo getReportCategoryName($cat); @endphp</b></p>
                                    </th>
                                    <td>
                                        <p>@php echo getReportOptionName($option); @endphp</p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> -->
                    @endif
                    
                </div>
                <button onclick="window.print();" style="float:right;" class="cstm-btn d-print-none">Print</button>

            </div>
            @else
            <div class="noData offset-md-4 col-md-4 sorry_msg">
                <div class="no_results">
                    <h3>Sorry, no results</h3>
                    <p>No Booking Found</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection