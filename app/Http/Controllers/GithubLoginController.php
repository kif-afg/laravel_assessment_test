<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\GithubGist;
use Laravel\Socialite\Facades\Socialite;

class GithubLoginController extends Controller
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function redirectToProvider()
    {
        return Socialite::driver('github')
            ->scopes(['gist'])
            ->redirect();
    }

    public function handleProviderCallback()
    {
        $githubUser = Socialite::driver('github')->user();
        session(['github_user' => $githubUser]);
        session(['github_token' => $githubUser->token]);

        return redirect('/');
    }

    public function getUserGists()
    {
        try {
            // Fetch user's gists
            $gistsData = $this->makeGithubApiRequest('GET', 'https://api.github.com/gists');
            $gists = array_map(function ($gistData) {
                return new GithubGist($gistData);
            }, $gistsData);

            // Fetch starred gists
            $starredGists = $this->getUserStarredGists();

            return view('gists', ['gists' => $gists, 'starredGists' => $starredGists]);
        } catch (\Exception $e) {
            return view('gists', ['gists' => [], 'starredGists' => [], 'error' => $e->getMessage()]);
        }
    }


    public function getGistDetails($gistId)
    {
        try {
            $gistData = $this->makeGithubApiRequest('GET', "https://api.github.com/gists/{$gistId}");
            $commentsData = $this->makeGithubApiRequest('GET', "https://api.github.com/gists/{$gistId}/comments");

            // Check if the gist is starred
            $isStarred = $this->checkGistStarred($gistId);
            $gist = new GithubGist($gistData);

            // Initialize $editingCommentId as null
            $editingCommentId = null;

            return view('gist_detail', ['gist' => $gist, 'comments' => $commentsData, 'editingCommentId' => $editingCommentId, 'isStarred' => $isStarred]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Helper method to check if a gist is starred
    private function checkGistStarred($gistId)
    {
        try {
            $token = session('github_token');
            if (!$token) {
                throw new \Exception('No GitHub token found in session.');
            }

            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/vnd.github.v3+json',
            ];

            $options = ['headers' => $headers];

            // Make the API request
            $response = $this->client->request("GET", "https://api.github.com/gists/{$gistId}/star", $options);

            // If the response status is 204, then the gist is starred
            return $response->getStatusCode() == 204;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // If a ClientException is thrown, check if it's a 404 error (not starred)
            if ($e->getResponse()->getStatusCode() == 404) {
                return false;
            }

            // Re-throw the exception if it's not a 404 error
            throw $e;
        } catch (\Exception $e) {
            // Re-throw any other exceptions
            throw $e;
        }
    }




    public function createGist(Request $request)
    {
        return view('create_gist');
    }

    public function submitGist(Request $request)
    {
        try {
            $gistData = GithubGist::prepareDataForApi(
                $request->input('description'),
                $request->input('filename'),
                $request->input('content'),
                $request->input('public') == '1'
            );

            $this->makeGithubApiRequest('POST', 'https://api.github.com/gists', $gistData);

            return redirect('/gists')->with('success', 'Gist created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while creating the gist: ' . $e->getMessage());
        }
    }

    public function editGist($gistId)
    {
        try {
            $gistData = $this->makeGithubApiRequest('GET', "https://api.github.com/gists/{$gistId}");
            $gistData['public'] = $gistData['public'] ? '1' : '0';

            return view('edit_gist', ['gist' => $gistData]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateGist(Request $request, $gistId)
    {
        try {
            $filesData = [$request->input('filename') => ['content' => $request->input('content')]];
            $updateData = GithubGist::prepareDataForUpdate(
                $request->input('description'),
                $filesData,
                $request->input('public') == '1'
            );

            $this->makeGithubApiRequest('PATCH', "https://api.github.com/gists/{$gistId}", $updateData);

            return redirect('/gists')->with('success', 'Gist updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while updating the gist: ' . $e->getMessage());
        }
    }

    public function deleteGist($gistId)
    {
        try {
            $this->makeGithubApiRequest('DELETE', "https://api.github.com/gists/{$gistId}");

            return redirect('/gists')->with('success', 'Gist deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while deleting the gist: ' . $e->getMessage());
        }
    }

    public function createComment(Request $request, $gistId)
    {
        try {
            $commentData = $this->makeGithubApiRequest('POST', "https://api.github.com/gists/{$gistId}/comments", [
                'body' => $request->input('comment_body')
            ]);

            return redirect("/gists/{$gistId}")->with('success', 'Comment created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function editComment($gistId, $commentId)
    {
        try {
            // Fetch the comment data using the GitHub API and the comment ID
            $commentData = $this->makeGithubApiRequest('GET', "https://api.github.com/gists/{$gistId}/comments/{$commentId}");
            // Pass the comment data to the view as an object
            return view('edit_comment', ['comment' => (object)$commentData, 'gistId' => $gistId]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    public function updateComment(Request $request, $gistId, $commentId)
    {
        try {
            // Update the comment using the GitHub API and the comment ID
            $updatedCommentData = $this->makeGithubApiRequest('PATCH', "https://api.github.com/gists/{$gistId}/comments/{$commentId}", [
                'body' => $request->input('edited_comment_body')
            ]);

            return redirect("/gists/{$gistId}")->with('success', 'Comment updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function makeGithubApiRequest($method, $url, $data = [])
    {
        $token = session('github_token');
        if (!$token) {
            throw new \Exception('No GitHub token found in session.');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.github.v3+json',
        ];

        $options = ['headers' => $headers];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        $response = $this->client->request($method, $url, $options);
        return json_decode((string) $response->getBody(), true);
    }
    public function deleteComment(Request $request, $gistId, $commentId)
    {
        try {
            $this->makeGithubApiRequest('DELETE', "https://api.github.com/gists/{$gistId}/comments/{$commentId}");

            return redirect("/gists/{$gistId}")->with('success', 'Comment deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function starGist($gistId)
    {
        try {
            $this->makeGithubApiRequest('PUT', "https://api.github.com/gists/{$gistId}/star");
            // Set the starred property of the gist to true
            $gist = new GithubGist(['starred' => true]);
            return redirect("/gists/{$gistId}")->with('success', 'Gist starred successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function unstarGist($gistId)
    {
        try {
            $this->makeGithubApiRequest('DELETE', "https://api.github.com/gists/{$gistId}/star");
            // Set the starred property of the gist to false
            $gist = new GithubGist(['starred' => false]);
            return redirect("/gists/{$gistId}")->with('success', 'Gist unstarred successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getUserStarredGists()
    {
        try {
            $starredGistsData = $this->makeGithubApiRequest('GET', 'https://api.github.com/gists/starred');
            $starredGists = array_map(function ($gistData) {
                return new GithubGist($gistData);
            }, $starredGistsData);

            return $starredGists;
        } catch (\Exception $e) {
            return [];
        }
    }
}
