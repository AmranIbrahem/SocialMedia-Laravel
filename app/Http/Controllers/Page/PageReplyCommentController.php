<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\addReplyCommentRequest;
use App\Http\Responses\Response;
use App\Models\Page\PagePostComment;
use App\Models\Page\PageReplyComment;
use Illuminate\Support\Facades\Auth;

class PageReplyCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Reply To comment {Page}:
    public function addReplyCommentPage(addReplyCommentRequest $request, $commentId)
    {
        try {
            //// Check From Found Comment:
            $comment = PagePostComment::find($commentId);
            if (!$comment) {
                return Response::Message("Comment not found..!",404);
            }
            //
            $user = Auth::user();

            $reply = PageReplyComment::create([
                'comment_id' => $commentId,
                'user_id' => $user->id,
                'reply' => $request->reply,
            ]);

            $comment->increment('count_of_Reply');

            return Response::Message("Reply added successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to add reply", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Reply To comment {Page}:
    public function updateReplyCommentPage(addReplyCommentRequest $request, $replyId)
    {
        try {
            //// Check From Reply and User :
            $reply = PageReplyComment::find($replyId);
            if (!$reply) {
                return Response::Message("Reply not found..!",404);
            }
            $user = Auth::user();
            if ($reply->user_id !== $user->id) {
                return Response::Message("Unauthorized action", 403);
            }
            //
            $reply->reply = $request->reply;
            $reply->save();

            return Response::Message("Reply updated successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to update reply", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Reply To comment {Page}:
    public function deleteReplyCoometPage($replyId)
    {
        try {
            //// Check From Reply and User :
            $reply = PageReplyComment::find($replyId);
            if (!$reply) {
                return Response::Message("Reply not found..!",404);
            }
            $user = Auth::user();
            if ($reply->user_id !== $user->id) {
                return Response::Message("Unauthorized action", 403);
            }
            //
            $reply->delete();

            PagePostComment::find($reply->comment_id)->decrement('count_of_Reply');

            return Response::Message("Reply deleted successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to delete reply", 500);
        }
    }
}
