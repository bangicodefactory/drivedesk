@extends('client.layouts.app')
@section('title','UI Test Index')
@section('content')
<div class="container py-5">
    <h1>UI Component Test Links</h1>
    <ul class="list-unstyled row g-3">
        @foreach([
            'hero'=>'Hero','pickup'=>'Pickup Form','feature-benefit'=>'Feature Benefit','about'=>'About','car-rentals'=>'Car Rentals','car-service'=>'Car Service','funfact'=>'Fun Fact','popular-cars'=>'Popular Cars','testimonials'=>'Testimonials','gallery'=>'Gallery','news'=>'News','cta-rental'=>'CTA Rental','cta-cheap-rental'=>'CTA Cheap Rental','full'=>'Full Landing'
        ] as $slug=>$label)
            <li class="col-6 col-md-4"><a href="{{ route('ui.test.'.$slug) }}">{{ $label }}</a></li>
        @endforeach
    </ul>
</div>
@endsection
