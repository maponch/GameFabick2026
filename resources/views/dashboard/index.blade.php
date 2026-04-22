@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div id="app"></div>
<script>
    localStorage.setItem('user', @json($user));
</script>

@endsection