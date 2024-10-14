<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\createPostGroupRequest;
use App\Http\Responses\Response;
use App\Models\Group\Group;
use App\Models\Group\GroupPost;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsGroupController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// update Group Details :
    public function createPostGroup(createPostGroupRequest $request, $groupId)
    {
        $userId = Auth::id();
        try {
            $group = Group::findOrFail($groupId);

            if (!$group->members()->where('user_id', $userId)->exists()) {
                return Response::Message("You are not a member of this group.",403);
            }

            $post = new GroupPost();
            $post->group_id = $groupId;
            $post->user_id = $userId;

            if ($request->text) {
                $post->text = $request->text;
            }

            $files = [];
            if ($request->hasFile('files')) {
                $uploadedFiles = $request->file('files');
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('uploads', 'public');
                        $files[] = $path;
                    } else {
                        return Response::Message("Invalid file upload",400);
                    }
                }
            }

            $post->files = json_encode($files);
            $post->count_of_comment = 0;
            $post->count_of_interaction = 0;

            if ($post->save()) {
                return response()->json(['message' => 'Post created successfully', 'post' => $post, 'uploaded_files' => $files], 201);
            } else {
                return Response::Message("Something went wrong!",400);
            }

        } catch (ModelNotFoundException $e) {
            return Response::Message("Group not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Post:
    public function updatePostGroup(createPostGroupRequest $request, $postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            if ($post->user_id !== Auth::id()) {
                return Response::Message("Unauthorized action.",403);
            }

            if ($request->has('Text')) {
                $post->text = $request->input('Text');
            }

            $result = $post->save();

            if ($result) {
                return Response::Message("Post updated successfully",200);
            } else {
                return Response::Message("Something went wrong. Please try again later.",500);
            }

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Post :
    public function deletePostGroup($postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            if ($post->user_id !== Auth::id()) {
                return Response::Message("Unauthorized action.",401);
            }

            if ($post->files) {
                $files = json_decode($post->files);
                foreach ($files as $file) {
                    Storage::disk('public')->delete($file);
                }
            }

            $result = $post->delete();

            if ($result) {
                return Response::Message("Post deleted successfully.",200);
            } else {
                return Response::Message("Failed to delete post.",500);
            }

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Group Posts  :
    public function getGroupPosts($groupId)
    {
        try {
            $group = Group::findOrFail($groupId);

            $user = Auth::user();
            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group.",403);
            }

            $posts = GroupPost::where('group_id', $groupId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    $post->files = json_decode($post->files);

                    $user = User::find($post->user_id);
                    if ($user) {
                        $post->user_name = "{$user->FirstName} {$user->LastName}";

                        $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                        $post->user_image = $mainImage ? $mainImage->Main_Image : null;
                    } else {
                        $post->user_name = 'Unknown User';
                        $post->user_image = null;
                    }

                    return $post;
                });

            return response()->json($posts, 200);

        } catch (ModelNotFoundException $e) {
            return Response::Message("Group not found.",404);

        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Show Post self and get all thing :
    public function ShowAllThingAboutPost($postId){
        ////Check for Post and User:
        $post = GroupPost::find($postId);
        if(!$post){
            return Response::Message("Post Not Found..!",403);
        }

        $user=User::find($post->user_id);
        if (!$user){
            return Response::Message("Something went wrong!..!",401);
        }
        //
        $comments = $post->comments()->get();

        $formattedComments = $comments->map(function ($comment) {
            $userId=$comment->user->id;
            $user = User::find($userId);

            $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
            $image =  $mainImage ? $mainImage->Main_Image : null;
            $storyExists = $user->stories()->exists();
            $story=$storyExists? true:null;
            return [
                'comment_id' => $comment->id,
                'comment_text' => $comment->content,
                'user_id' => $comment->user->id,
                'user_name' => $comment->user->FirstName." ".$comment->user->LastName,
                'user_image' =>$image,
                'user_story'=>$story,
                'created_at' => $comment->created_at,
            ];
        });

        $mainImageOwner = $user->getMainImage()->orderBy('created_at', 'desc')->first();
        $imageOwner =  $mainImageOwner ? $mainImageOwner->Main_Image : null;

        return response()->json([
            'OwnerPost'=>$user->FirstName . " " .$user->LastName,
            'MainImageOwner'=>$imageOwner,
            'Post'=>$post,
            'comments' => $formattedComments], 200);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Interaction Post :
    public function GetInteractionPostGroup($postId){
        // Check for Post:
        $post = GroupPost::find($postId);
        if(!$post){
            return Response::Message("Post Not Found..!",404);
        }

        $interactionTypes = ['like', 'love', 'wow'];
        $interactionCounts = [];

        foreach ($interactionTypes as $type) {
            $interactionCounts[$type] = 0;
        }

        // Get interactions with user details
        $users = $post->interactions()
            ->with('user:id,FirstName,LastName')
            ->get()
            ->map(function ($interaction)  use (&$interactionCounts) {
                $user = $interaction->user;
                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $image = $mainImage ? $mainImage->Main_Image : null;

                if (array_key_exists($interaction->type, $interactionCounts)) {
                    $interactionCounts[$interaction->type]++;
                }

                return [
                    'user_id' => $user->id,
                    'name' => $user->FirstName . " " . $user->LastName,
                    'profile_image' => $image,
                    'interaction_type' => $interaction->type,
                ];
            });

        // Count of interactions
        $countOfInteractions = $post->interactions()->count();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'Count Of Interaction' => $countOfInteractions,
            'interactionCounts' => $interactionCounts,
            'users' => $users
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get users by interaction type for Post:
    public function getUsersByInteractionTypeForPostGroup($postId, $interactionType)
    {
        $post = GroupPost::find($postId);
        if (!$post) {
            return Response::Message("Post Not Found..!",404);
        }

        $users = $post->interactions->filter(function ($interaction) use ($interactionType) {
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
