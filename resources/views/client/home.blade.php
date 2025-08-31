@extends('client.layouts.app')

@section('title','Home ')

@section('content')
    @include('client.pages.home.hero')
    @include('client.pages.home.pickup')
    @include('client.pages.home.feature-benefit')
    @include('client.pages.home.about')
    @include('client.pages.home.car-rentals')
    @include('client.pages.home.car-service')
    @include('client.pages.home.funfact')
    @include('client.pages.home.popular-cars')
    @include('client.pages.home.testimonials')
    {{-- @include('client.pages.home.gallery') --}}
    @include('client.pages.home.news')
    {{-- @include('client.pages.home.cta-rental') --}}
    @include('client.pages.home.cta-cheap-rental')
@endsection
