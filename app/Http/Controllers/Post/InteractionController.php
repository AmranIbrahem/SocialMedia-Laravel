<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Post\Interaction;
use App\Models\Post\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Interaction :
    public function addInteraction(Request $request,$postId)
    {
        $request->validate([
            'type' => 'required|in:like,love,wow',
        ]);

        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found",404);
        }

        $existingInteraction = Interaction::where('user_id', Auth::id())
                                          ->where('post_id', $postId)->exists();

        if ($existingInteraction) {
            return Response::Message("You already reacted to this post",403);
        }

        $interaction = Interaction::create([
            "post_id"=>$postId,
            "user_id"=>Auth::id(),
            "type"=>$request->type
        ]);

        $InteractionCount = $post->interactions()->count();
        $post->count_of_Interaction=$InteractionCount;
        $result=$post->save();
        if ($interaction && $result){
            return response()->json(['message' => 'Interaction added successfully', 'interaction' => $interaction], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Interaction :

    public function updateInteraction(Request $request,$postId)
    {
        $request->validate([
            'type' => 'required|in:like,love,wow',
        ]);

        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found",404);
        }

        $FindInteraction = Interaction::where('user_id', Auth::id())
            ->where('post_id', $postId)->first();

        if (!$FindInteraction) {
            return Response::Message("Not reacted to this post",403);
        }
        $Interaction=$request->type;
        $FindInteraction->type=$Interaction;
        $result=$FindInteraction->save();
        if ($result){
            return response()->json(['message' => 'Interaction Update successfully', 'interaction' => $FindInteraction], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Interaction :
    public function deleteInteraction($postId){
        $post = Posts::find($postId);
        if (!$post) {
            return Response::Message("Post not found",404);
        }

        $FindInteraction = Interaction::where('user_id', Auth::id())
            ->where('post_id', $postId)->first();

        if (!$FindInteraction) {
            return Response::Message("Not reacted to this post",403);
        }

        $result=$FindInteraction->delete();

        $InteractionCount = $post->interactions()->count();
        $post->count_of_Interaction=$InteractionCount;
        $result=$post->save();

        if ($result){
            return Response::Message("Interaction Delete successfully",200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////




}
