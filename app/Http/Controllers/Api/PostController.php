<?php

namespace App\Http\Controllers\Api;

use App\Models\Post; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{    
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get posts
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }
    
    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Post Success Ditambahkan!', $post);
    }
    // show data
    public function show (Post $post){
        // return single post  as a resource
        return new PostResource(true, 'Data Post Success Ditemukan!', $post);
    }

    // upadte data
    public function update(Request $request, Post $post){
        // define validator rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check if image is not empty
        if ($request->hasFile('image')){

            // up image
            $image = $request->file('image');
            $image->storeAs('public/post', $image->hashName());

            // delete old image
            Storage::delete('public/posts/' .$post->image);

            // up post w new i
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {

            // up data w out image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }
        // return response
        return new PostResource(true, 'Data Success Diubah!', $post);
    }
    public function destroy(Post $post)
    {
        //delete image
        Storage::delete('public/posts/'.$post->image);

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post success Dihapus!', null);
    }
}