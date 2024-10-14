<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Page\Page;
use Illuminate\Support\Facades\Auth;

class PageFollowerController extends Controller
{
    public function followPage($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $user = Auth::user();

            if ($page->followers()->where('user_id', $user->id)->exists()) {
                return Response::Message("Already following this page", 403);
            }

            $page->followers()->attach($user->id);

            $page->increment('followers_count');

            return Response::Message("Followed page successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to follow page", 500);
        }
    }

    public function unfollowPage($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $user = Auth::user();

            if (!$page->followers()->where('user_id', $user->id)->exists()) {
                return Response::Message("Not following this page", 403);
            }

            $page->followers()->detach($user->id);

            $page->decrement('followers_count');

            return Response::Message("Unfollowed page successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to unfollow page", 500);
        }
    }


}
