
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Create a New GitHub Gist</h1>
        <form action="{{ route('submit_gist') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="filename">Filename:</label>
                <input type="text" id="filename" name="filename" required class="form-control">
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" class="form-control">
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="public">Public:</label>
                <select id="public" name="public" class="form-control">
                    <option value="1">Public</option>
                    <option value="0">Secret</option>
                </select>
            </div>

            <button type="submit" class="btn">Create Gist</button>
        </form>
    </div>
@endsection
