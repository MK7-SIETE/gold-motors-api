<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HeroImageController extends Controller
{
    // PUBLIC — all hero images
    public function index()
    {
        return response()->json(
            HeroImage::orderBy('sort_order')->get()
        );
    }

    // SUPER — list all
    public function superIndex()
    {
        return response()->json(
            HeroImage::orderBy('sort_order')->get()
        );
    }

    // SUPER — upload to Cloudinary
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $file      = $request->file('image');
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey    = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');
        $folder    = 'gold-motors/heroes';
        $timestamp = time();
        $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");

        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder'    => $folder,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Cloudinary upload failed',
                'error'   => $response->json(),
            ], 500);
        }

        $cloudUrl  = $response->json('secure_url');
        $publicId  = $response->json('public_id');

        $hero = HeroImage::create([
            'filename'      => $publicId,
            'original_name' => $file->getClientOriginalName(),
            'url'           => $cloudUrl,
            'sort_order'    => (HeroImage::max('sort_order') ?? 0) + 1,
        ]);

        return response()->json($hero, 201);
    }

    // SUPER — delete from Cloudinary
    public function destroy(HeroImage $heroImage)
    {
        try {
            $cloudName = config('services.cloudinary.cloud_name');
            $apiKey    = config('services.cloudinary.api_key');
            $apiSecret = config('services.cloudinary.api_secret');
            $publicId  = $heroImage->filename; // we store public_id in filename
            $timestamp = time();
            $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");

            Http::post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
                'public_id' => $publicId,
                'api_key'   => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);
        } catch (\Throwable $e) {
            // Don't fail if Cloudinary delete errors
        }

        $heroImage->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
