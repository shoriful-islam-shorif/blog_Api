<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        return $request->all();
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validateUser->fails()) {
            return $this->apiResponse(
                data: ['errors' => $validateUser->errors()],
                message: 'validation error',
                status: 'error',
                statusCode: 401
            );
        }
        if (!Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
            return $this->apiResponse(
                data: [],
                message: 'Email & Password does not match with our record.',
                statusCode: 401
            );
        }
        $user = Auth::guard('admin')->user();
        return $this->apiResponse(
            data: [
                'token' => $user->createToken("MyAdmin", ['admin'])->plainTextToken,
                'email' => $user->email
            ],
            message: 'User logged in successful.',
        );
    }

    function logout(Request $request): JsonResponse {
        Auth::user()->tokens()->delete();

        return $this->apiResponse(
            null,
            message: 'Successfully logged out',
        );
    }
}

