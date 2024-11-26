<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index()
        {
            $categories = Category::orderby('id', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
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
           // return $request->all();
            $request->validate([
                'name' => 'required',
            ]);
            
    
            $data = [
                'name' => $request->name,
            ];
    
            $category = Category::create($data);
    
            return response()->json([
                'status' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 200);
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
            $category = Category::find($id);
    
            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found',
                ], 404);
            }
    
            $request->validate([
                'name' => 'required|unique:categories,name,' . $category->id . '|max:255',
                //'description' => 'required',
            ]);
    
            $data = [
                'name' => $request->name,
                //'description' => $request->description,
            ];
    
            $category->update($data);
    
            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully',
                'data' => $category,
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
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found',
                ], 404);
            }
    
            $category->delete();
    
            return response()->json([
                'status' => true,
                'message' => 'Category deleted successfully',
            ], 200);
        }
    
}
