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
        // public function index()
        // {
        //     $categories = Category::all();

        //     $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
        //         ->select('blogs.*', 'categories.name as category_name')
        //         ->orderBy('blogs.created_at', 'desc')
        //         ->get();

        //         $blogs->each(function ($blog) {
        //             // Assuming the 'thumbnail' field contains the filename
        //             if ($blog->thumbnail) {
        //                 $blog->thumbnail_url = asset('post_thumbnails/' . $blog->thumbnail);
        //             } else {
        //                 // If there's no thumbnail, you can provide a default image or set it to null
        //                 $blog->thumbnail_url = null;
        //             }
        //         });

        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Posts retrieved successfully.',
        //         'data' => [
        //             'categories' => $categories,
        //             'Blog_posts' => $blogs,
                    
        //         ],
        //     ], 200);
        // }

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
                'custom_url' => 'nullable|string|max:255|unique:blogs,custom_url',
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

                // Handle the custom URL
        try {
            if ($request->custom_url) {
                // Use the provided custom URL as-is (slug validation ensures safety)
                $data['custom_url'] = $this->generateUniqueCustomUrl($request->custom_url);
            } else {
                // Generate a URL slug from the title
                $data['custom_url'] = $this->generateUniqueCustomUrl(Str::slug($request->title));
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid custom URL provided.',
                'error' => $e->getMessage(),
            ], 400);
        }

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
                ], 500);
            }
        }

        private function generateUniqueCustomUrl($inputUrl)
    {
        // Generate a base slug directly from the input URL
        $baseSlug = Str::slug($inputUrl);

        // Ensure the slug is unique
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (Blog::where('custom_url', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }


        public function update(Request $request, $id)
        {
            // Find the blog post
            $blog = Blog::find($id);
            if (!$blog) {
                return response()->json([
                    'status' => false,
                    'message' => 'Post not found.',
                ], 404);
            }
        
            // Validate the request
            $request->validate([
                'title' => 'required',
                'custom_url' => [
                'nullable',
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
                'category_id' => $request->category_id,
                'content' => $request->content,
                'user_id' => Auth::id(),
                'status' => $request->status,
            ];
        
            // Handle the custom URL
        if ($request->custom_url) {
            // Generate a unique custom URL
            $data['custom_url'] = $this->generateUniqueCustomUrlUpdate($request->custom_url, $blog->id);
        } else {
            // Generate a slug from the title if custom URL is not provided
            $data['custom_url'] = $this->generateUniqueCustomUrlUpdate($request->title, $blog->id);
        }
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
        
        private function generateUniqueCustomUrlUpdate($customUrl, $currentId = null)
            {
                // Use the input as a base slug directly
                $baseSlug = Str::slug($customUrl);
                $uniqueSlug = $baseSlug;
                $counter = 1;

                // Check uniqueness, excluding the current blog ID if provided
                while (
                    Blog::where('custom_url', $uniqueSlug)
                        ->when($currentId, fn($query) => $query->where('id', '!=', $currentId))
                        ->exists()
                ) {
                    $uniqueSlug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                return $uniqueSlug;
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

        public function filter_by_category($id=null)
        {

 
            $blogs = Blog::when(!is_null($id), function ($query) use ($id) {
                // If $id is not null, filter blogs by the given category_id
                $query->where('category_id', $id);
            })
            ->with('category') // Load the related category for all blogs
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Blog Posts filtered by category.',
                'data' => $blogs,
            ]);
        }


}

