@extends('layout.layout')

@php
    $title = 'SMTP';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
@endphp

@section('content')
    <div class="card p-4">
        <h4>Edit Email Template</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('template.update', $template->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ $template->subject }}">
            </div>

            <div class="mb-3">
                <label>Body</label>
                <textarea name="body" id="editor" class="form-control" rows="10">{{ $template->body }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Template</button>
        </form>
    </div>

    {{-- Load CKEditor --}}
    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('editor', {
            height: 250,
            removeButtons: 'PasteFromWord'
        });
    </script>
@endsection
