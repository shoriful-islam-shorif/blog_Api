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
    public function index()
    {
        
       
        $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->where('blogs.status', 1)
            ->orderby('blogs.id', 'desc')
            ->paginate(8);

        $recentPost = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->where('blogs.status', 1)
            ->orderby('blogs.id', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::all();

        return response()->json([
            'status' => true,
            'message' => 'Data retrieved successfully.',
            'data' => compact('blogs', 'recentPost', 'categories'),
        ]);
    }

    public function single_post_view($id)
    {
        $blogs = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->where('blogs.id', $id)
            ->first();
    
        return response()->json([
            'status' => true,
            'message' => 'Blog Post  retrieved successfully.',
            'data' => $blogs,
        ]);
    }

    public function filter_by_category($id)
    {
        $filtered_posts = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->where('blogs.status', 1)
            ->where('blogs.category_id', $id)
            ->orderby('blogs.id', 'desc')
            ->paginate(8);

        $recentPost = Blog::join('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.name as category_name')
            ->where('blogs.status', 1)
            ->where('blogs.category_id', $id)
            ->orderby('blogs.id', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::all();

        $category_with_post_count= Category::withCount(['blogs' => function ($query) {
            $query->where('status', 1); 
        }])
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Blog Posts filtered by category.',
            'data' => compact('filtered_posts', 'recentPost', 'categories','category_with_post_count',),
        ]);
    }

}
