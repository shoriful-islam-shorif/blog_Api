<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Support\Str;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
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

            $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
                ->select('blogs.*', 'categories.name as category_name')
                ->orderBy('blogs.created_at', 'desc')
                ->paginate(8);

            return response()->json([
                'status' => true,
                'message' => 'Posts retrieved successfully.',
                'data' => $blogs,
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
                'custom_url' => 'required|string|max:255|unique:blogs,custom_url',
                'category_id' => 'required',
                'content' => 'required',
                'thumbnail' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
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
            ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create the blog post.',
                    'error' => $e->getMessage(),
                    'data' => Null,
                ], 500);
            }
        }

    
    public function show($id){
        $blog = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
        ->select('blogs.*', 'categories.name as category_name')
        ->where('blogs.id', $id)
        ->first();

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found.',
                'data'   =>null,
            ], 404); // Return 404 status if the blog is not found
        }

        return response()->json([
            'status' => true,
            'message' => 'Data retrieved successfully.',
            'data' => $blog,
        ],200);
    }

        public function update(Request $request, Blog $blog)
        {        
            // Validate the request
            $request->validate([
                'title' => 'required',
                'custom_url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blogs', 'custom_url')->ignore($blog->id), // Ensure custom_url is unique excluding the current blog
            ],
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
                'user_id' => Auth::id(),
                'status' => $request->status,
            ];
        
       
        $parsedUrl = parse_url($blog->thumbnail);

        // Use pathinfo to get the filename
        $sliceFileName = pathinfo($parsedUrl['path'], PATHINFO_BASENAME);
        $data['thumbnail'] = $sliceFileName;
        
            // Handle thumbnail image upload
        if ($request->hasFile('thumbnail')) {
            // Delete the old thumbnail if it exists
            if ($blog->thumbnail) {
                File::delete(public_path('post_thumbnails/' . $blog->thumbnail));
            }

            // Get the new thumbnail file
            $file = $request->file('thumbnail');
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // Resize and save the image
            $thumbnail = Image::make($file)->resize(600, 360); // Resize as needed
            $thumbnail->save(public_path('post_thumbnails/' . $filename));

            // Update the data with the new filename
            $data['thumbnail'] = $filename;
        } 
        
            // Update the blog post
            $blog->update($data);
        
            // Generate the full URL to the thumbnail
          //  $thumbnailUrl = $data['thumbnail'] ? asset('post_thumbnails/' . $data['thumbnail']) : null;
        
            return response()->json([
                'status' => true,
                'message' => 'Post updated successfully.',
                'data' => $blog,
               // 'thumbnail_url' => $thumbnailUrl, // Provide the thumbnail URL or null
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
                        'data'    =>null,
                    ], 404);
                }

                $parsedUrl = parse_url($blog->thumbnail);

                // Use pathinfo to get the filename
                $sliceFileName = pathinfo($parsedUrl['path'], PATHINFO_BASENAME);

                if ($blog->thumbnail) {
                    File::delete(public_path('post_thumbnails/' . $sliceFileName));
                }

                $blog->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'blog deleted successfully.',
                    'data'   =>null,
                ], 200);
            }



        // public function toggleStatus($id)
        // { 
        //     $blog = Blog::find($id);

        //     if (!$blog) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Post not found.',
        //         ], 404);
        //     }

        //     $blog->status = $blog->status == 1 ? 0 : 1; // Toggle status
        //     $blog->save();

        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Post status updated successfully.',
        //         'data' => $blog,
        //     ], 200);
        // }

        public function filter_by_category($id=null)
        {

 
            $blogs = Blog::when(!is_null($id), function ($query) use ($id) {
                // If $id is not null, filter blogs by the given category_id
                $query->where('category_id', $id);
            })
            ->with('category') // Load the related category for all blogs
            ->paginate(8);

            return response()->json([
                'status' => true,
                'message' => 'Blog Posts filtered by category.',
                'data' => $blogs,
            ]);
        }


}

