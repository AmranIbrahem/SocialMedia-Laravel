<?php

namespace App\Http\Controllers\Post;

use App\Events\PostCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\createPostRequest;
use App\Http\Responses\Response;
use App\Models\Post\Posts;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To create Post:
    public function createPost(CreatePostRequest $request)
    {
        $UserId = Auth::id();

        $post = new Posts();
        $post->user_id = $UserId;
        if ($request->Text) {
            $post->Text = $request->Text;
        }

        $files = [];
        if ($request->hasFile('files')) {
            $uploadedFiles = $request->file('files');
            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }

            foreach ($uploadedFiles as $file) {
                if ($file->isValid()) {
                    $path = $file->store('uploads', 'public');
                    $files[] = $path;
                } else {
                    return Response::Message("Invalid file upload",400);
                }
            }
        }

        $post->files = json_encode($files);
        if($post->save()){
            return response()->json(['message' => 'Post created successfully', 'post' => $post, 'uploaded_files' => $files], 201);
        }else{
            return Response::Message("Something went wrong!..!",401);
         }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// To Get All Post for user:
    public function getUserPosts($userId)
    {
        $user = User::find($userId);
        if(!$user){
            return Response::Message("User Not Found",404);
        }

        $posts = $user->posts()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($post) {
                $post->files = json_decode($post->files);
                return $post;
            });

        $posts = $posts->map(function ($post) use ($user) {
            $post->user_name = "$user->FirstName $user->LastName ";
            $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
            $post->user_image =  $mainImage ? $mainImage->Main_Image : null;
            return $post;
        });

        return response()->json(['message' => 'User posts retrieved successfully','posts' => $posts], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////\
    /// Update Post:
    public function updatePost(createPostRequest $request, $postId)
    {
        $post = Posts::find($postId);
        if(!$post){
            return Response::Message("Post Not Found..!",403);
        }
        if ($post->user_id !== Auth::id()) {
            return Response::Message("Unauthorized action..!",403);
        }
        if($request->input('Text')){
            $post->Text = $request->input('Text');
        }

        $result=$post->save();
        if($result){
            return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
        }else{
            return Response::Message("Something went wrong!..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Post :
    public function deletePost($postId)
    {
        $post = Posts::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post Not Found'], 404);
        }

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        if ($post->files) {
            $files = json_decode($post->files);
            foreach ($files as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $result = $post->delete();
        if ($result) {
            return response()->json(['message' => 'Post Deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete post'], 500);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Show Post self and get all thing :
    public function ShowAllThingAboutPost($postId){
        ////Check for Post and User:
        $post = Posts::find($postId);
        if(!$post){
            return Response::Message("Post Not Found..!",403);
        }

        $user=User::find($post->user_id);
        if (!$user){
            return Response::Message("Something went wrong!..!",401);
        }
        //
        $comments = $post->comments()->get();

        $formattedComments = $comments->map(function ($comment) {
            $userId=$comment->user->id;
            $user = User::find($userId);

            $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
            $image =  $mainImage ? $mainImage->Main_Image : null;
            $storyExists = $user->stories()->exists();
            $story=$storyExists? true:null;
            return [
                'comment_id' => $comment->id,
                'comment_text' => $comment->content,
                'user_id' => $comment->user->id,
                'user_name' => $comment->user->FirstName." ".$comment->user->LastName,
                'user_image' =>$image,
                'user_story'=>$story,
                'created_at' => $comment->created_at,
            ];
        });

        $mainImageOwner = $user->getMainImage()->orderBy('created_at', 'desc')->first();
        $imageOwner =  $mainImageOwner ? $mainImageOwner->Main_Image : null;

        return response()->json([
            'OwnerPost'=>$user->FirstName . " " .$user->LastName,
            'MainImageowner'=>$imageOwner,
            'Post'=>$post,
            'comments' => $formattedComments], 200);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Interaction Post :
    public function GetInteractionPost($postId){
        // Check for Post:
        $post = Posts::find($postId);
        if(!$post){
            return response()->json(['message' => 'Post Not Found..!'], 404);
        }

        $interactionTypes = ['like', 'love', 'wow'];
        $interactionCounts = [];

        foreach ($interactionTypes as $type) {
            $interactionCounts[$type] = 0;
        }

        // Get interactions with user details
        $users = $post->interactions()
            ->with('user:id,FirstName,LastName')
            ->get()
            ->map(function ($interaction)  use (&$interactionCounts) {
                $user = $interaction->user;
                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $image = $mainImage ? $mainImage->Main_Image : null;

                if (array_key_exists($interaction->type, $interactionCounts)) {
                    $interactionCounts[$interaction->type]++;
                }

                return [
                    'user_id' => $user->id,
                    'name' => $user->FirstName . " " . $user->LastName,
                    'profile_image' => $image,
                    'interaction_type' => $interaction->type,
                ];
            });

        // Count of interactions
        $countOfInteractions = $post->interactions()->count();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'Count Of Interaction' => $countOfInteractions,
            'interactionCounts' => $interactionCounts,
            'users' => $users
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get users by interaction type for Post:
    public function getUsersByInteractionTypeForPost($posetId, $interactionType)
    {
        $post = Posts::find($posetId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $users = $post->interactions->filter(function ($interaction) use ($interactionType) {
            return $interaction->type === $interactionType;
        })->map(function ($interaction) {
            $user = $interaction->user;
            $mainImage = $user->mainImage ? $user->mainImage->Main_Image : null;
            return [
                'user_id' => $user->id,
                'name' => $user->FirstName . " " . $user->LastName,
                'profile_image' => $mainImage,
                'interaction_type' => $interaction->type,
            ];
        });

        $count = $users->count();

        return response()->json([
            'message' => 'Users retrieved successfully for interaction type: ' . $interactionType,
            'count' => $count,
            'users' => $users,
        ], 200);
    }

}
