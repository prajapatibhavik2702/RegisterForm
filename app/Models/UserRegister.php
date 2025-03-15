<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegister extends Model
{
    use HasFactory;

    protected $table = 'user_register';

    protected $fillable = ['full_name', 'dob', 'gender', 'profile_image', 'email', 'mobile', 'password'];
}
