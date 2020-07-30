@extends('inc.homelayout')

@section('title', 'DRH|Register')

@section('content')

@endsection

<br/><br/>
<br/><br/>
<section class="my-players section-padding coach_listing">
<div class="container">
<div class="pink-heading">
    <h2>{{ getAllValueWithMeta('coach_listing_heading', 'coach-listing') }}</h2>
</div>

{!! getAllValueWithMeta('coach_listing_desc', 'coach-listing') !!}

<br/><br/>

@if(count($coach)> 0)
    <div class="all-members">
	  <div class="row">

        @foreach($coach as $co)

        @php 
            $coach_id = $co->id;   
            $coach_profile = DB::table('coach_profiles')->where('coach_id',$coach_id)->first(); 
        @endphp
	    <div class="col-lg-3 col-md-6">
			<div class="activity-card text-center">
                <figure class="activity-card-img">
                    @if(!empty($coach_profile))
                        @if($coach_profile->image)
                            <a href="{{url('/coach/detail')}}/@php echo base64_encode($co->id); @endphp"><img src="{{ URL::asset('/uploads').'/'.$coach_profile->image }}"></a>
                        @else
                            <a href="{{url('/coach/detail')}}/@php echo base64_encode($co->id); @endphp"><img src="{{ URL::asset('/images').'/default.jpg' }}"></a>
                        @endif
                    @else
                        <img src="{{ URL::asset('/images').'/default.jpg' }}">
                    @endif
                </figure>
                <figcaption class="activity-caption dsgn-coach-box"> 
                    <h2>{{$co->name}}</h2>
                    @if(!empty($coach_profile))
                        <p>{{$coach_profile->qualified_clubs}}</p>
                    @endif
                    <a href="{{url('/coach/detail')}}/@php echo base64_encode($co->id); @endphp" class="book-now-link">More info</a>    
                </figcaption>
            </div>
		</div>

        @endforeach

        </div>
    </div>

    @else
        <div class="noData offset-md-4 col-md-4 sorry_msg">
            <div class="no_results">
              <h3>Sorry, no results</h3>
              <p>No Coach Found</p>
            </div>
        </div>
    @endif

	  
  </div>
</section>