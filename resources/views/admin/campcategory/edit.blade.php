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
                    <li class="breadcrumb-item "><a href="{{ route($addLink) }}">View</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

       <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <!-- /.card-header -->
       @include('admin.error_message')
 
            <div class="card-body">



<div class="col-md-12">

  <form role="form" method="post" id="venueForm" enctype="multipart/form-data">
                
                   @csrf
                  
                   {{textbox($errors,'Title<span class="cst-upper-star">*</span>','title', $venue->title)}}
                   {{textarea($errors,'Description<span class="cst-upper-star">*</span>','description', $venue->description)}}

                   <!-- {{textarea($errors,'Description - More Text<span class="cst-upper-star">*</span>','description_more', $venue->description_more)}} -->

                    <div class="form-group">
                      <label class="control-label">Image</label>
                      <input type="file" name="image" id="selImage" accept="image/*" onchange="ValidateSingleInput(this, 'image_src')">
                      @if ($errors->has('image'))
                          <div class="error">{{ $errors->first('image') }}</div>
                      @endif
                    </div>

                    <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->image }}" />

                  <!-- Slider Image - Start Here -->
                    <div class="form-group">
                      <label class="control-label">Slider Image - 1</label>
                      <input type="file" name="slider_image1" id="selImage1" accept="image/*" onchange="ValidateSingleInput(this, 'slider_image1')">
                      @if ($errors->has('slider_image1'))
                          <div class="error">{{ $errors->first('slider_image1') }}</div>
                      @endif
                    </div>
                    <!-- <img id="slider_image1" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image1 }}" /> -->

                    @if($venue->slider_image1)
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image1 }}" />
                    @else
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/images').'/default.jpg' }}" />
                    @endif
                    

                    <div class="form-group">
                      <label class="control-label">Slider Image - 2</label>
                      <input type="file" name="slider_image2" id="selImage2" accept="image/*" onchange="ValidateSingleInput(this, 'slider_image2')">
                      @if ($errors->has('slider_image2'))
                          <div class="error">{{ $errors->first('slider_image2') }}</div>
                      @endif
                    </div>
                    <!-- <img id="slider_image2" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image2 }}" /> -->

                    @if($venue->slider_image2)
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image2 }}" />
                    @else
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/images').'/default.jpg' }}" />
                    @endif

                    <div class="form-group">
                      <label class="control-label">Slider Image - 3</label>
                      <input type="file" name="slider_image3" id="selImage3" accept="image/*" onchange="ValidateSingleInput(this, 'slider_image3')">
                      @if ($errors->has('slider_image3'))
                          <div class="error">{{ $errors->first('slider_image3') }}</div>
                      @endif
                    </div>
                    <!-- <img id="slider_image3" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image3 }}" /> -->

                    @if($venue->slider_image3)
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image3 }}" />
                    @else
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/images').'/default.jpg' }}" />
                    @endif

                    <div class="form-group">
                      <label class="control-label">Slider Image - 4</label>
                      <input type="file" name="slider_image4" id="selImage4" accept="image/*" onchange="ValidateSingleInput(this, 'slider_image4')">
                      @if ($errors->has('slider_image4'))
                          <div class="error">{{ $errors->first('slider_image4') }}</div>
                      @endif
                    </div>
                    <!-- <img id="slider_image4" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image4 }}" /> -->

                    @if($venue->slider_image4)
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/uploads').'/'.$venue->slider_image4 }}" />
                    @else
                      <img id="image_src" style="width: 100px; height: 100px;" src="{{ URL::asset('/images').'/default.jpg' }}" />
                    @endif
                  <!-- Slider Image - End Here -->
                  

                <div class="card-footer pl-0">
                  <button type="submit" id="btnVanue" class="btn btn-primary">Submit</button>
                </div>
 </form>


</div>

            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>

 
     
@endsection

@section('scripts')
<script src="{{url('/admin-assets/js/validations/valueValidation.js')}}"></script>
<script src="{{url('/js/validations/imageShow.js')}}"></script>

<script src="{{ asset('js/cke_config.js') }}"></script>
<script type="text/javascript">
   CKEDITOR.replace('description', options);
   CKEDITOR.replace('venue_info', options);
   CKEDITOR.replace('camp_go', options);
   CKEDITOR.replace('day_camp_info', options);
   CKEDITOR.replace('day_camp_sports', options);
   CKEDITOR.replace('times_costs', options);
   CKEDITOR.replace('child_need', options);
   CKEDITOR.replace('reviews', options);
   CKEDITOR.replace('location', options);
</script>
@endsection