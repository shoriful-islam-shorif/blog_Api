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


class BlogController extends Controller
{
    public function index($category_id)
{
   
    $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
        ->select('blogs.*', 'categories.name as category_name')
        ->where('blogs.status', 1)
        ->where('blogs.category_id',$category_id )
        ->orderby('blogs.created_at', 'desc')
        ->paginate(8); 


    return response()->json([
        'status' => true,
        'message' => 'Data retrieved successfully.',
        'data' => $blogs,
    ]);
}


    public function single_post_view($custom_url)
            {
                    $blog = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
                    ->select('blogs.*', 'categories.name as category_name')
                    ->where('blogs.custom_url', $custom_url)
                    ->first();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data retrieved successfully.',
                        'data' => $blog,
                    ]);
        }
}
