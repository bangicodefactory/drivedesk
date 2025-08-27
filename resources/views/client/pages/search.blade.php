@extends('client.layouts.app')
@section('title','Search Results')
@section('content')
<div class="container py-120">
    <h1 class="mb-4">Search Results</h1>
    @if($q)
        <p>Your query: <strong>{{ $q }}</strong></p>
        <p>No results implemented yet.</p>
    @else
        <p>No search query provided.</p>
    @endif
</div>
@endsection
