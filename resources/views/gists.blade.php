@extends('layouts.app')

@section('content')
<div class="container">

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2>All Gists</h2>
    <ul class="gist-list">
        @forelse ($gists as $gist)
            <li>
                <a href="{{ url('/gists/' . $gist->id) }}">{{ $gist->description ?: 'No Description' }}</a>
                <div class="gist-actions">
                    <a href="{{ url('/gists/' . $gist->id . '/edit') }}" class="btn btn-primary">Edit</a>
                    <form action="{{ url('/gists/' . $gist->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </li>
        @empty
            <p>No gists found.</p>
        @endforelse
    </ul>

    <h2>Starred Gists</h2>
    <ul class="starred-gist-list">
        @forelse ($starredGists as $gist)
            <li>
                <a href="{{ url('/gists/' . $gist->id) }}">{{ $gist->description ?: 'No Description' }}</a>
            </li>
        @empty
            <p>No starred gists found.</p>
        @endforelse
    </ul>
</div>
@endsection
