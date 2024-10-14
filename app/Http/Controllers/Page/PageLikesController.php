<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Page\Page;
use Illuminate\Support\Facades\Auth;

class PageLikesController extends Controller
{
    public function likePage($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $user = Auth::user();

            if ($page->likes()->where('user_id', $user->id)->exists()) {
                return Response::Message("Already liked this page", 403);
            }

            $page->likes()->attach($user->id);
            $page->increment('likes_count');

            return Response::Message("Page liked successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to like page", 500);
        }
    }


    public function unlikePage($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $user = Auth::user();

            if (!$page->likes()->where('user_id', $user->id)->exists()) {
                return Response::Message("Not liked this page", 403);
            }

            $page->likes()->detach($user->id);
            $page->decrement('likes_count');

            return Response::Message("Page unliked successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to unlike page", 500);
        }
    }
}
