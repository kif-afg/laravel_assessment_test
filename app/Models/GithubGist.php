<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GithubGist
{
    public $id;
    public $description;
    public $files;
    public $public;
    public $createdAt;
    public $starred;


    public function __construct($gistData)
    {
        $this->id = $gistData['id'] ?? null;
        $this->description = $gistData['description'] ?? null;
        $this->files = $gistData['files'] ?? [];
        $this->public = $gistData['public'] ?? false;
        $this->createdAt = $gistData['created_at'] ?? null;
        $this->starred = $gistData['starred'] ?? false;
    }

    // Method to prepare data for creating a gist
    public static function prepareDataForApi($description, $filename, $content, $isPublic)
    {
        return [
            'description' => $description,
            'public' => $isPublic,
            'files' => [
                $filename => ['content' => $content]
            ]
        ];
    }

    // Method to prepare data for updating an existing gist
    public static function prepareDataForUpdate($description, $files)
    {
        return [
            'description' => $description,
            'files' => $files
        ];
    }
}
