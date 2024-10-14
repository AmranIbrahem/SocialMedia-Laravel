<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Post\Comments;
use App\Models\Post\InteractionComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction Comment :
    public function AddInteractionComment(Request $request,$CommentId){
        $request->validate([
            'type' => 'required|in:like,love,wow',
        ]);

        ////Check For Comment and User is Interaction before:
        $comment = Comments::find($CommentId);
        if (!$comment) {
            return Response::Message("Comment not found",404);
        }

        $existingInteraction = InteractionComment::where('user_id', Auth::id())
                                                 ->where('comment_id', $CommentId)->exists();

        if ($existingInteraction) {
            return Response::Message("You already reacted to this Comment",403);
        }
        //
        $interaction = InteractionComment::create([
            "comment_id"=>$CommentId,
            "user_id"=>Auth::id(),
            "type"=>$request->type
        ]);

        $InteractionCount = $comment->interactionComment()->count();
        $comment->count_of_Interaction=$InteractionCount;
        $editCountOfInteraction=$comment->save();
        if($editCountOfInteraction && $interaction){
            return response()->json(['message' => 'Interaction added successfully', 'interaction' => $interaction], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction Comment:
    public function updateInteractionComment(Request $request,$CommentId)
    {
        $request->validate([
            'type' => 'required|in:like,love,wow',
        ]);
        ////Check For Comment and user :
        $comment = Comments::find($CommentId);
        if (!$comment) {
            return Response::Message("Comment not found",404);
        }
        $FindInteraction = InteractionComment::where('user_id', Auth::id())
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
    /// Delete Interaction :
    public function deleteInteractionComment($InteractionId){
        ////Check For Interaction Comment and user :
        $FindInteraction = InteractionComment::find($InteractionId);
        if (!$FindInteraction) {
            return Response::Message("Not reacted to this post",403);
        }
        if($FindInteraction->user_id ==! Auth::id()){
            return Response::Message("Unauthorized action..!",403);
        }

        $comment=Comments::find($FindInteraction->comment_id)->first();
        $result=$FindInteraction->delete();

        $InteractionCount = $comment->interactionComment()->count();
        $comment->count_of_Interaction=$InteractionCount;
        $result=$comment->save();

        if ($result){
            return Response::Message("Interaction Comment Delete successfully",200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


}
