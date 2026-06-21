<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Message;
use App\Models\Testimonial;
use App\Models\User;

class StatsController extends Controller
{
    /** Dealer-scoped stats */
    public function dealer()
    {
        $user = auth()->user();

        $dealerCars   = Car::where('user_id', $user->id);
        $dealerCarIds = Car::where('user_id', $user->id)->pluck('id');

        // Top 5 most-viewed cars
        $topCars = Car::where('user_id', $user->id)
            ->select('id', 'make', 'model', 'views')
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        // Total views across all dealer cars
        $totalViews = Car::where('user_id', $user->id)->sum('views');

        // Inventory value — sum of price for available cars only
        $inventoryValue = Car::where('user_id', $user->id)
            ->where('is_available', true)
            ->sum('price');

        return response()->json([
            'total_cars'           => Car::where('user_id', $user->id)->count(),
            'available_cars'       => Car::where('user_id', $user->id)->where('is_available', true)->count(),
            'sold_cars'            => Car::where('user_id', $user->id)->where('is_available', false)->count(),
            'featured_cars'        => Car::where('user_id', $user->id)->where('is_featured', true)->count(),
            'total_views'          => (int) $totalViews,
            'inventory_value'      => (int) $inventoryValue,
            'top_cars'             => $topCars,
            'unread_messages'      => Message::whereIn('car_id', $dealerCarIds)->where('is_read', false)->count(),
            'total_messages'       => Message::whereIn('car_id', $dealerCarIds)->count(),
            'total_testimonials'   => Testimonial::count(),
            'pending_testimonials' => Testimonial::where('status', 'pending')->count(),
        ]);
    }

    /** Platform-wide stats for super admin */
    public function super()
    {
        return response()->json([
            'total_cars'           => Car::count(),
            'available_cars'       => Car::where('is_available', true)->count(),
            'total_messages'       => Message::count(),
            'unread_messages'      => Message::where('is_read', false)->count(),
            'total_dealers'        => User::where('role', 'dealer')->count(),
            'active_dealers'       => User::where('role', 'dealer')->where('is_active', true)->count(),
            'total_testimonials'   => Testimonial::count(),
            'pending_testimonials' => Testimonial::where('status', 'pending')->count(),
        ]);
    }
}