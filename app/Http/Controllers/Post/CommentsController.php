<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Create Comment:

    public function createComment(Request $request, $postId)
    {
        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found", 404);
        }

        $comment = Comments::create([
            "post_id" => $postId,
            "user_id" => Auth::id(),
            "content" => $request->input('content')
        ]);
        $commentsCount = $post->comments()->count();
        $post->count_of_Comment = $commentsCount;
        $post->save();

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment], 201);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Comment For Post:
    public function getPostComments($postId)
    {
        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found", 404);
        }
        $comments = $post->comments()->get();

        $formattedComments = $comments->map(function ($comment) {
            $userId = $comment->user->id;
            $user = User::find($userId);

            $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
            $image = $mainImage ? $mainImage->Main_Image : null;
            $storyExists = $user->stories()->exists();
            $story=$storyExists? true:null;
            return [
                'comment_id' => $comment->id,
                'comment_text' => $comment->content,
                'user_id' => $comment->user->id,
                'user_name' => $comment->user->FirstName . " " . $comment->user->LastName,
                'user_image' => $image,
                'user_story'=>$story,
                'created_at' => $comment->created_at,
            ];
        });

        return response()->json(['message' => 'Comments retrieved successfully', 'comments' => $formattedComments], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Count of comment in post :
    public function countComments($postId)
    {
        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found..!", 404);
        }
        $commentsCount = $post->comments()->count();

        return response()->json(['message' => 'Number of comments retrieved successfully', 'count' => $commentsCount], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Comment:
    public function deleteComment($commentId)
    {
        $comment = Comments::find($commentId);
        if (!$comment) {
            return Response::Message("Comment not found..!", 404);
        }
        if ($comment->user_id !== Auth::id()) {
            return Response::Message("Unauthorized action..!", 403);
        }

        $deleted = $comment->delete();
        $post = Posts::find($comment->post_id);
        $commentsCount = $post->comments()->count();
        $post->count_of_Comment = $commentsCount;
        $post->save();

        if ($deleted) {
            return Response::Message("Comment deleted successfully", 200);
        } else {
            return Response::Message("Failed to update comment", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Comment:
    public function updateComment(Request $request, $commentId)
    {
        $comment = Comments::find($commentId);
        if (!$comment) {
            return Response::Message("Comment not found..!", 404);
        }
        if ($comment->user_id !== Auth::id()) {
            return Response::Message("Unauthorized action..!", 403);
        }

        $comment->content = $request->input('comment');

        $updated = $comment->save();

        if ($updated) {
            return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
        } else {
            return Response::Message("Failed to update comment", 500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Reply for Comment:
    public function GetAllReplyForComment($CommentId)
    {
        $comment = Comments::find($CommentId);
        if (!$comment) {
            return Response::Message("Comment not found..!", 404);
        }

        $replies = $comment->replies()->with('user')->get()->map(function ($reply) {
            $user = $reply->user;
            $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
            $image = $mainImage ? $mainImage->Main_Image : null;
            $storyExists = $user->stories()->exists();
            $story=$storyExists? true:null;
            return [
                'reply_id' => $reply->id,
                'comment_id' => $reply->comment_id,
                'user_id' => $reply->user_id,
                'user_name' => $user->FirstName . " " . $user->LastName,
                'user_image' => $image,
                'reply' => $reply->reply,
                'user_story'=>$story,
                'created_at' => $reply->created_at,
            ];
        });
        if (count($replies) > 0) {
            return response()->json([
                'message' => 'This All Replies',
                'Comment' => $comment->content,
                'replies' => $replies], 200);
        } else {
            return Response::Message("No Replies To this comment", 401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Interaction for Comment:
    public function getUsersInteractedOnComment($commentId)
    {
        $comment = Comments::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $users = $comment->interactionComment()
            ->with('user:id,FirstName,LastName')
            ->get()
            ->map(function ($interaction) {
                $user=User::find($interaction->user->id);
                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $image = $mainImage ? $mainImage->Main_Image : null;
                return [
                    'user_id' => $interaction->user->id,
                    'name' => $interaction->user->FirstName." ".$interaction->user->LastName,
                    'profile_image' => $image,
                    'interaction_type' => $interaction->type,
                ];
            });
        return response()->json([
            'message' => 'Users retrieved successfully',
            'Count Of Interaction'=>$comment->interactionComment()->count(),
            'users' => $users], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get evert thing for Comment:
    public function getEveryThingForComment($commentId)
    {
        $comment = Comments::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $interactionTypes = ['like', 'love', 'wow'];
        $interactionCounts = [];

        foreach ($interactionTypes as $type) {
            $interactionCounts[$type] = 0;
        }

        $users = $comment->interactionComment->map(function ($interaction) use (&$interactionCounts) {
            $user = $interaction->user;
            $mainImage = $user->mainImage ? $user->mainImage->Main_Image : null;

            if (array_key_exists($interaction->type, $interactionCounts)) {
                $interactionCounts[$interaction->type]++;
            }

            return [
                'user_id' => $user->id,
                'name' => $user->FirstName . " " . $user->LastName,
                'profile_image' => $mainImage,
                'interaction_type' => $interaction->type,
            ];
        });

        $replies = $comment->replies->map(function ($reply) {
            $user = $reply->user;
            $mainImage = $user->mainImage ? $user->mainImage->Main_Image : null;
            $storyExists = $user->stories()->exists();
            $story=$storyExists? true:null;
            return [
                'reply_id' => $reply->id,
                'comment_id' => $reply->comment_id,
                'user_id' => $reply->user_id,
                'user_name' => $user->FirstName . " " . $user->LastName,
                'user_image' => $mainImage,
                'reply' => $reply->reply,
                'user_story'=>$story,
                'created_at' => $reply->created_at,
            ];
        });

        return response()->json([
            'message' => 'This All thing about comment',
            'Comment_id' => $comment->id,
            'Comment' => $comment->content,
            'CountOfReply' => $comment->replies->count(),
            'usersReply' => $replies,
            'CountOfInteraction' => $comment->interactionComment->count(),
            'interactionCounts' => $interactionCounts,
            'usersInteraction' => $users,
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get users by interaction type :
    public function getUsersByInteractionType($commentId, $interactionType)
    {
        $comment = Comments::find($commentId);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $users = $comment->interactionComment->filter(function ($interaction) use ($interactionType) {
            return $interaction->type === $interactionType;
        })->map(function ($interaction) {
            $user = $interaction->user;
            $mainImage = $user->mainImage ? $user->mainImage->Main_Image : null;
            return [
                'user_id' => $user->id,
                'name' => $user->FirstName . " " . $user->LastName,
                'profile_image' => $mainImage,
                'interaction_type' => $interaction->type,
            ];
        });

        $count = $users->count();

        return response()->json([
            'message' => 'Users retrieved successfully for interaction type: ' . $interactionType,
            'count' => $count,
            'users' => $users,
        ], 200);
    }



}
