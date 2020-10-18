<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controllers;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $response['status'] = true;
            $response['message'] = 'Berhasil login';
            $response['data']['token'] = 'Bearer ' . $user->createToken('token')->accessToken;

            return response()->json($response, 200);
        } else {
            $response['status'] = false;
            $response['message'] = 'Unauthorized';

            return response()->json($response, 401);
        }
    }

    public function register(Request $request)
    {
        // return $request->all();

        $validate = Validator::make($request->all(), [
            'name' => ['string', 'required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'npp' => ['required', 'numeric', 'min:5', 'unique:users'],
            'npp_supervisor' => ['numeric', 'min:5', 'nullable'],
        ]);

        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = 'Gagal Registrasi';
            $response['error'] = $validate->errors();

            return response()->json($response, 422);
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'npp' => $request['npp'],
            'npp_supervisor' => $request['npp_supervisor'],
        ]);

        $response['status'] = true;
        $response['message'] = 'Berhasil registrasi';
        $response['data'] = $user->name;
        // $response['data']['token'] = 'Bearer ' . $user->createToken('LawanKovid')->accessToken;

        return response()->json($response, 200);
    }

    public function profile()
    {
        $user = Auth::user();
        // $user = $user->makeHidden(['email_verified_at', 'password', 'remember_token']);

        $response['status'] = true;
        $response['message'] = 'User login profil';
        $response['data'] = $user;

        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $response['status'] = true;
        $response['message'] = 'Berhasil logout';

        return response()->json($response, 200);
    }
}
