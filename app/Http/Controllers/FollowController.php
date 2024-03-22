<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    function follUser(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        if ($user->id == auth()->id()) {
            return response()->json([
                'message' => 'You are not allowed to follow yourself',
            ], 422);
        }
        $followed = Follow::where(['follower_id' => auth()->id(), 'following_id' => $user->id])->first();
        if ($followed) {
            return response()->json([
                'message' => 'You are already followed',
                'status' => $followed->is_accepted ? 'following' : 'requsted'
            ], 422);
        }

        $status = $user->is_private ? false : true;

        $follow = Follow::create([
            'follower_id' => auth()->id(),
            'following_id' => $user->id,
            'is_accepted' => $status
        ]);

        return response()->json([
            'message' => 'Follow success',
            'status' => $follow->is_accepted ? 'following' : 'requested',
        ], 200);
    }
    function unfollowUser(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ]);
        }
        $followed = Follow::where(['follower_id' => auth()->id(), 'following_id' => $user->id])->first();
        if (!$followed) {
            return response()->json([
                'message' => 'You are not following the user',
            ], 422);
        }
        if ($followed->delete()) {
            return response()->json([], 204);
        }
    }

    function followingUser(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        $following = Follow::where('follower_id', $user->id)->get();
        $followingId = $following->pluck('following_id');
        $user = User::whereIn('id', $followingId)->get();
        return response()->json([
            'following' => $user
        ], 200);
    }

    function accept(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        $follow = Follow::where(['following_id' => auth()->id(), 'follower_id' => $user->id])->first();
        if (!$follow) {
            return response()->json([
                'message' => 'This user is not following you'
            ], 422);
        }
        if ($follow->is_accepted == true) {
            return response()->json([
                'message' => 'Follow request is already accepted'
            ], 422);
        }
        if ($follow->is_accepted = true) {
            $follow->save();
            return response()->json([
                'message' => 'Follow request accepted'
            ], 200);
        }
    }
    function followersUser(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $followers = Follow::where('following_id', $user->id)->get();
        $followerId = $followers->pluck('follower_id');
        $user = User::whereIn('id', $followerId)->get();
        $user->makeHidden("updated_at");
        return response()->json([
            'followers' => $user
        ], 200);
    }
}
