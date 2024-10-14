<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\AddInteractionCommentGroupRequest;
use App\Http\Responses\Response;
use App\Models\Group\Group_Post_Comment;
use App\Models\Group\GroupInteractionsComment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupInteractionsCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction Comment {Group}:
    public function AddInteractionCommentGroup(AddInteractionCommentGroupRequest $request, $CommentId)
    {
        try {
            $comment = Group_Post_Comment::findOrFail($CommentId);

            $existingInteraction = GroupInteractionsComment::where('user_id', Auth::id())
                ->where('comment_id', $CommentId)->exists();

            if ($existingInteraction) {
                return Response::Message("You already reacted to this Comment.",403);
            }

            $interaction = GroupInteractionsComment::create([
                "comment_id" => $CommentId,
                "user_id" => Auth::id(),
                "type" => $request->type
            ]);

            $comment->count_of_Interaction += 1;
            $editCountOfInteraction = $comment->save();

            if ($editCountOfInteraction && $interaction) {
                return response()->json(['message' => 'Interaction added successfully', 'interaction' => $interaction], 201);
            } else {
                return Response::Message("Something went wrong!..!",401);
            }
        } catch (ModelNotFoundException $e) {
            return Response::Message("Comment not found",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction Comment {Group}:
    public function updateInteractionCommentGroup(AddInteractionCommentGroupRequest $request,$CommentId)
    {
        ////Check For Comment and user :
        $comment = Group_Post_Comment::find($CommentId);
        if (!$comment) {
            return Response::Message("Comment not found",404);
        }
        $FindInteraction = GroupInteractionsComment::where('user_id', Auth::id())
            ->where('comment_id', $CommentId)->first();
        if (!$FindInteraction) {
            return Response::Message("Not reacted to this post",403);
        }
        //
        $Interaction=$request->type;
        $FindInteraction->type=$Interaction;
        $result=$FindInteraction->save();
        if ($result){
            return response()->json(['message' => 'Interaction Comment Update successfully', 'interaction Comment' => $FindInteraction], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Interaction {Group}:
    public function deleteInteractionCommentGroup($InteractionId){
        ////Check For Interaction Comment and user :
        $FindInteraction = GroupInteractionsComment::find($InteractionId);
        if (!$FindInteraction) {
            return Response::Message("Not reacted to this post",403);
        }
        if($FindInteraction->user_id ==! Auth::id()){
            return Response::Message("Unauthorized action..!",403);
        }

        $comment=Group_Post_Comment::find($FindInteraction->comment_id)->first();
        $result=$FindInteraction->delete();

        $comment->count_of_Interaction-=1;
        $result=$comment->save();

        if ($result){
            return Response::Message("Interaction Comment Delete successfully",200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
}
