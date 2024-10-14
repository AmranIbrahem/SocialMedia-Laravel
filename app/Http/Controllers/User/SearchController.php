<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Post\Posts;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use App\Models\User\UserBlock;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///To Search By Name:
    public function SearchByName($name){
        $currentUser = Auth::user();
        $blockedUsers = UserBlock::where('blocker_id', $currentUser->id)->pluck('blocked_id')->toArray();

        $Names = User::whereRaw("concat(FirstName, ' ', LastName) like ?", ["%" . $name . "%"])->get();
        $names_arr = [];

        foreach ($Names as $valus) {
            $idUser = $valus->id;
            if (in_array($idUser, $blockedUsers)) {
                continue;
            }
            $NameUser = "$valus->FirstName $valus->LastName";
            $find_main_image = MainPhoto::where('user_id', $idUser)->latest()->take(1)->pluck('Main_Image');
            array_push($names_arr, [
                "User_id" => $idUser,
                "Name" => $NameUser,
                "Main_Photo" => $find_main_image->first()
            ]);
        }

        if (count($names_arr) >= 1) {
            return response()->json([
                "message" => "Search by name successfully.",
                "data" => $names_arr
            ], 200);
        } else {
            return Response::Message("Nothing found in this search", 401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///To Search By Post:
    public function searchByPost($keyword)
    {
        $currentUser = Auth::user();
        $blockedUsers = UserBlock::where('blocker_id', $currentUser->id)->pluck('blocked_id')->toArray();

        $posts = Posts::where('Text', 'like', "%$keyword%")->get();
        if(!$posts){
            return Response::Message("Something went wrong!..!", 401);
        }

        $data = [];
        foreach ($posts as $post) {
            $user = User::find($post->user_id);
            if ($user && !in_array($user->id, $blockedUsers)) {
                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $data[] = [
                    'Post_id' => $post->id,
                    'Name' => $user->FirstName . ' ' . $user->LastName,
                    'Main_Photo' => $mainImage ? $mainImage->Main_Image : null,
                    "Post" => $post->Text,
                    "File" => $post->files ?? null,
                ];
            }
        }

        if (count($data) > 0) {
            return response()->json([
                'message' => 'Search by post successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No posts found with the given keyword.',
            ], 404);
        }
    }


}
