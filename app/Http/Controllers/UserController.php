<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function getUser(Request $request)
    {
        $follow = Follow::where('follower_id', auth()->id())->get();
        $followingId = $follow->pluck('following_id')->toArray();

        $followingId[] = auth()->id();

        $users = User::whereNotIn('id', $followingId)->get();

        return response()->json([
            'users' => $users
        ], 200);
    }
    function getDetailUser($username)
    {
        $user = User::where('username', $username)->with('post.attachments')->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->id == auth()->id()) {
            $user->is_your_account = true;
        } else {
            $user->is_your_account = false;
        }
        $follow = Follow::where(['follower_id' => auth()->id(), 'following_id' => $user->id])->first();
        $status = $follow ? ($follow->is_accepted ? 'following' : 'requested') : 'not-followed';

        $user->following_status = $status;
        $user->post_count = Post::where('user_id', $user->id)->count();
        $user->followers_count = Follow::where('following_id', $user->id)->count();
        $user->following_count = Follow::where('follower_id', $user->id)->count();
        return response()->json([
            $user
        ], 200);
    }
}
