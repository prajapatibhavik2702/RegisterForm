<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\UserRegister;
use Illuminate\Support\Facades\DB;
use App\Models\Captcha;
use App\Services\UserService;


class UserRegisterController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function  showRegisterForm()
    {
        return view('register');
    }

    public function getUsers(Request $request)
    {
        $users = $this->userService->getUsers($request);
        return response()->json($users);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => [
                'required',
                'regex:/^[A-Za-z\s\-\'\.]+$/',
                'unique:user_register,full_name'
            ],
            'dob' => ['required', 'date', 'before:-18 years'],
            'gender' => ['required', 'in:male,female'],
            'email' => ['required', 'email', 'unique:user_register,email'],
            'mobile' => ['required', 'digits:10','unique:user_register,mobile'],
            'password' => ['required', 'confirmed', 'min:6'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $user = new UserRegister();
            $user->full_name = $request->full_name;
            $user->dob = $request->dob;
            $user->gender = $request->gender;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->password = Hash::make($request->password);

            if ($request->hasFile('profile_image')) {
                $request->validate([
                    'profile_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                $file = $request->file('profile_image');
                $filename = 'profile_' . time() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('profiles', $filename, 'public');

                $user->profile_image = $path;
            }

            $user->save();
            DB::commit();

            return response()->json(['success' => 'Registration successful!'], 201);
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
}



    // public function getUsers(Request $request, $count = 10)
    // {
    //     $pageNumber = $request->get('page', 1);
    //     $q = trim($request->get('search'));

    //     $users = UserRegister::select('id', 'full_name', 'email', 'mobile')
    //         ->when($q, function ($query) use ($q) {
    //             return $query->where(function ($query) use ($q) {
    //                 $num = 0;
    //                 foreach (['full_name', 'email', 'mobile'] as $field) {
    //                     if ($num) {
    //                         $query->orWhereRaw("LOCATE(?, $field)", [$q]);
    //                     } else {
    //                         $query->whereRaw("LOCATE(?, $field)", [$q]);
    //                     }
    //                     $num++;
    //                 }
    //             });
    //         })
    //         ->orderBy('id', 'DESC')
    //         ->paginate($count, ['*'], 'page', $pageNumber);

    //     return response()->json($users);
    // }