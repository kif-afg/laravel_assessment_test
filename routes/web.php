<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login/github', [GithubLoginController::class, 'redirectToProvider']);
Route::get('login/github/callback', [GithubLoginController::class, 'handleProviderCallback']);
Route::get('/logout', function () {
    session()->forget('github_user');
    return redirect('/');
})->name('logout');
Route::get('/gists', [GithubLoginController::class, 'getUserGists'])->name('gists');
Route::get('/create_gist', [GithubLoginController::class, 'createGist'])->name('create_gist');
Route::post('/submit_gist', [GithubLoginController::class, 'submitGist'])->name('submit_gist');
Route::get('/gists/{gistId}', [GithubLoginController::class, 'getGistDetails']);
Route::get('/gists/{gistId}/edit', [GithubLoginController::class, 'editGist']);
Route::patch('/gists/{gistId}', [GithubLoginController::class, 'updateGist']);
Route::delete('/gists/{gistId}', [GithubLoginController::class, 'deleteGist']);
Route::post('/gists/{gistId}/comments', [GithubLoginController::class, 'createComment'])->name('create_comment');
Route::get('/gists/{gistId}/comments/{commentId}/edit', [GithubLoginController::class, 'editComment'])->name('edit_comment');
Route::put('/gists/{gistId}/comments/{commentId}', [GithubLoginController::class, 'updateComment'])->name('update_comment');
Route::delete('/gists/{gistId}/comments/{commentId}', [GithubLoginController::class, 'deleteComment'])->name('delete_comment');
Route::put('/gists/{gistId}/star', [GithubLoginController::class, 'starGist']);
Route::delete('/gists/{gistId}/unstar', [GithubLoginController::class, 'unstarGist']);
