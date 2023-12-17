@extends('layouts.app')

@section('content')
    <h1>Edit Gist</h1>

    <form action="{{ url('/gists/' . $gist['id']) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" id="description" name="description" value="{{ $gist['description'] }}" class="form-control">
        </div>

        @foreach ($gist['files'] as $filename => $file)
            <div class="form-group">
                <label for="filename">Filename:</label>
                <input type="text" id="filename" name="filename" value="{{ $filename }}" class="form-control"
                    readonly>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" rows="10">{{ $file['content'] }}</textarea>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Update Gist</button>
    </form>
@endsection
