<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\AddMainImageRequest;
use App\Http\Responses\Response;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class MainPhotoController extends Controller
{
    public function AddMainImage(AddMainImageRequest $request)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Add Main photo :
        $id=Auth::id();
        $checkExpert=User::find($id);
        if($checkExpert){

            $image = MainPhoto::create([
                'user_id'=>$id,
                'Main_Image'=>$request->Main_Image
            ]);
            if ($request->Main_Image) {
                $destination = time() . $request->Main_Image->getClientOriginalName();
                $request->Main_Image->move('images/MainImage', $destination);
                $image->Main_Image = $destination;
                $image->Main_Image = "images/MainImage/$image->Main_Image";
            }
            $result = $image->save();
            if ($result) {
                return Response::Photo('Main Image has been added successfully',$image,200);
            } else {
                return Response::Message("Something went wrong!..!",400);
            }
        }else{
            return Response::Message("User Not Found..!",400);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Main photo :
    public function GetMainPhoto($id)
    {
        $user = User::find($id);
        if ($user) {
            $data = $user->getMainImage()->orderBy('created_at', 'desc')->pluck('Main_Image')->toArray();

            if (!empty($data)) {
                return Response::Photo("These are all Main Photos",$data,200);
            } else {
                return Response::Message("There are no Photos to show.",200);
            }
        } else {
            return Response::Message("User Not Found..!",400);
        }
    }


}
