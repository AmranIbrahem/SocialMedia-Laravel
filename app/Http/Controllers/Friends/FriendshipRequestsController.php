<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Friends\friendship_requests;
use App\Models\Friends\friendships;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class FriendshipRequestsController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///To Send friend requests:
    public function sendRequest($receiver_user_id)
    {
        $sender_user_id=Auth::id();
        $find_user=User::Find($sender_user_id)->first();
        if(!$find_user){
            return Response::Message("Something went wrong!..!",404);
        }
        //This check ($ckeckBreforFromAdd1,$ckeckBreforFromAdd2) in order not to repeat the same friend requests
        //This check ($ckeckBreforFromFriend1,$ckeckBreforFromFriend2) To check if they are really friends
        $ckeckBreforFromAdd1=friendship_requests::where('sender_user_id',$receiver_user_id)
                                                ->where('receiver_user_id',$sender_user_id)->first();
        $ckeckBreforFromAdd2=friendship_requests::where('sender_user_id',$sender_user_id)
                                                ->where('receiver_user_id',$receiver_user_id)->first();
        $ckeckBreforFromFriend1=friendships::where('sender_user_id',$receiver_user_id)
                                                ->where('receiver_user_id',$sender_user_id)->first();
        $ckeckBreforFromFriend2=friendships::where('sender_user_id',$sender_user_id)
                                                ->where('receiver_user_id',$receiver_user_id)->first();
        if($ckeckBreforFromAdd1    || $ckeckBreforFromAdd2 || $receiver_user_id == $sender_user_id ||
           $ckeckBreforFromFriend1 || $ckeckBreforFromFriend2){
            return Response::Message("Something went wrong!..!",400);
        }
        ////
        $friendshipRequest = friendship_requests::create([
            "sender_user_id"=>$receiver_user_id,
            "receiver_user_id"=>$sender_user_id,
        ]);
        if($friendshipRequest){
            //Return a message that the transmission was successful
            return Response::Message("Friendship request sent successfully..!",400);
            ////
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",400);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Show all sent friend requests:
    public function GetAllRequestSend(){
        $checkUse=Auth::id();
        $find_user=User::Find($checkUse)->first();
        if(!$find_user){
            return Response::Message("Something went wrong!..!",404);
        }
        if($find_user){
            $friendshipRequests=$find_user->getallrequsert;
            if(count($friendshipRequests) != 0) {
                $account_arr=array();
                foreach ($friendshipRequests as $value){
                    $id_User_Requests=$value->receiver_user_id;
                    $name_Requsets=User::find($id_User_Requests);
                    //This check is for the return of the last picture
                    $find_main_image=MainPhoto::where('user_id',$id_User_Requests)
                                              ->latest()->take(1)->Pluck('Main_Image');
                    ////
                    array_push($account_arr , array(
                        "User_Requests_id"=>$id_User_Requests,
                        "Full_Name"=>"$name_Requsets->FirstName $name_Requsets->LastName",
                        "Main_Image"=>$find_main_image,
                    ));
                }
                return response()->json([
                    "All Requests"=>$account_arr
                ],200);
            }else{
                //Return a message that there are no requests
                return Response::Message("No Friend Requests to show",400);
                ////
            }
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Show all received friend requests:
    public function GetAllRequest($id){
        $checkUse=Auth::id();
        $find_user=User::Find($checkUse)->first();
        if(!$find_user){
            return Response::Message("Something went wrong!..!",404);
        }
        if($find_user){
            $friendshipRequests=$find_user->getallrequsertR;
            if(count($friendshipRequests) != 0) {
                $account_arr=array();
                foreach ($friendshipRequests as $value){
                    $id_User_Requests=$value->sender_user_id;
                    $name_Requsets=User::find($id_User_Requests);
                    //This check is for the return of the last picture
                    $find_main_image=MainPhoto::where('user_id',$id_User_Requests)
                                               ->latest()->take(1)->Pluck('Main_Image');
                    ////
                    array_push($account_arr , array(
                        "User_Requests_id"=>$id_User_Requests,
                        "Full_Name"=>"$name_Requsets->FirstName $name_Requsets->LastName",
                        "Main_Image"=>$find_main_image,
                    ));
                }
                return response()->json([
                    "All Requests"=>$account_arr
                ],200);

            }else{
                //Return a message that there are no requests
                return Response::Message("No Friend Requests to show",400);
                ////
            }
        }else{
            //Return a message that something went wrong
            return Response::Message("Something went wrong!..!",401);
            ////
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

}
