<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\addReplyCommentRequest;
use App\Http\Responses\Response;
use App\Models\Post\Comments;
use App\Models\Post\ReplyComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class   ReplyCommentController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Reply To comment:
    public function addReply(addReplyCommentRequest $request,$CommentId){
        //// Check From Found Comment:
        $comment = Comments::find($CommentId);
        if (!$comment) {
            return Response::Message("Comment not found..!",404);
        }
        //
        $replyComment = ReplyComment::create([
            "comment_id" => $CommentId,
            "user_id" => Auth::id(),
            "reply" => $request->reply,

        ]);
        $countOfReply=$comment->replies()->count();
        $comment->count_of_Reply=$countOfReply;
        $EditCount=$comment->save();
        if($replyComment && $EditCount) {
            return response()->json(['message' => 'Reply added successfully', 'reply' => $replyComment], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Update Reply To comment:
    public function updateReply(addReplyCommentRequest $request, $replyId)
    {
        //// Check From Reply and User :
        $reply = ReplyComment::find($replyId);
        if (!$reply) {
            return Response::Message("Reply not found..!",404);
        }
        if ($reply->user_id !== Auth::id()) {
            return Response::Message("Unauthorized action",403);
        }
        //
        $reply->reply = $request->reply;
        if($reply->save()){
            return response()->json(['message' => 'Reply updated successfully', 'reply' => $reply], 200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Reply To comment:

    public function deleteReply($ReplyId){
        //// Check From Reply and User :
        $reply = ReplyComment::find($ReplyId);
        if (!$reply) {
            return Response::Message("Reply not found..!",404);
        }
        if ($reply->user_id !== Auth::id()) {
            return Response::Message("Unauthorized action",403);
        }
        //
        $comment = Comments::find($reply->comment_id);

        $delete=$reply->delete();

        $countOfReply=$comment->replies()->count();
        $comment->count_of_Reply=$countOfReply;
        $EditCount=$comment->save();

        if($EditCount && $delete){
            return Response::Message("Reply deleted successfully",200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }


    }




}
