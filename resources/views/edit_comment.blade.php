@extends('layouts.app')

@section('content')
    <div class="edit-comment">
        <h1>Edit Comment</h1>
        <form action="{{ route('update_comment', ['gistId' => $gistId, 'commentId' => $comment->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <textarea id="edited_comment_body" name="edited_comment_body" rows="4" required class="form-control">{{ $comment->body }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
@endsection
