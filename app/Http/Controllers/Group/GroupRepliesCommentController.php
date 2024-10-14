<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\addReplyGroupRequest;
use App\Http\Responses\Response;
use App\Models\Group\Group_Post_Comment;
use App\Models\Group\GroupRepliesComment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupRepliesCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Reply To comment:
    public function addReplyGroup(addReplyGroupRequest $request, $CommentId)
    {
        try {
            $comment = Group_Post_Comment::findOrFail($CommentId);

            $user = Auth::user();
            if (!$user) {
                return Response::Message("User not authenticated.",403);
            }
            if (!$comment->post->group->users->contains($user->id)) {
                return Response::Message("User not a member of the group.",403);
            }

            $replyComment = GroupRepliesComment::create([
                "comment_id" => $CommentId,
                "user_id" => $user->id,
                "reply" => $request->reply,
            ]);

            $comment->count_of_Reply += 1;
            $EditCount = $comment->save();

            if ($replyComment && $EditCount) {
                return response()->json(['message' => 'Reply added successfully', 'reply' => $replyComment], 201);
            } else {
                return Response::Message("Something went wrong!..",401);
            }

        } catch (ModelNotFoundException $e) {
             return Response::Message("Comment not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Reply To comment:
    public function updateReplyGroup(addReplyGroupRequest $request, $replyId)
    {
        try {
            $reply = GroupRepliesComment::findOrFail($replyId);

            $user = Auth::user();
            if (!$user) {
                return Response::Message("User not authenticated.",403);
            }

            if ($reply->user_id !== $user->id) {
                return Response::Message("Unauthorized action.",403);
            }

            if (!$reply->comment->post->group->users->contains($user->id)) {
                return Response::Message("User not a member of the group.",403);
            }

            $reply->reply = $request->reply;
            if($reply->save()){
                return response()->json([
                    'message' => 'Reply updated successfully',
                    'reply' => [
                        'reply_id' => $reply->id,
                        'comment_id' => $reply->comment_id,
                        'user_id' => $reply->user_id,
                        'reply' => $reply->reply,
                        'created_at' => $reply->created_at,
                        'updated_at' => $reply->updated_at,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->FirstName . ' ' . $user->LastName,
                            'image' => $user->getMainImage()->orderBy('created_at', 'desc')->first()->Main_Image ?? null
                        ]
                    ]
                ], 200);
            }else{
                return Response::Message("Something went wrong!..",401);
            }

        } catch (ModelNotFoundException $e) {
             return Response::Message("Reply not found..",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Reply To comment:
    public function deleteReplyGroup($ReplyId)
    {
        try {
            $reply = GroupRepliesComment::findOrFail($ReplyId);

            $user = Auth::user();
            if (!$user) {
                return Response::Message("User not authenticated.",403);
            }

            if ($reply->user_id !== $user->id) {
                return Response::Message("Unauthorized action.",403);
            }

            if (!$reply->comment->post->group->users->contains($user->id)) {
                return Response::Message("User not a member of the group.",403);
            }

            $comment = Group_Post_Comment::findOrFail($reply->comment_id);

            $delete = $reply->delete();

            $comment->count_of_Reply -= 1;
            $EditCount = $comment->save();

            if ($EditCount && $delete) {
                return response()->json(['message' => 'Reply deleted successfully'], 200);
            } else {
                return Response::Message("Something went wrong!..",401);
            }

        } catch (ModelNotFoundException $e) {
             return Response::Message("Reply not found..",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }


}
