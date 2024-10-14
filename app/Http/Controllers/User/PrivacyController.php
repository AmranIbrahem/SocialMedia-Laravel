<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class PrivacyController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get User Post Interactions:
    public function userPostInteractions()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("User not found", 404);
        }

        $interactions = $user->interactions()->orderBy('created_at', 'desc')->get();

        if ($interactions->isEmpty()) {
            return Response::Message("No interactions found", 200);
        }

        $interactionDetails = $interactions->map(function ($interaction) {
            $postOwner = $interaction->post->user;
            $userName = $interaction->user->FirstName . " " . $interaction->user->LastName;
            $postOwnerName = $postOwner->FirstName . " " . $postOwner->LastName;
            return [
                'message' => $userName . ' reacted on a post by ' . $postOwnerName,
                'post_id' => $interaction->post->id,
                'interaction'=>$interaction->type,
                'file'=>$interaction->post->files
            ];
        });

        return response()->json($interactionDetails, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get User Post Comment:
    public function userPostComments()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("User not found", 404);
        }

        $comments = $user->comments()->orderBy('created_at', 'desc')->get();

        if ($comments->isEmpty()) {
            return Response::Message("No Comment found", 200);
        }

        $commentDetails = $comments->map(function ($comment) {
            $postOwner = $comment->post->user;
            $userName = $comment->user->FirstName . " " . $comment->user->LastName;
            $postOwnerName = $postOwner->FirstName . " " . $postOwner->LastName;
            return [
                'message' => $userName . ' commented on a post by ' . $postOwnerName,
                'post_id' => $comment->post->id,
                'Comment'=>$comment->content
            ];
        });

        return response()->json($commentDetails, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get User Comment Interactions :
    public function userCommentInteractions()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("User not found", 404);
        }

        $commentInteractions = $user->commentInteractions()->orderBy('created_at', 'desc')->get();

        if ($commentInteractions->isEmpty()) {
            return Response::Message("No Comment interactions found", 200);
        }

        $commentDetails = $commentInteractions->map(function ($comment) {
            $userName = $comment->user->FirstName . " " . $comment->user->LastName;
            return [
                'message' => $userName . " reacted $comment->type on a comment by " . $comment->comment->content,
                'comment_id' => $comment->comment->id,
            ];
        });

        return response()->json($commentDetails, 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get User Comment Replies :
    public function userCommentReplies()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("User not found", 404);
        }

        $commentReplies = $user->commentReplies;

        if ($commentReplies->isEmpty()) {
            return Response::Message("No Comment replies found", 200);
        }

        $commentRepliesDetails = $commentReplies->map(function ($comment) {
            $userName = $comment->user->FirstName . " " . $comment->user->LastName;
            return [
                'message' => $userName . ' replay on a Comment '.$comment->comment->content ,
                'comment_id' => $comment->comment->id,
                'replay_Comment'=>$comment->reply
            ];
        });

        return response()->json($commentRepliesDetails, 200);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Blocked Users:
    public function getBlockedUsers()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("User not found", 404);
        }

        $blockedUsers = $user->blockedUsers()->with('blocked')->get();

        if ($blockedUsers->isEmpty()) {
            return Response::Message("No blocked users found", 200);
        }

        $blockedDetails = $blockedUsers->map(function ($block) {
            $blockedUser = $block->blocked;
            $mainImage = MainPhoto::where('user_id', $blockedUser->id)->latest()->first();
            return [
                'blocked_user_id' => $blockedUser->id,
                'blocked_user_name' => $blockedUser->FirstName . " " . $blockedUser->LastName,
                'blocked_user_photo' => $mainImage ? $mainImage->Main_Image : null,
            ];
        });

        return response()->json($blockedDetails, 200);
    }



}

