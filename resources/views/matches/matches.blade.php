@extends('inc.homelayout')
@section('title', 'DRH|Register')
@section('content')
<div class="account-menu acc_sub_menu">
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
            @include('inc.coach-menu')
            @endif
         </ul>
      </nav>
   </div>
</div>
<!-- Success Message -->
@if(Session::has('success'))               
<div class="alert_msg alert alert-success">
   <p>{{ Session::get('success') }} </p>
</div>
@endif
<section class="member section-padding">
   <div class="container">
      <div class="pink-heading">
         <h2 class="text-left">Competiton Matches&nbsp; </h2><a href="{{url('/user/reports/comp')}}/@php echo base64_encode($comp_id); @endphp"><i style="font-size: 18px; color: #00a0d5; cursor: pointer;" class="fas fa-pen" aria-hidden="true"></i></a>
      </div>
      @php $competition = DB::table('competitions')->where('id',$comp_id)->first(); @endphp
      <div class="player-report-table tbl_shadow matches-wrap">
         <div class="report-table-wrap">
            <div class="m-b-table">
               <table class="matches-wrap-table">
                  <thead>
                     <tr>
                        <th>Player Name</th>
                        <th>Competition Name</th>
                        <th>Competition Type</th>
                        <th>Competition Date</th>
                        <th>Competition Venue</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td>@php echo getUsername($competition->player_id); @endphp</td>
                        <td>{{$competition->comp_name}}</td>
                        <td>{{$competition->comp_type}}</td>
                        <td>{{$competition->comp_date}}</td>
                        <td>{{$competition->comp_venue}}</td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <div class="accordian_summary">
         <div class="card">
            @php $i = 1; @endphp
            @foreach($matches as $match)
            <div class="card-header">
               <a class="collapsed card-link" data-toggle="collapse" href="#Personal-{{$match->id}}">
               <span>{{$i}}</span> {{$match->match_title}}
               </a>
            </div>
            <div id="Personal-{{$match->id}}" class="collapse" >
               <div class="card-body">
                  <div class="report-table-wrap report-tab-sec report-tab-one player_rp_detail matches-dtl">
                     <div class="col-md-12 report_row">
                        <table class="table table-bordered  cst-reports">
                           <tbody>
                              <tr>
                                 <th>
                                    <p><b>Result :</b></p>
                                 </th>
                                 <td>
                                    <h4><strong>{{isset($match->result) ? $match->result : ''}}</strong></h4>
                                 </td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>Score : </b></p>
                                 </th>
                                 <td>{{isset($match->start_date) ? $match->start_date : ''}}</td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>Start Date :</b></p>
                                 </th>
                                 <td>{{isset($match->surface_type) ? $match->surface_type : ''}}</td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>Surface Type :</b></p>
                                 </th>
                                 <td>{{isset($match->surface_type) ? $match->surface_type : ''}}</td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>What went well : </b></p>
                                 </th>
                                 <td>{{isset($match->wht_went_well) ? $match->wht_went_well : ''}}</td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>What could've been better : </b></p>
                                 </th>
                                 <td>{{isset($match->wht_could_better) ? $match->wht_could_better : ''}}</td>
                              </tr>
                              <tr>
                                 <th>
                                    <p><b>Other Comments : </b></p>
                                 </th>
                                 <td>{{isset($match->other_comments) ? $match->other_comments : ''}}</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
            @php $i++; @endphp
            @endforeach
         </div>
      </div>
   </div>
</section>
@endsection