<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    public function index()
    {
        $post = Post::with('users')
            ->withCount('comments')
            ->get();

        $post =  $post->reverse()->values();


        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'url' => ['required', 'string','unique:posts'],
            'notation' => 'string',
            'user_id' => ['required', 'exists:users,id'],
            'category_id' => ['exists:categories,id']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad params',
                'error' => $validator->messages(),
            ], 422);
        } else {

            if (Post::where('url', $request->url)->exists()) {

                $post = Post::where('url', $request->url)->get();
                $user = User::find($request->user_id);
                $comment = new Comment();
                $comment->content = $request->content;
                $comment->post_id = $post[0]["id"];
                $comment->username = $user["name"];
                $comment->save();

                return response()->json(['comment on existing post'], 209);
            }
            $post = new Post;
            $post->url = $request->url;
            $post->content = $request->content;
            $post->notation = $request->notation;
            $post->user_id = $request->user_id;

            if ($request->category_id) {
                $post->category_id = $request->category_id;
            } else {
                $post->category_id = 1;
            }

            $post->save();
            return response()->json(["post created"], 201);
        }
    }


    public function show($id)
    {
        $post = Post::find($id);
        if (!empty($post)) {
            return response()->json($post);
        } else {
            return response()->json([
                "message" => "Post not found !!"
            ], 404);
        }
    }


    public function update(Request $request, $id)
    {
        
        if (Post::where('id', $id)->exists()) {

            $validator = Validator::make($request->all(), [
                'content' => 'required|string',
                'url' => ['required', 'string']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Bad params',
                    'error' => $validator->messages(),
                ], 422);
            } else {
                $post = Post::find($id);
                $post->url = $request->url;
                $post->content = $request->content;
                $post->notation = $request->notation;
                if ($request->category_id) {
                    $post->category_id = $request->category_id;
                }


                $post->save();
                return response()->json([
                    "message" => "Post Updated Succefully !"
                ], 200);
            }
        } else {

            return response()->json([
                "message" => "Post Not Found ."
            ], 404);
        }
    }


    public function destroy($id)
    {
        if (Post::where('id', $id)->exists()) {
            $post = Post::find($id);
            $post->delete();

            return response()->json([
                "message" => "Post deleded Succesfully !"
            ], 200);
        } else {
            return response()->json([
                "message" => "Post Not Found ."
            ], 404);
        }
    }
}
