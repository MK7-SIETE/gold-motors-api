<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HeroImageController extends Controller
{
    // PUBLIC — all hero images
    public function index()
    {
        return response()->json(
            HeroImage::orderBy('sort_order')->get()->map(fn($img) => $this->withUrl($img))
        );
    }

    // SUPER — list all
    public function superIndex()
    {
        return response()->json(
            HeroImage::orderBy('sort_order')->get()->map(fn($img) => $this->withUrl($img))
        );
    }

    // SUPER — upload
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $file     = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Save to storage/app/public/heroes — accessible via /storage/heroes/filename
        $file->storeAs('heroes', $filename, 'public');

        $hero = HeroImage::create([
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'sort_order'    => (HeroImage::max('sort_order') ?? 0) + 1,
        ]);

        return response()->json($this->withUrl($hero), 201);
    }

    // SUPER — delete
    public function destroy(HeroImage $heroImage)
    {
        \Illuminate\Support\Facades\Storage::disk('public')->delete('heroes/' . $heroImage->filename);
        $heroImage->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    // Append public URL to a hero image model
    private function withUrl(HeroImage $img): array
    {
        $data = $img->toArray();
        $data['url'] = asset('storage/heroes/' . $img->filename);
        return $data;
    }
}