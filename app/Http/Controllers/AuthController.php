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
            $response['message'] = 'Berhasil Login';
            $response['data']['token'] = 'Bearer ' . $user->createToken('token')->accessToken;

            return response()->json($response, 200);
        } else {
            $response['status'] = false;
            $response['message'] = 'Email dan Password anda tidak terdaftar';

            return response()->json($response, 401);
        }
    }

    public function register(Request $request)
    {
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
        $response['message'] = 'Berhasil Registrasi';
        $response['data'] = $user->name;

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
