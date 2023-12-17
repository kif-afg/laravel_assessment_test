@extends('layouts.app')

@section('content')
    <div class="gist-details">
        <h1>Gist Details</h1>
        <p><strong>Description:</strong> {{ $gist->description ?: 'No Description' }}</p>
        <p><strong>Public:</strong> {{ $gist->public ? 'Yes' : 'No' }}</p>
        <p><strong>Created At:</strong>
            {{ $gist->createdAt ? date('F j, Y, g:i a', strtotime($gist->createdAt)) : 'Unknown' }}</p>

        {{-- Add star and unstar buttons based on the gist's starred status --}}
        @if ($isStarred)
            {{-- Show the unstar button if the gist is starred --}}
            <form method="POST" action="{{ url("/gists/{$gist->id}/unstar") }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-warning">Unstar</button>
            </form>
        @else
            {{-- Show the star button if the gist is not starred --}}
            <form method="POST" action="{{ url("/gists/{$gist->id}/star") }}">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-primary">Star</button>
            </form>
        @endif

        {{-- Display files details --}}
        @foreach ($gist->files as $filename => $file)
            <div class="file-details">
                <h3>File: {{ $filename }}</h3>
                <pre>{{ $file['content'] ?? 'Content not available' }}</pre>
            </div>
        @endforeach

        {{-- Display comments sorted by updated_at --}}
        <div class="gist-comments">
            <h2>Comments</h2>
            @php
                // Convert the array into a collection
                $commentsCollection = collect($comments);

                // Sort the collection by updated_at in descending order
                $sortedComments = $commentsCollection->sortByDesc('updated_at');
            @endphp
            @if ($sortedComments->isEmpty())
                <p>No comments yet.</p>
            @else
                @foreach ($sortedComments as $comment)
                    <div class="comment" id="comment-{{ $comment['id'] }}">
                        <p class="comment-text">
                            {{ $comment['body'] }}
                            <br>
                            <small>Commented on {{ date('F j, Y, g:i a', strtotime($comment['created_at'])) }}</small>
                            @if ($comment['updated_at'])
                                <small>Updated on {{ date('F j, Y, g:i a', strtotime($comment['updated_at'])) }}</small>
                            @endif
                        </p>
                        <div class="comment-actions">
                            <a href="{{ route('edit_comment', ['gistId' => $gist->id, 'commentId' => $comment['id']]) }}"
                                class="btn btn-link edit-button">Edit</a>
                            <form
                                action="{{ route('delete_comment', ['gistId' => $gist->id, 'commentId' => $comment['id']]) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link delete-button">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Create comments --}}
        <form action="{{ route('create_comment', ['gistId' => $gist->id]) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="comment_body">Comment:</label>
                <textarea id="comment_body" name="comment_body" rows="4" required class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Comment</button>
        </form>
    </div>
@endsection
