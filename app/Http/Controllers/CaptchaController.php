<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
// use Intervention\Image\ImageManagerStatic as Image;
use Gregwar\Captcha\CaptchaBuilder;
use App\Models\Captcha;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;


class CaptchaController extends Controller
{
    public function generateCaptcha()
    {
        $fontPath = public_path('fonts/arial.ttf');
        if (!file_exists($fontPath)) {
            return response()->json(['error' => 'Font file not found!'], 500);
        }

        $captchaCode = Str::random(6);

        $image = imagecreatetruecolor(200, 60);
        $bgColor = imagecolorallocate($image, 0, 0, 0);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 200, 60, $bgColor);

        imagettftext($image, 20, 0, 30, 40, $textColor, $fontPath, $captchaCode);

        $imageName = 'captchas/' . uniqid() . '.png';
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($imageName, $imageData);

        $captcha = Captcha::create([
            'code' => $captchaCode,
            'expires_at' => now()->addMinutes(1),
            'image_path' => $imageName,
        ]);

        return response()->json([
            'image_url' => asset('storage/' . $imageName),
            'captcha_key' => $captcha->id
        ]);
    }

    public function verifyCaptcha(Request $request)
    {
        $request->validate([
            'captcha' => 'required|string',
            'captcha_key' => 'required|exists:captchas,id'
        ]);

        $captcha = Captcha::where('id', $request->captcha_key)
            ->whereRaw('BINARY code = ?', [$request->captcha])
            ->where('expires_at', '>', now())
            ->first();

        $oldCaptha =  Captcha::where('id', $request->captcha_key)->first();

        if ($oldCaptha->image_path) {
            Storage::disk('public')->delete($captcha->image_path);
        }
        $oldCaptha->delete();

        if ($captcha) {
            return response()->json(['success' => 'Captcha verified!']);
        } else {
            return response()->json(['error' => 'Invalid captcha!'], 400);
        }
    }
}
