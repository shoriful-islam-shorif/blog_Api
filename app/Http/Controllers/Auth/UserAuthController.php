<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    public function createUser(Request $request)
    {
       // return $request->all();
        try {
        
            $validateUser=Validator::make($request->all(),[
                'name'=>'required',
                'email'=>'required|email|unique:Users,email',
                'password'=>'required'
            
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>'validation error',
                    'errors'=>$validateUser->errors()
                ],401);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                //'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }
    public function loginUser(Request $request)
    {
       // return $request->all();
        try {
            // $validateUser = Validator::make($request->all(), 
            // [
            //     'email' => 'required|email',
            //     'password' => 'required'
            // ]);

            // if($validateUser->fails()){
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'validation error',
            //         'errors' => $validateUser->errors()
            //     ], 401);
            // }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return $this->apiResponse(
                data: [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'token' => $user->createToken($user->name)->plainTextToken,
                ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    { 
        auth()->user()->tokens()->delete();

        return $this-> apiResponse(null,
            message: 'Successfully logged out',
            status: 'success',
        );
    }
}
