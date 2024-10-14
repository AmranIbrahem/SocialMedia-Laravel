<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\SavedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedPostController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Post to Save List :
    public function savePost(Request $request)
    {
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $user = Auth::user();

        $existingSavedPost = SavedPost::where('user_id', $user->id)
            ->where('post_id', $request->post_id)
            ->first();

        if ($existingSavedPost) {
            return response()->json(['message' => 'Post is already saved'], 409);
        }

        $savedPost = SavedPost::create([
            "user_id" =>  $user->id,
            "post_id" => $request->post_id,
        ]);

        return response()->json($savedPost, 201);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Remove Post to Save List :
    public function removeSavedPost($post_id)
    {
        $user = Auth::user();
        $savedPost = SavedPost::where('user_id', $user->id)->where('post_id', $post_id)->first();

        if (!$savedPost) {
            return response()->json(['message' => 'Saved post not found'], 404);
        }

        $savedPost->delete();

        return response()->json(['message' => 'Saved post removed'], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Post from Save List :
    public function getSavedPosts()
    {
        $user = Auth::user();

        $savedPosts = $user->savedPosts()
            ->with(['post' => function ($query) {
                $query->select('id', 'user_id', 'Text', 'files')
                    ->with('user:id,FirstName,LastName');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($savedPost) {
                $files = json_decode($savedPost->post->files, true);
                if (empty($files)) {
                    $files = null;
                }
                return [
                    'post_id' => $savedPost->post->id,
                    'text' => $savedPost->post->Text,
                    'post_owner' => $savedPost->post->user->FirstName . ' ' . $savedPost->post->user->LastName,
                    'files' => $files,
                    'saved_at' => $savedPost->created_at->diffForHumans(),
                ];
            });

        return response()->json($savedPosts, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////



}
