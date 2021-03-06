@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-10">Competition Matches</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url(route('admin_dashboard'))}}"><i class="feather icon-home"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- [ breadcrumb ] end -->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <!-- [ Hover-table ] start -->
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header">
                    <h5>Competition Matches</h5>
                </div>

                <br/>
                <!-- Filter Section - Start -->
                <form action="{{route('admin.matchReports.compList')}}" method="POST" class="cst-selection">
                @csrf
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-4">
                                <input type="text" name="player_name" class="form-control" value="" placeholder="Enter player name">
                            </div>

                            <div class="col-sm-1" style="margin-right:10px;">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>

                            <div class="col-sm-1" style="margin-left:10px">
                                <a href="" onclick="myFunction();" class="btn btn-primary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Filter Section - End -->

                <div class="card-block table-border-style">
                    <div class="table-responsive">
                      @include('admin.error_message')
                        <table class="table table-hover">
                            <thead>
                            <tr> 
                                <th>Player Name</th>
                                <th>Competition Name</th> 
                                <th width="12%">Competition Type</th>
                                <th width="12%">Competition Date</th>
                                <th>Competition Venue</th>
                                <th>Created By</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($competitions)>0)
                            @foreach($competitions as $comp)
                                <tr>
                                    <td>@php echo getUsername($comp->player_id); @endphp</td>
                                    <td>{{$comp->comp_name}}</td>
                                    <td>{{$comp->comp_type}}</td>
                                    <td>{{$comp->comp_date}}</td>
                                    <td>{{$comp->comp_venue}}</td>
                                    <td>
                                        @if(!empty($comp->parent_id)) 
                                            @php echo getUsername($comp->parent_id); @endphp
                                        @elseif(!empty($comp->coach_id)) 
                                            @php echo getUsername($comp->coach_id); @endphp
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Action</button>
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                            </button>

                                            <div class="dropdown-menu" role="menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(82px, -64px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <a href="{{url('/admin/match-reports/competition')}}/{{$comp->id}}" class="dropdown-item">View Report</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @else
                                <tr><td colspan="6"><div class="no_results"><h3>No result found</h3></div></td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    @if(count($competitions)>0)
                        {{ $competitions->render() }}
                    @endif
                </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>

@endsection