<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;


class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::all();

        $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->orderBy('blogs.id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Posts retrieved successfully.',
            'data' => [
                'categories' => $categories,
                'Blog_posts' => $blogs,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {      

        $request->validate([
            'title' => 'required',
           'custom_url' => 'nullable|url|max:255',
            'category_id' => 'required',
            'content' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
             'status' => 'required|in:0,1',
        ]);

        $data = [
            'title' => $request->title,
            'custom_url' => $request->custom_url,
            'category_id' => $request->category_id,
            'content' => $request->content,
            //'user_id'=>$request->user_id,
            'user_id' => Auth::id(), 
            'status' => $request->status,
            
        ];
       

       // Handle the thumbnail file upload
    if ($request->hasFile('thumbnail')) {
        $file = $request->file('thumbnail');
        $filename = time() . '.' . $file->getClientOriginalExtension();

        try {
            // Resize and save the image
            $thumbnail = Image::make($file)->resize(600, 360);
            
            $thumbnail->save(public_path('post_thumbnails/' . $filename));
           
            // Store the filename in the database
            $data['thumbnail'] = $filename;
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process and save the thumbnail image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //return $data;
    
    
    try {
        $blog = Blog::create($data);
    
        return response()->json([
            'status' => true,
            'message' => 'Post created successfully.',
            'data' => $blog,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to create the blog post.',
            'error' => $e->getMessage(),
        ], 500);
    }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
       // return $request;

        $blog = Blog::find($id);
        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $request->validate([
            'title' => 'required',
           'custom_url' => 'nullable|url|max:255',
            'category_id' => 'required',
            'content' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'title' => $request->title,
            'custom_url' => $request->custom_url,
            'category_id' => $request->category_id,
            'content' => $request->content,
            //'user_id'=>$request->user_id,
            'user_id' => Auth::id(),
            'status' => $request->status,
        ];

        if ($request->hasFile('thumbnail')) {
            if ($blog->thumbnail) {
                File::delete(public_path('post_thumbnails/' . $blog->thumbnail));
            }

            $file = $request->file('thumbnail');
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // Resize and save the image
            $thumbnail = Image::make($file);
            $thumbnail->resize(600, 360)->save(public_path('post_thumbnails/' . $filename));

            $data['thumbnail'] = $filename;
        }

        $blog->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully.',
            'data' => $blog,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        if ($blog->thumbnail) {
            File::delete(public_path('post_thumbnails/' . $blog->thumbnail));
        }

        $blog->delete();

        return response()->json([
            'status' => true,
            'message' => 'blog deleted successfully.',
        ], 200);
    }



    public function toggleStatus($id)
{ 
    $blog = Blog::find($id);

    if (!$blog) {
        return response()->json([
            'status' => false,
            'message' => 'Post not found.',
        ], 404);
    }

    $blog->status = $blog->status == 1 ? 0 : 1; // Toggle status
    $blog->save();

    return response()->json([
        'status' => true,
        'message' => 'Post status updated successfully.',
        'data' => $blog,
    ], 200);
}
}

