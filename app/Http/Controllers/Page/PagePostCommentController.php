<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\createCommentRequest;
use App\Http\Responses\Response;
use App\Models\Page\PagePost;
use App\Models\Page\PagePostComment;


use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class PagePostCommentController extends Controller
{
    public function createComment(createCommentRequest $request, $pagePostId)
    {
        try {
            $post = PagePost::find($pagePostId);
            if (!$post) {
                return Response::Message("Post not found", 404);
            }

            $comment = new PagePostComment([
                'page_post_id' => $pagePostId,
                'user_id' => Auth::id(),
                'content' =>$request->input('content')
            ]);

            $comment->save();

            $post->count_of_Comment += 1;
            $post->save();

            return response()->json(['message' => 'Comment added successfully', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            return Response::Message("Failed to add comment", 500);
        }
    }

    public function deleteComment($commentId)
    {
        try {
            $comment = PagePostComment::findOrFail($commentId);

            if ($comment->user_id !== Auth::id()) {
                return Response::Message("Unauthorized action", 403);
            }

            $post = $comment->post;
            $comment->delete();

            $post->count_of_Comment -= 1;
            $post->save();

            return Response::Message("Comment deleted successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to delete comment", 500);
        }
    }


    public function getRecentComments($pagePostId)
    {
        try {
            $comments = PagePost::findOrFail($pagePostId)
                ->comments()
                ->orderBy('created_at', 'desc')
                ->get();

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

            return response()->json(['message' => 'Recent comments retrieved successfully', 'comments' => $formattedComments], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve recent comments', 'error' => $e->getMessage()], 500);
        }
    }



    public function getOldComments($pagePostId)
    {
        try {
            $comments = PagePost::findOrFail($pagePostId)
                ->comments()
                ->orderBy('created_at', 'asc')
                ->get();

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

            return response()->json(['message' => 'Old comments retrieved successfully', 'comments' => $formattedComments], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve old comments', 'error' => $e->getMessage()], 500);
        }
    }


    public function getTopInteractionComments($pagePostId)
    {
        try {
            $comments = PagePost::findOrFail($pagePostId)
                ->comments()
                ->orderBy('count_of_Interaction', 'desc')
                ->get();

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

            return response()->json(['message' => 'Top interaction comments retrieved successfully', 'comments' => $formattedComments], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve top interaction comments', 'error' => $e->getMessage()], 500);
        }
    }



}
