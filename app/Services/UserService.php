<?php

namespace App\Services;

use App\Models\UserRegister;
use Illuminate\Http\Request;

class UserService
{
      public function getUsers(Request $request)
    {
        $pageNumber = $request->get('page', 1);
        $q = trim($request->get('search'));
        $count = 3;

        $data = UserRegister::select('id', 'full_name', 'email', 'mobile','dob','gender')
            ->when($q, function ($query) use ($q) {
                return $query->where(function ($query) use ($q) {
                    $num = 0;
                    foreach (['full_name', 'email', 'mobile','dob','gender'] as $field) {
                        if ($num) {
                            $query->orWhereRaw("LOCATE(?, $field)", [$q]);
                        } else {
                            $query->whereRaw("LOCATE(?, $field)", [$q]);
                        }
                        $num++;
                    }
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($count, ['*'], 'page', $pageNumber);


            return $data;
    }
}
