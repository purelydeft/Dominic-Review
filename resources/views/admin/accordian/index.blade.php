@extends('layouts.admin')

@section('content')

<div class="page-header">
                        <div class="page-block">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="page-header-title">
                                        <h5 class="m-b-10">{{$title}}</h5>
                                    </div>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{url(route('admin_dashboard'))}}"><i class="feather icon-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="{{ url(route($addLink)) }}">Add</a></li>
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

                                            <h5>{{$title}}</h5>
                                            <div class="cst-admin-filter">
                                                <a href="{{ url(route($addLink)) }}" class="btn btn-primary">Add</a>
                                            </div>
                                           
                                            <!-- <span class="d-block m-t-5">use class <code>table-hover</code> inside table element</span> -->
                                        
                                    </div>
                                        <div class="card-block table-border-style">
                                        <div class="table-responsive">
                                          @include('admin.error_message')
                                            <table class="table table-hover">
                                                <thead>
                                                <tr> 
                                                    <th>Sort</th>
                                                    <th>Name</th>
                                                    <!-- <th>Description</th>  -->
                                                    <th>Linked With</th>
                                                    <th>Status</th>
                                                    <th>Action</th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($accordian as $test)
                                                    <tr>
                                                        <td><input type="text" id="update_acc_sort" data-id="{{$test->id}}" value="{{$test->sort}}" style="width:
                                                        50px"></td>
                                                        <td>{{$test->title}}</td>
                                                        <!-- <td>{!! $test->description !!}</td>  -->
                                                        <td>{{$test->page_title}}</td>
                                                        <td>
                                                        @if($test->status == '1')
                                                            <span class="cst_active"><i class="fas fa-check-circle"></i></span>
                                                        @else
                                                            <span class="cst_in-active"><i class="fas fa-times-circle"></i></span>
                                                        @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-primary">Action</button>
                                                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                </button>

                                                                <div class="dropdown-menu" role="menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(82px, -64px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                    <a href="{{url('admin/accordian')}}/{{$test->slug}}" class="dropdown-item">Edit</a>
                                                                    <a href="{{url('admin/accordian/duplicate')}}/{{$test->id}}" class="dropdown-item">Duplicate</a>

                                                                    <a href="{{route('admin.accordian.status',$test->slug)}}" class="dropdown-item">
                                                                        @if($test->status == '1')
                                                                            In-active
                                                                        @else
                                                                            Active
                                                                        @endif
                                                                    </a>

                                                                    <a href="{{url('admin/accordian/delete')}}/{{$test->id}}" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this accordian?')">Delete</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $accordian->render() }}
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>


@endsection

<!-- @section('scripts')
<script type="text/javascript">
 
 
$(function() { 
        var i=1;
    $('#example2').DataTable({
         
        processing: true,
        serverSide: true,
        ajax: '<?= url(route('admin.venues.ajax_getVenues')) ?>',
        columns: [
             { data: 'title', name: 'title' },
             { data: 'description', name: 'description' },            
             { data: 'status', name: 'status' },
             { data: 'action', name: 'action' },
        ]
       
    });


});
 

</script>
     
@endsection -->
