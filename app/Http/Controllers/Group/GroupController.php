<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\createGroupRequest;
use App\Http\Responses\Response;
use App\Models\Group\Group;
use App\Models\Group\GroupJoinRequest;
use App\Models\User\MainPhoto;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// create Group :
    public function createGroup(createGroupRequest $request)
    {
        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => Auth::id(),
            'admins' => [],
        ]);

        if($group){
            return response()->json($group, 201);
        }else{
            return Response::Message("SomeThing Was Wrong !!",401);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Get Group info :
    public function getGroup($groupId)
    {
        $group = Group::with(['owner:id,FirstName,LastName', 'members'])->findOrFail($groupId);

        $groupData = $group->toArray();
        $owner = $group->owner;
        $mainImage = MainPhoto::where('user_id', $owner->id)->latest()->take(1)->pluck('Main_Image')->first();

        $groupData['owner'] = [
            'id' => $owner->id,
            'name' => $owner->FirstName . " " . $owner->LastName,
            'Main_Photo' => $mainImage,
        ];

        return response()->json($groupData);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// join Group :
    public function sendJoinRequest($groupId)
    {
        $group = Group::findOrFail($groupId);

        if ($group->members()->where('user_id', Auth::id())->exists()) {
            return Response::Message("You are already a member of this group.",401);
        }

        if (GroupJoinRequest::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->exists()) {
            return Response::Message("You have already sent a join request.",400);
        }

        $joinRequest = GroupJoinRequest::create([
            'group_id' => $groupId,
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return response()->json($joinRequest, 201);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// accept Join Request :
    public function acceptJoinRequest($requestId)
    {
        try {
            $joinRequest = GroupJoinRequest::findOrFail($requestId);

            $group = Group::findOrFail($joinRequest->group_id);

            if ($group->members()->where('user_id', $joinRequest->user_id)->exists()) {
                return Response::Message("The user is already a member of this group..",400);
            }

            $group->members()->attach($joinRequest->user_id);

            $joinRequest->delete();

            $group->members_count = $group->members()->count();
            $group->save();

            return Response::Message("Join request accepted and member added to the group",200);
        } catch (ModelNotFoundException $e) {
            return Response::Message("Request or group not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// reject Join Request :
    public function rejectJoinRequest($requestId)
    {
        try {
            $joinRequest = GroupJoinRequest::findOrFail($requestId);

            $group = $joinRequest->group;

            $currentUser = Auth::user();
            if ($group->owner_id != $currentUser->id && !in_array($currentUser->id, $group->admins)) {
                return Response::Message("You are not authorized to reject join requests for this group.",403);
            }

            $joinRequest->delete();
            return Response::Message("Join request rejected.",200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::Message("Join request not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Leave Group :
    public function leaveGroup($groupId)
    {
        try {
            $group = Group::findOrFail($groupId);

            if (!$group->members()->where('user_id', Auth::id())->exists()) {
                return Response::Message("You are not a member of this group.",400);
            }

            $group->members()->detach(Auth::id());

            $group->decrement('members_count');

            return response()->json(['message' => 'Left group successfully.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::Message("Group not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Delete Group :
    public function deleteGroup($groupId)
    {
        try {
            $group = Group::where('id', $groupId)->where('owner_id', Auth::id())->firstOrFail();

            if ($group->delete()) {
                return Response::Message("Group deleted successfully.",200);
            } else {
                return Response::Message("Failed to delete the group. Please try again later.",500);
            }

        } catch (ModelNotFoundException $e) {
            $groupExists = Group::where('id', $groupId)->exists();
            if (!$groupExists) {
                return Response::Message("Group not found.",404);
            } else {
                return Response::Message("You do not have permission to delete this group",403);
            }
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Add Admins :
    public function updateAdmins(Request $request, $groupId)
    {
        $request->validate([
            'admins' => 'required|array',
            'admins.*' => 'exists:users,id',
        ]);

        try {
            $group = Group::where('id', $groupId)->where('owner_id', Auth::id())->firstOrFail();

            $newAdmins = [];
            foreach ($request->admins as $adminId) {
                $user = User::find($adminId);
                if (!$user) {
                    return Response::Message("User with ID ' . $adminId . ' not found.",404);
                }

                if ($adminId == $group->owner_id) {
                    return Response::Message("The owner cannot be added as an admin.",400);
                }
                if (in_array($adminId, $group->admins ?? [])) {
                    return Response::Message("One or more users are already admins.",400);
                }

                $newAdmins[] = $adminId;
            }

            $group->admins = array_merge($group->admins ?? [], $newAdmins);
            $group->save();

            return Response::Message("Group admins updated successfully.",200);

        } catch (ModelNotFoundException $e) {
            $groupExists = Group::where('id', $groupId)->exists();
            if (!$groupExists) {
                return Response::Message("Group not found.",404);
            } else {
                  return Response::Message("You do not have permission to update this group",403);
            }
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Remove Admins :
    public function removeAdmin(Request $request, $groupId, $adminId)
    {
        try {
            $group = Group::where('id', $groupId)->where('owner_id', Auth::id())->firstOrFail();

            if ($adminId == $group->owner_id) {
                return Response::Message("The owner cannot be removed as an admin.",400);
            }

            if (!in_array($adminId, $group->admins ?? [])) {
                return Response::Message("User is not an admin of this group..",400);
                }

            // Remove admin from admins array
            $group->admins = array_values(array_diff($group->admins ?? [], [$adminId]));
            $group->save();

             return Response::Message("'Admin removed successfully.",200);

        } catch (ModelNotFoundException $e) {
            $groupExists = Group::where('id', $groupId)->exists();
            if (!$groupExists) {
                return Response::Message("Group not found.",404);
            } else {
        return Response::Message("You do not have permission to update this group",403);
                }
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Show All Admins :
    public function getAllAdmins($groupId)
    {
        try {
            $group = Group::where('id', $groupId)->firstOrFail();

            $admins = User::whereIn('id', $group->admins)
                ->select('id', 'FirstName', 'LastName')
                ->get()
                ->map(function ($admin) {
                    $mainImage = MainPhoto::where('user_id', $admin->id)
                        ->latest()
                        ->pluck('Main_Image')
                        ->first();
                    $admin->name=$admin->FirstName." ".$admin->LastName;
                    $admin->Main_Photo = $mainImage ? $mainImage : null;
                    return $admin;
                });

            return response()->json($admins);

        } catch (ModelNotFoundException $e) {
            return Response::Message("Group not found.",404);
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// update Group Details :
    public function updateGroupDetails(Request $request, $groupId)
    {
        try {
            $group = Group::where('id', $groupId)->where('owner_id', Auth::id())->firstOrFail();

            $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($request->filled('name')) {
                $group->name = $request->name;
            }

            if ($request->filled('description')) {
                $group->description = $request->description;
            }

            $group->save();

        return Response::Message("YGroup details updated successfully",200);

        } catch (ModelNotFoundException $e) {
            $groupExists = Group::where('id', $groupId)->exists();
            if (!$groupExists) {
                return Response::Message("Group not found.",404);
            } else {
            return Response::Message("You do not have permission to delete this group",403);            }
        } catch (\Exception $e) {
            return Response::Message("An unexpected error occurred. Please try again later.",500);
        }
    }




}
