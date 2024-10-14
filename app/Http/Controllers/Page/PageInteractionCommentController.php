<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Page\addInteractionPostPageRequest;
use App\Http\Responses\Response;
use App\Models\Page\PageInteractionComment;
use App\Models\Page\PagePostComment;
use Illuminate\Support\Facades\Auth;

class PageInteractionCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction Comment {Page} :
    public function addInteraction(addInteractionPostPageRequest $request, $commentId)
    {
        try {
            $comment = PagePostComment::findOrFail($commentId);
            $user = Auth::user();

            if (PageInteractionComment::where('user_id', $user->id)->where('comment_id', $commentId)->exists()) {
                return Response::Message("You already reacted to this comment", 403);
            }

            $interaction = PageInteractionComment::create([
                'user_id' => $user->id,
                'comment_id' => $commentId,
                'type' => $request->type,
            ]);

            $comment->increment('count_of_Interaction');

            return Response::Message("Interaction added successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to add interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction Comment {Page}:
    public function updateInteraction(addInteractionPostPageRequest $request, $commentId)
    {
        try {
            $user = Auth::user();
            $interaction = PageInteractionComment::where('user_id', $user->id)->where('comment_id', $commentId)->first();

            if (!$interaction) {
                return Response::Message("No interaction found for this comment", 403);
            }

            $interaction->type = $request->type;
            $interaction->save();

            return Response::Message("Interaction updated successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to update interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Interaction Comment {Page}:
    public function deleteInteraction($commentId)
    {
        try {
            $user = Auth::user();
            $interaction = PageInteractionComment::where('user_id', $user->id)->where('comment_id', $commentId)->first();

            if (!$interaction) {
                return Response::Message("No interaction found for this comment", 403);
            }

            $interaction->delete();

            PagePostComment::find($commentId)->decrement('count_of_Interaction');

            return Response::Message("Interaction deleted successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to delete interaction", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

}
