@extends('layouts.app')

@section('title', 'New Concert')

@section('content')
    <div class="page">
        <div class="container">
            <a class="back-link" href="{{ route('admin.concerts.index') }}"><i class="fas fa-arrow-left"></i> Concerts</a>
            <div class="page-head"><h1>New concert</h1></div>

            <form action="{{ route('admin.concerts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('concerts._form')
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a class="btn btn-ghost" href="{{ route('admin.concerts.index') }}">Cancel</a>
                    <button class="btn btn-accent" type="submit">Publish concert</button>
                </div>
            </form>
        </div>
    </div>
@endsection
