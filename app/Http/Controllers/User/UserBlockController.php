<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Friends\friendship_requests;
use App\Models\Friends\friendships;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use App\Models\User\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Assuming these models exist

class UserBlockController extends Controller
{

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Blocke Users:
    public function blockUser(Request $request)
    {
        $request->validate([
            'blocked_id' => 'required|exists:users,id',
        ]);

        $blocker_id = Auth::id();
        $user = User::find($blocker_id);

        if (!$user) {
            return Response::Message("Something went wrong!", 404);
        }

        $blocked_id = $request->input('blocked_id');

        if ($blocker_id == $blocked_id) {
            return Response::Message("You cannot block yourself", 400);
        }

        if (UserBlock::where('blocker_id', $blocker_id)->where('blocked_id', $blocked_id)->exists()) {
            return Response::Message("User already blocked", 200);
        }

        friendships::where(function ($query) use ($blocker_id, $blocked_id) {
            $query->where('sender_user_id', $blocker_id)->where('receiver_user_id', $blocked_id)
                ->orWhere('sender_user_id', $blocked_id)->where('receiver_user_id', $blocker_id);
        })->delete();

        friendship_requests::where(function ($query) use ($blocker_id, $blocked_id) {
            $query->where('sender_user_id', $blocker_id)->where('receiver_user_id', $blocked_id)
                ->orWhere('sender_user_id', $blocked_id)->where('receiver_user_id', $blocker_id);
        })->delete();

        $userBlock = UserBlock::create([
            'blocker_id' => $blocker_id,
            'blocked_id' => $blocked_id,
        ]);

        if($userBlock){
            return response()->json($userBlock, 201);

        }else{
            return Response::Message("Something went wrong", 404);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// UnBlock Users:
    public function unblockUser($blocked_id)
    {
        $blocker_id = Auth::id();

        $user = User::find($blocker_id);
        if (!$user) {
            return Response::Message("Something went wrong", 404);
        }

        $userBlock = UserBlock::where('blocker_id', $blocker_id)->where('blocked_id', $blocked_id)->first();

        if (!$userBlock) {
            return Response::Message("Block record not found", 404);
        }

        if($userBlock->delete()){
            return Response::Message("User unblocked successfully", 200);
        }else{
            return Response::Message("Something went wrong", 404);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Blocked Users:
    public function getBlockedUsers()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return Response::Message("Something went wrong!", 404);
        }

        $blockedUsers = $user->blockedUsers()->with('blocked')->get();

        if ($blockedUsers->isEmpty()) {
            return Response::Message("No blocked users found", 200);
        }

        $blockedDetails = $blockedUsers->map(function ($block) {
            $blockedUser = $block->blocked;
            $mainImage = MainPhoto::where('user_id', $blockedUser->id)->latest()->take(1)->pluck('Main_Image')->first();
            return [
                'block_id'=>$block->id,
                'blocked_user_id' => $blockedUser->id,
                'blocked_user_name' => $blockedUser->FirstName . " " . $blockedUser->LastName,
                'Main_Photo' => $mainImage ? $mainImage : null,
                'blocked_since' => $block->created_at->diffForHumans(),
            ];
        });

        return response()->json($blockedDetails, 200);
    }


}




