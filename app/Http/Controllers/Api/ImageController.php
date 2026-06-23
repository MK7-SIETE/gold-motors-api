<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImageController extends Controller
{
    // ── Upload to Cloudinary ───────────────────────────────────
    public function upload(Request $request, Car $car)
    {
        $this->authorizeDealer($request, $car);

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:15'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $uploaded  = [];
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey    = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        foreach ($request->file('images') as $file) {
            $timestamp = time();
            $folder    = 'gold-motors/cars/' . $car->id;
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
            $sortOrder = (DB::table('car_images')->where('car_id', $car->id)->max('sort_order') ?? 0) + 1;

            $imageId = DB::table('car_images')->insertGetId([
                'car_id'     => $car->id,
                'path'       => $cloudUrl,  // store full Cloudinary URL as path
                'sort_order' => $sortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $uploaded[] = [
                'id'         => $imageId,
                'car_id'     => $car->id,
                'sort_order' => $sortOrder,
                'url'        => $cloudUrl,
                'public_id'  => $publicId,
            ];
        }

        return response()->json($uploaded, 201);
    }

    // ── Delete from Cloudinary ─────────────────────────────────
    public function destroy(Request $request, Car $car, CarImage $img)
    {
        $this->authorizeDealer($request, $car);

        if ($img->car_id !== $car->id) {
            abort(403, 'Image does not belong to this car.');
        }

        // Try to delete from Cloudinary if it's a Cloudinary URL
        $path = $img->attributes['path'] ?? '';
        if (str_contains($path, 'cloudinary.com')) {
            $this->deleteFromCloudinary($path);
        }

        $img->delete();
        return response()->json(['message' => 'Image deleted.']);
    }

    // ── Reorder ────────────────────────────────────────────────
    public function reorder(Request $request, Car $car)
    {
        $this->authorizeDealer($request, $car);

        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($request->order as $sortOrder => $imageId) {
            DB::table('car_images')
                ->where('id', $imageId)
                ->where('car_id', $car->id)
                ->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['message' => 'Order updated.']);
    }

    // ── Helpers ────────────────────────────────────────────────
    private function authorizeDealer(Request $request, Car $car): void
    {
        if ($car->user_id !== $request->user()->id && ! $request->user()->isSuper()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function deleteFromCloudinary(string $url): void
    {
        try {
            $cloudName = config('services.cloudinary.cloud_name');
            $apiKey    = config('services.cloudinary.api_key');
            $apiSecret = config('services.cloudinary.api_secret');

            // Extract public_id from URL
            // URL format: https://res.cloudinary.com/{cloud}/image/upload/v123/{public_id}.ext
            preg_match('/upload\/(?:v\d+\/)?(.+)\.[a-z]+$/i', $url, $matches);
            if (empty($matches[1])) return;

            $publicId  = $matches[1];
            $timestamp = time();
            $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");

            Http::post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
                'public_id' => $publicId,
                'api_key'   => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);
        } catch (\Throwable $e) {
            // Don't fail the request if Cloudinary delete fails
        }
    }
}
