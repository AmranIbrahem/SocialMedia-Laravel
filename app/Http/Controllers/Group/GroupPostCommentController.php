<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Group\Group_Post_Comment;
use App\Models\Group\GroupPost;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupPostCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Create Comment:
    public function createCommentGroup(Request $request, $postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            $user = Auth::user();
            $group = $post->group;
            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group.",403);
            }

            $comment = Group_Post_Comment::create([
                "post_id" => $postId,
                "user_id" => Auth::id(),
                "content" => $request->input('content')
            ]);

            $post->count_of_Comment+=1;
            $post->save();

            return response()->json(['message' => 'Comment created successfully', 'comment' => $comment], 201);
        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///  Get All Comment:
    public function getPostCommentsGroup($postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            $user = Auth::user();
            $group = $post->group;
            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group.",403);
            }

            $comments = $post->comments()->get();

            $formattedComments = $comments->map(function ($comment) {
                $userId = $comment->user->id;
                $user = User::find($userId);

                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $image = $mainImage ? $mainImage->Main_Image : null;
                $storyExists = $user->stories()->exists();
                $story = $storyExists ? true : null;

                return [
                    'comment_id' => $comment->id,
                    'comment_text' => $comment->content,
                    'user_id' => $comment->user->id,
                    'user_name' => $comment->user->FirstName . " " . $comment->user->LastName,
                    'user_image' => $image,
                    'user_story' => $story,
                    'created_at' => $comment->created_at,
                ];
            });

            return response()->json(['message' => 'Comments retrieved successfully', 'comments' => $formattedComments], 200);

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///  Delete Comment:
    public function deleteCommentGroup($commentId)
    {
        try {
            $comment = Group_Post_Comment::findOrFail($commentId);

            if ($comment->user_id !== Auth::id()) {
                return Response::Message("Unauthorized action",403);
            }

            $post = GroupPost::findOrFail($comment->post_id);

            $user = Auth::user();
            $group = $post->group;

            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group.",403);
            }

            $deleted = $comment->delete();

            $post->count_of_Comment -= 1;
            $post->save();

            if ($deleted) {
                 return Response::Message("Comment deleted successfully.",200);
            } else {
                 return Response::Message("Failed to delete comment..",500);
            }

        } catch (ModelNotFoundException $e) {
             return Response::Message("Comment or Post not found",403);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///  Edit Comment:
    public function updateCommentGroup(Request $request, $commentId)
    {
        try {
            $comment = Group_Post_Comment::findOrFail($commentId);

            if ($comment->user_id !== Auth::id()) {
                return Response::Message("Unauthorized action",403);
            }

            $post = GroupPost::findOrFail($comment->post_id);

            $user = Auth::user();
            $group = $post->group;

            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group.",403);
            }

            $comment->content = $request->input('comment');

            $updated = $comment->save();

            if ($updated) {
                return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
            } else {
                 return Response::Message("Failed to update comment..",500);
            }

        } catch (ModelNotFoundException $e) {
             return Response::Message("Comment or Post not found",403);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Reply for Comment {Group}:
    public function GetAllReplyForCommentGroup($CommentId)
    {
        $comment = Group_Post_Comment::find($CommentId);
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
            return Response::Message("No Replies To this comment..",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get All Interaction for Comment {Group}:
    public function getUsersInteractedOnCommentGroup($commentId)
    {
        $comment = Group_Post_Comment::find($commentId);

        if (!$comment) {
            return Response::Message("Comment not found",404);
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
    /// Get evert thing for Comment {Group}:
    public function getEveryThingForCommentGroup($commentId)
    {
        $comment = Group_Post_Comment::find($commentId);

        if (!$comment) {
            return Response::Message("Comment not found",404);
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
    /// Get users by interaction type {Group}:
    public function getUsersByInteractionTypeGroup($commentId, $interactionType)
    {
        $comment = Group_Post_Comment::find($commentId);
        if (!$comment) {
            return Response::Message("Comment not found",404);
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
