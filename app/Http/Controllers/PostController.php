<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttechments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->mergeIfMissing([
            'size' => $request->input('size', 0),
            'page' => $request->input('page', 10)
        ]);
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:0',
            'page' => 'integer|min:1',
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 200);
        }
        $posts = Post::with('attachments')->get();
        $user = User::make($request->all())->get();
        
        $user->makeHidden("updated_at");
        $posts->makeHidden("user_id");

        $posts->each(function ($posts) {
            $posts->attachments->makeHidden(['post_id', 'created_at', 'updated_at']);
        });

        return response()->json([
            'page' => $request->page,
            'size' => $request->size,
            'posts' => $posts,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'attachments.*' => 'required|image:jpg,jpeg,webp,png,gif',
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 201);
        }
        $post = Post::create([
            'caption' => $request->caption,
            'user_id' => auth()->id()
        ]);
        foreach($request->attachments as $att){
            $attachments = new PostAttechments();
            $attachments->storage_path = $att->store('attachments');
            $attachments->post_id = $post->id;
            $attachments->save();
        }
        return response()->json([
            'message' => 'Create post success'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::where('id', $id)->first();
        if(!$post){
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }
        if($post->user_id != auth()->id()){
            return response()->json([
                'message' => 'Forbidden access'
            ]);
        }
        if($post->delete()){
            return response()->json([
            ], 204);
        }
    }
}
