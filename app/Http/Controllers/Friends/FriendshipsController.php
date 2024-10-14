<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\AcceptRequestsFriendRequest;
use App\Http\Responses\Response;
use App\Models\Friends\friendship_requests;
use App\Models\Friends\friendships;
use App\Models\User\MainPhoto;
use App\Models\User\User;

class FriendshipsController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Approve friend requests:
    public function AcceptRequestsFriend(AcceptRequestsFriendRequest $request , $receiver_user_id){
        //This check( $chechFromAdd ) To check if there is a request or not
        $chechFromAdd=friendship_requests::where('sender_user_id',$request->sender_user_id)
                                          ->where('receiver_user_id',$receiver_user_id)->first();
        ////
        if($chechFromAdd){
            $friend = friendships::create([
                "sender_user_id"=>$request->sender_user_id,
                "receiver_user_id"=>$receiver_user_id
            ]);

            if($friend){
                //In order to delete a friend request
                $chechFromAdd->delete();
                ////
                //Return a message that the Accept was successful
                return Response::Message("Friend Accept successfully ",200);
                ////
            }else{
                //Return a message that something went wrong
                return Response::Message("Something went wrong!..!",401);
                ////
            }
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Show all friends according to the time of addition from first to last:
    public function ShowFriendTFL($id){
        $check=User::find($id);
        if($check){
            $friends = friendships::where('sender_user_id', $id)
                ->orWhere('receiver_user_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
            if(count($friends) != 0) {
                $friends_arr = array();
                foreach ($friends as $valus) {
                    //The first condition in order if id is in the first column takes the second column (second condition is opposite)
                    if ($valus->sender_user_id == $id) {
                        $user_id = $valus->receiver_user_id;
                    }
                    if ($valus->receiver_user_id == $id) {
                        $user_id = $valus->sender_user_id;
                    }
                    ////
                    $name_Requsets = User::find($user_id);
                    //This check is for the return of the last picture
                    $find_main_image = MainPhoto::where('user_id', $user_id)
                        ->latest()->take(1)->Pluck('Main_Image');
                    ////

                    array_push($friends_arr, array(
                        "User_id" => $user_id,
                        "Full_Name" => "$name_Requsets->FirstName $name_Requsets->LastName",
                        "Main_Image" => $find_main_image,

                    ));
                }
                //Return a message confirming the return of friends successfully+
                return response()->json([
                    'Friends' => $friends_arr
                ], 200);
                //
            }else{
                //Return a message that there are no friends
                return Response::Message('No Friend To Show',200);
                ////
            }
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Show all friends according to the time of addition from last to first:
    public function ShowFriendTLF($id){
        $check=User::find($id);
        if($check){
            $friends = friendships::where('sender_user_id', $id)
                ->orWhere('receiver_user_id', $id)
                ->orderByDesc('created_at')
                ->get();
            if(count($friends) != 0) {
                $friends_arr = array();
                foreach ($friends as $valus) {
                    //The first condition in order if id is in the first column takes the second column (second condition is opposite)
                    if ($valus->sender_user_id == $id) {
                        $user_id = $valus->receiver_user_id;
                    }
                    if ($valus->receiver_user_id == $id) {
                        $user_id = $valus->sender_user_id;
                    }
                    ////
                    $name_Requsets = User::find($user_id);
                    //This check is for the return of the last picture
                    $find_main_image = MainPhoto::where('user_id', $user_id)
                        ->latest()->take(1)->Pluck('Main_Image');
                    ////

                    array_push($friends_arr, array(
                        "User_id" => $user_id,
                        "Full_Name" => "$name_Requsets->FirstName $name_Requsets->LastName",
                        "Main_Image" => $find_main_image,

                    ));
                }
                //Return a message confirming the return of friends successfully+
                return response()->json([
                    'Friends' => $friends_arr
                ], 200);
                //
            }else{
                //Return a message that there are no friends
                return Response::Message('No Friend To Show',200);
                ////
            }

        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Show ِAll friends in alphabetical order:
    public function ShowFriendN($id){
        $check=User::find($id);
        if($check){
            $friends = User::leftJoin('friendships', function ($join) use ($id) {
                $join->on('users.id', '=', 'friendships.sender_user_id')
                    ->orOn('users.id', '=', 'friendships.receiver_user_id');
            })
                ->where('users.id', '!=', $id)
                ->orderBy('users.FirstName', 'asc')
                ->orderBy('users.LastName', 'asc')
                ->get();

            if(count($friends) != 0) {
                $friends_arr = array();
                $user_id = null;
                $added_users = [];
                foreach ($friends as $valus) {
                    //The first condition in order if id is in the first column takes the second column (second condition is opposite)
                    if ($valus->sender_user_id == $id) {
                        $user_id = $valus->receiver_user_id;
                    }
                    if ($valus->receiver_user_id == $id) {
                        $user_id = $valus->sender_user_id;
                    }
                    ////
                    if ($valus->id == null) {
                        break;
                    }

                    if (in_array($user_id, $added_users)) {
                        continue;
                    }
                    $name_Requsets = User::find($user_id);
                    if ($name_Requsets !== null) {
                        //This check is for the return of the last picture
                        $find_main_image = MainPhoto::where('user_id', $user_id)
                            ->latest()->take(1)->pluck('Main_Image');
                        ////
                        array_push($friends_arr, array(
                            "User_id" => $user_id,
                            "Full_Name" => $name_Requsets->FirstName . ' ' . $name_Requsets->LastName,
                            "Main_Image" => $find_main_image,
                        ));
                    }
                }
                if(count($friends_arr) != 0){
                    //Return a message confirming the return of friends successfully+
                    return response()->json([
                        'Friends' => $friends_arr
                    ], 200);
                        //
                }else{
                    return Response::Message('No Friend To Show',200);}
                    ////
            }else{
                //Return a message that there are no friends
                return Response::Message('No Friend To Show',200);
                ////
            }
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////\
    ///To Show 6 Friend Random:
    public function ShowFriend6Only($id)
    {
        $check = User::find($id);
        if ($check) {
            //This checked in order to fetch 6 random accounts
            $friends = friendships::where('sender_user_id', $id)
                                   ->orWhere('receiver_user_id', $id)
                                   ->orderByRaw('RAND()')
                                   ->take(6)
                                   ->get();
            ////
            if (count($friends) != 0) {
                $friends_arr = array();
                foreach ($friends as $valus) {
                    //The first condition in order if id is in the first column takes the second column (second condition is opposite)
                    if ($valus->sender_user_id == $id) {
                        $user_id = $valus->receiver_user_id;
                    }
                    if ($valus->receiver_user_id == $id) {
                        $user_id = $valus->sender_user_id;
                    }
                    ////
                    $name_Requsets = User::find($user_id);
                    //This check is for the return of the last picture
                    $find_main_image = MainPhoto::where('user_id', $user_id)
                        ->latest()->take(1)->Pluck('Main_Image');
                    ////
                    array_push($friends_arr, array(
                        "User_id" => $user_id,
                        "Full_Name" => "$name_Requsets->FirstName $name_Requsets->LastName",
                        "Main_Image" => $find_main_image,

                    ));
                }
                //Return a message confirming the return of friends successfully+
                return response()->json([
                    'Friends' => $friends_arr
                ], 200);
                //
            } else {
                //Return a message that there are no friends
                return Response::Message('No Friend To Show',200);
                ////
            }
        } else {
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////\



}
