<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\profileRequest;
use App\Http\Responses\Response;
use App\Models\User\CoverPhoto;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function profile(profileRequest $request){
        ////Check from User and Profile:
        $user=User::find(Auth::id());
        if(!$user){
            return Response::Message('Not Found User',404);

        }
        $profile=UserProfile::find(Auth::id());
        if(!$profile){
            return Response::Message('Something is wrong',403);
        }
        //
        if($request->current_location){
            $profile->current_location=$request->current_location;
        }
        if($request->hometown){
            $profile->hometown=$request->hometown;
        }
        if($request->marital_status){
            $profile->marital_status=$request->marital_status;
        }
        if($request->education){
            $profile->education=$request->education;
        }
        if($request->social_accounts){
            $profile->social_accounts=$request->social_accounts;
        }

        if($profile->save()){
            return Response::Message('Edit Profile successfully',200);
        }else{
            return Response::Message('Something is wrong',403);
        }

    }


    public function deleteMainPhoto($mainPhotId){
        ////check from photo and authenticated user:
        $mainphoto=MainPhoto::find($mainPhotId);
        if(!$mainphoto){
            return Response::Message('Not Found Main Image',404);
        }
        $user=Auth::id();
        if($mainphoto->user_id !== Auth::id()){
            return Response::Message("Unauthorized to delete this story.", 403);
        }
        //
        if($mainphoto->delete()){
            return Response::Message('Delete Photo successfully',200);
        }else{
            return Response::Message('Something is wrong',403);
        }
    }

    public function deleteCoverPhoto($coverPhotId){
        ////check from photo and authenticated user:
        $coverPhoto=CoverPhoto::find($coverPhotId);
        if(!$coverPhoto){
            return Response::Message('Not Found Cover Image',404);
        }
        $user=Auth::id();
        if($coverPhoto->user_id !== Auth::id()){
            return Response::Message("Unauthorized to delete this story.", 403);
        }
        //
        if($coverPhoto->delete()){
            return Response::Message('Delete Photo successfully',200);
        }else{
            return Response::Message('Something is wrong',403);
        }
    }

}
