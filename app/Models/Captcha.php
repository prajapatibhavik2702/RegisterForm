<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Captcha extends Model
{
    protected $fillable = ['code', 'image_path', 'expires_at'];

}
