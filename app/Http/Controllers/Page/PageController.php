<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;

use App\Http\Requests\Page\addAdminRequest;
use App\Http\Requests\Page\createPageRequest;
use App\Http\Requests\Page\editPageDetailsMainRequest;
use App\Http\Responses\Response;
use App\Models\Page\Page;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;



class PageController extends Controller
{
    public function createPage(createPageRequest $request)
    {
        $user=Auth::id();
        try {
            $page = Page::create([
                'name' => $request->name,
                'owner_id' => $user,
            ]);

            return response()->json(['message' => 'Page created successfully', 'page' => $page], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create page', 'error' => $e->getMessage()], 500);
        }

    }

    public function getAdmins($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);

            // Ensure that only the owner can view admins
            if ($page->owner_id !== Auth::id()) {
                return Response::Message("Unauthorized action", 403);
            }

            // Decode the JSON admins to array
            $admins = $page->admins ? json_decode($page->admins, true) : [];

            // Ensure $admins is a valid array
            if (!is_array($admins)) {
                return Response::Message("Admins data is corrupted", 500);
            }

            // Get admin user details
            $formattedAdmins = User::whereIn('id', $admins)->get()->map(function ($user) {
                $mainImage = $user->getMainImage()->orderBy('created_at', 'desc')->first();
                $image = $mainImage ? $mainImage->Main_Image : null;
                $storyExists = $user->stories()->exists();
                $story = $storyExists ? true : null;

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->FirstName . " " . $user->LastName,
                    'user_image' => $image,
                    'user_story' => $story,
                ];
            });

            return response()->json(['message' => 'Admins retrieved successfully', 'admins' => $formattedAdmins], 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to retrieve admins", 500);
        }
    }

    public function removeAdmin($pageId, $adminId)
    {
        try {
            $page = Page::findOrFail($pageId);

            // Ensure that only the owner can remove admins
            if ($page->owner_id !== Auth::id()) {
                return Response::Message("Unauthorized action", 403);
            }

            // Decode the JSON admins to array
            $admins = $page->admins ? json_decode($page->admins, true) : [];

            // Ensure $admins is a valid array
            if (!is_array($admins)) {
                return Response::Message("Admins data is corrupted", 500);
            }

            // Check if admin is in the list
            if (!in_array($adminId, $admins)) {
                return Response::Message("Admin not found", 404);
            }

            // Remove the admin
            $admins = array_filter($admins, function ($id) use ($adminId) {
                return $id != $adminId;
            });

            $page->admins = json_encode(array_values($admins));
            $page->save();

            return Response::Message("Admin removed successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to remove admin", 500);
        }
    }


    public function editPageDetailsMain(editPageDetailsMainRequest $request, $id)
    {
        try {
            $page = Page::find($id);

            if ($page->owner_id !== Auth::id()) {
                return Response::Message("Unauthorized action", 403);
            }

            if ($request->has('name')) {
                $page->name = $request->name;
            }
            if ($request->has('bio')) {
                $page->bio = $request->bio;
            }

            $page->save();

            return Response::Message("Page updated successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to update page", 500);
        }
    }

    public function destroyPage($id)
    {
        try {
            $page = Page::findOrFail($id);

            if ($page->owner_id !== Auth::id()) {
                return Response::Message("Unauthorized action", 403);
            }

            $page->delete();

            return Response::Message("Page deleted successfully", 200);
        } catch (\Exception $e) {
            return Response::Message("Failed to delete page", 500);
        }
    }


}
