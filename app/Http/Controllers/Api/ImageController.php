<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Request $request, Car $car)
    {
        $this->authorizeDealer($request, $car);

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:15'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = 'cars/' . $car->id;
            $path      = $file->storeAs($directory, $filename, 'public');

            $sortOrder = $car->images()->max('sort_order') + 1;

            $image = CarImage::create([
                'car_id'     => $car->id,
                'path'       => $path,
                'sort_order' => $sortOrder,
            ]);

            $uploaded[] = $image;
        }

        return response()->json($uploaded, 201);
    }

    public function destroy(Request $request, Car $car, CarImage $img)
    {
        $this->authorizeDealer($request, $car);

        if ($img->car_id !== $car->id) {
            abort(403, 'Image does not belong to this car.');
        }

        Storage::disk('public')->delete($img->path);
        $img->delete();

        return response()->json(['message' => 'Image deleted.']);
    }

    public function reorder(Request $request, Car $car)
    {
        $this->authorizeDealer($request, $car);

        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($request->order as $sortOrder => $imageId) {
            CarImage::where('id', $imageId)
                    ->where('car_id', $car->id)
                    ->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['message' => 'Order updated.']);
    }

    private function authorizeDealer(Request $request, Car $car): void
    {
        if ($car->user_id !== $request->user()->id && ! $request->user()->isSuper()) {
            abort(403, 'Unauthorized.');
        }
    }
}
