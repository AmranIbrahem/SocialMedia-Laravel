<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\AddCoverImageRequest;
use App\Http\Responses\Response;
use App\Models\User\CoverPhoto;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class CoverPhotoController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Cover photo :
    public function AddCoverImage(AddCoverImageRequest $request)
    {
        $id=Auth::id();
        $checkExpert=User::find($id);
        if($checkExpert){

            $image = CoverPhoto::create([
                'user_id'=>$id,
                'Cover_Image'=>$request->Cover_Image
            ]);

            if ($request->Cover_Image) {
                $destination = time() . $request->Cover_Image->getClientOriginalName();
                $request->Cover_Image->move('images/CoverImage', $destination);
                $image->Cover_Image = $destination;
                $image->Cover_Image = "images/CoverImage/$image->Cover_Image";

            }
            $result = $image->save();
            if ($result) {
                return Response::Photo('Cover Image has been added successfully',$image,200);
            } else {
                return Response::Message("Something went wrong!..!",400);
            }
        }else{
            return Response::Message("User Not Found..!",400);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Cover photo :
    public function GetCoverPhoto()
    {
        $id=Auth::id();
        $user = User::find($id);
        if ($user) {
            $data = $user->getCoverImage()->orderBy('created_at', 'desc')->pluck('Cover_Image')->toArray();

            if (!empty($data)) {
                return Response::Photo("These are all Covers Photos",$data,200);
            } else {
                return Response::Message("There are no Photos to show.",200);
            }
        } else {
            return Response::Message("User Not Found..!",400);
        }
    }




}
