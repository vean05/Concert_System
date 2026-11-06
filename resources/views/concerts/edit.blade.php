@extends('layouts.app')

@section('title', 'Edit ' . $concert->title)

@section('content')
    <div class="page">
        <div class="container">
            <a class="back-link" href="{{ route('admin.concerts.show', $concert) }}"><i class="fas fa-arrow-left"></i> Back to concert</a>
            <div class="page-head"><h1>Edit concert</h1></div>

            <form action="{{ route('admin.concerts.update', $concert) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('concerts._form', ['concert' => $concert])
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a class="btn btn-ghost" href="{{ route('admin.concerts.show', $concert) }}">Cancel</a>
                    <button class="btn btn-accent" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
