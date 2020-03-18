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
                    <li class="breadcrumb-item "><a href="javascript:void(0)">View</a></li>
                    <li class="breadcrumb-item "><a href="{{ route($addLink) }}">Add</a></li>
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
                                            
<div class="row">
    <div class="col-md-6"><h5>{{$title}}</h5></div>
                                            
  </div>
</div>
                                        <div class="card-block table-border-style">
                                            <div class="table-responsive">
                                              @include('admin.error_message')
                                                <table id="" class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        
                                                        <!-- @php $i=1; @endphp -->
                                                        <th>Title</th>
                                                        <th width="180px">Action</th>

                                                      
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Privacy Policy Page -->
                                                        <td>Pivacy-Policy Page</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-primary">Action</button><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                                                                <div class="dropdown-menu" role="menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(82px, -64px, 0px); top: 0px; left: 0px; will-change: transform;"><a href="{{url('admin/pages/privacy-policy')}}" class="dropdown-item">Edit</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tbody>


                                                    <tbody>
                                                        <!-- Term & Condition Page -->
                                                        <td>Term & Condition Page</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-primary">Action</button><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                                                                <div class="dropdown-menu" role="menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(82px, -64px, 0px); top: 0px; left: 0px; will-change: transform;"><a href="{{url('admin/pages/terms-and-condition')}}" class="dropdown-item">Edit</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tbody>
                                                </table>
                                            </div>
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
        ajax: '<?= url(route('admin.cms-pages.ajaxData')) ?>',
        columns: [
             { data: 'title', name: 'title' },          
             { data: 'status', name: 'status' },
             { data: 'action', name: 'action' },
        ]
       
    });
});
 
</script>
     
@endsection -->