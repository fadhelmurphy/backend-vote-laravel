<?php

namespace App\Utils;

use App\Models\Vote;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SharedUtils {

    public static function saveImage($file) : String {
        $fileExtension = $file->getClientOriginalExtension();
        $fileBaseName = basename($file->getClientOriginalName(), '.' . $fileExtension);
        $timestamp = date("YdmHi",time());
        $randomstr = Str::random(3);
        $fileFullName = "${fileBaseName}_${timestamp}_$randomstr.${fileExtension}";
        Log::channel('stderr')->info($fileFullName);

        SharedUtils::deleteImage($fileFullName);
        Storage::putFileAs('public', $file, $fileFullName);
        return $fileFullName;
    }

    public static function deleteImage($fileFullName) {
        $exist = Storage::disk('public')->exists($fileFullName);
        if ($exist) {
            Storage::disk('public')->delete($fileFullName);
        }
    }

    public static function generateId($length) : String {
        $id = Str::random($length);
        $exist = Vote::where('id', $id)->count();
        if ($exist) {
            $id = SharedUtils::generateId($length);
        }
        return $id;
    }

}
