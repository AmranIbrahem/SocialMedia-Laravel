<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\AddInteractionCommentGroupRequest;
use App\Http\Responses\Response;
use App\Models\Group\GroupPost;
use App\Models\Group\GroupPostInteractions;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupPostInteractionsController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction Group:
    public function addInteractionGroup(AddInteractionCommentGroupRequest $request, $postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            $user = Auth::user();
            $group = $post->group;

            if (!$group->members()->where('users.id', $user->id)->exists()) {
                 return Response::Message("User is not a member of this group.",403);
            }

            $existingInteraction = GroupPostInteractions::where('user_id', Auth::id())
                ->where('post_id', $postId)->exists();

            if ($existingInteraction) {
            return Response::Message("You already reacted to this post.",403);
            }

            $interaction = GroupPostInteractions::create([
                "post_id" => $postId,
                "user_id" => Auth::id(),
                "type" => $request->type
            ]);

            $post->count_of_Interaction += 1;
            $post->save();

            return response()->json(['message' => 'Interaction added successfully', 'interaction' => $interaction], 201);

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid input', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction Group:
    public function updateInteractionGroup(AddInteractionCommentGroupRequest $request, $postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            $user = Auth::user();
            $group = $post->group;

            if (!$group->members()->where('users.id', $user->id)->exists()) {
                return Response::Message("User is not a member of this group",403);
            }

            $FindInteraction = GroupPostInteractions::where('user_id', Auth::id())
                ->where('post_id', $postId)->first();

            if (!$FindInteraction) {
                 return Response::Message("Not reacted to this post.",403);
            }

            $FindInteraction->type = $request->type;
            $result = $FindInteraction->save();

            if ($result) {
                return response()->json(['message' => 'Interaction updated successfully', 'interaction' => $FindInteraction], 200);
            } else {
                return Response::Message("Failed to update interaction.",500);
            }

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid input', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Interaction Group:
    public function deleteInteractionGroup($postId)
    {
        try {
            $post = GroupPost::findOrFail($postId);

            $user = Auth::user();
            $group = $post->group;

            if (!$group->members()->where('users.id', $user->id)->exists()) {
                 return Response::Message("User is not a member of this group.",403);
            }

            $FindInteraction = GroupPostInteractions::where('user_id', Auth::id())
                ->where('post_id', $postId)->first();

            if (!$FindInteraction) {
                 return Response::Message("Not reacted to this post.",403);
            }

            $result = $FindInteraction->delete();

            $post->count_of_Interaction -=1;
            $result = $post->save();

            if ($result) {
                 return Response::Message("Interaction deleted successfully.",200);
            } else {
                 return Response::Message("Failed to delete interaction.",500);
            }

        } catch (ModelNotFoundException $e) {
            return Response::Message("Post not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


}
