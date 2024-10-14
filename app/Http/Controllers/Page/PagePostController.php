<?php

namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserController\createPostRequest;
use App\Http\Responses\Response;
use App\Models\Page\Page;
use App\Models\Page\PagePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagePostController extends Controller
{
    public function createPagePost(createPostRequest $request, $pageId)
    {
        try {
            $page = Page::findOrFail($pageId);

            $user = Auth::user();
            if ($page->owner_id !== $user->id && !$page->admins->contains($user->id)) {
                return Response::Message("Unauthorized action", 403);
            }

            $post = new PagePost();
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
                        // Save the file in a custom directory called 'page_posts_uploads'
                        $path = $file->store('page_posts_uploads', 'public');
                        $files[] = $path;
                    } else {
                        return Response::Message("Invalid file upload", 400);
                    }
                }
            }
            $post->files = json_encode($files);

            if ($post->save()) {
                return response()->json(['message' => 'Post created successfully', 'post' => $post, 'uploaded_files' => $files], 201);
            } else {
                return Response::Message("Something went wrong!..!", 401);
            }
        } catch (\Exception $e) {
            return Response::Message("Failed to create page post", 500);
        }
    }


    public function updatePagePost(createPostRequest $request, $postId)
    {
        try {
            $pagePost = PagePost::findOrFail($postId);

            // Assuming the authenticated user must be the owner or admin of the page
            $user = Auth::user();
            $page = $pagePost->page;
            if ($page->owner_id !== $user->id && !$page->admins->contains($user->id)) {
                return Response::Message("Unauthorized action", 403);
            }

            if ($request->has('Text')) {
                $pagePost->Text = $request->Text;
            }

            $pagePost->save();

            return response()->json(['message' => 'Page post updated successfully', 'pagePost' => $pagePost], 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to update page post", 500);
        }
    }

    public function deletePagePost($postId)
    {
        try {
            $pagePost = PagePost::findOrFail($postId);

            // Assuming the authenticated user must be the owner or admin of the page
            $user = Auth::user();
            $page = $pagePost->page;
            if ($page->owner_id !== $user->id && !$page->admins->contains($user->id)) {
                return Response::Message("Unauthorized action", 403);
            }

            $pagePost->delete();

            return Response::Message("Page post deleted successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to delete page post", 500);
        }
    }


    public function getPagePosts($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $posts = $page->posts()->orderBy('created_at', 'desc')->get();

            $posts = $posts->map(function ($post) {
                $createdAt = Carbon::parse($post->created_at);
                $now = Carbon::now();
                $daysDifference = $createdAt->diffInDays($now);

                if ($daysDifference > 30) {
                    $post->time_since_posted = $createdAt->toDateString();
                } else {
                    $post->time_since_posted = $createdAt->diffForHumans($now);
                }

                return $post;
            });

            return response()->json(['message' => 'Posts retrieved successfully', 'posts' => $posts], 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to retrieve posts", 500);
        }
    }



}
