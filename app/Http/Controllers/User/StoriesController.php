<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\User\Stories;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoriesController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Story :
    public function createStory(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);
        ////Check for find User :
        $user = User::find(Auth::id());
        if (!$user) {
            return Response::Message("User Not Found", 404);
        }
        //
        $file = $request->file('file');
        $filePath = $file->store('stories', 'public');
        $fileType = $file->getClientOriginalExtension() == 'mp4' || $file->getClientOriginalExtension() == 'mov' || $file->getClientOriginalExtension() == 'avi' ? 'video' : 'image';

        $story = Stories::create([
            'user_id' => Auth::id(),
            'file_path' => $filePath,
            'file_type' => $fileType,
        ]);

        if($story){
            return response()->json(['message' => 'Story created successfully', 'story' => $story], 201);
        }else{
            return Response::Message("Failed to update story", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Story :
    public function getUserStories($userId): JsonResponse
    {
        ////Check for find User :
        $user = User::find($userId);
        if (!$user) {
            return Response::Message("User Not Found", 404);
        }
        //
        $stories = $user->stories()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($story) {
                $createdAt = Carbon::parse($story->created_at);
                $currentTime = Carbon::now();
                $timeDifference = $createdAt->diffForHumans($currentTime);

                return [
                    'id' => $story->id,
                    'file_path' => "/storage/".$story->file_path,
                    'time_ago' => $timeDifference,
                ];
            });

        return response()->json([
            'message' => 'User stories retrieved successfully',
            'stories' => $stories,
        ],200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Deleted Story :
    public function deleteStory($storyId): JsonResponse
    {
        ////Check for find story and authenticated user :
        $story = Stories::find($storyId);
        if (!$story) {
            return Response::Message("Story Not Found", 404);
        }
        if ($story->user_id !== auth()->id()) {
            return Response::Message("Unauthorized to delete this story.", 403);
        }
        //
        if (Storage::disk('public')->exists($story->file_path)) {
            Storage::disk('public')->delete($story->file_path);
        }

        if($story->delete()){
        return Response::Message("Story deleted successfully.", 200);

        }else{
        return Response::Message("Failed to delete story", 500);

        }
    }




}
