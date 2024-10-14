<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Page\addInteractionPostPageRequest;
use App\Http\Responses\Response;
use App\Models\Page\PagePost;
use App\Models\Page\PagePostInteraction;
use Illuminate\Support\Facades\Auth;

class PagePostInteractionController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction Post {Page} :
    public function addInteractionPostPage(addInteractionPostPageRequest $request, $postPageId)
    {
        try {
            $user = Auth::user();

            // Check if interaction already exists
            if (PagePostInteraction::where('user_id', $user->id)->where('post_page_id', $postPageId)->exists()) {
                return Response::Message("Already interacted with this post", 403);
            }

            $interaction = PagePostInteraction::create([
                'user_id' => $user->id,
                'post_page_id' => $postPageId,
                'type' => $request->type,
            ]);

            // Increment interaction count on the post
            $post = PagePost::findOrFail($postPageId);
            $post->count_of_Interaction += 1;
            $post->save();

            return Response::Message("Interaction added successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to add interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction Post {Page}:
    public function updateInteractionPostPage(addInteractionPostPageRequest $request, $postPageId)
    {
        try {
            $user = Auth::user();

            $interaction = PagePostInteraction::where('user_id', $user->id)->where('post_page_id', $postPageId)->first();
            if (!$interaction) {
                return Response::Message("No interaction found for this post", 403);
            }

            $interaction->type = $request->type;
            $interaction->save();

            return Response::Message("Interaction updated successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to update interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Interaction Post {Page}:
    public function removeInteractionPostPage($postPageId)
    {
        try {
            $user = Auth::user();

            $interaction = PagePostInteraction::where('user_id', $user->id)->where('post_page_id', $postPageId)->first();
            if (!$interaction) {
                return Response::Message("No interaction found for this post", 403);
            }

            $interaction->delete();

            // Decrement interaction count on the post
            $post = PagePost::findOrFail($postPageId);
            $post->count_of_Interaction -= 1;
            $post->save();

            return Response::Message("Interaction removed successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to remove interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

}
