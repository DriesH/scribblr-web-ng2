<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Achievement;
use App\Achievement_User;
use Auth;


class AchievementController extends Controller
{
    function all() {
        $user = Auth::user();

        $all_achievements = Achievement::all();
        $completed_achievements = $user->achievements()->get();
        $total_points = $completed_achievements->sum('points') - $user->achievement_points_used;

        if ($total_points < 0) {
            $total_points = 0;
        }

        $completed_achievements->map(function ($completed_achievements) {
            $completed_achievements->completed = true;
            return $completed_achievements;
        });

        $not_completed = $all_achievements->diff($completed_achievements);
        $not_completed->map(function ($not_completed) {
            $not_completed->completed = false;
            return $not_completed;
        });

        $marked_achievements = $completed_achievements->merge($not_completed)->sortBy('id')->groupBy('category')->all();

        return response()->json([
            self::SUCCESS => true,
            'achievements' => $marked_achievements,
            'total_points' => $total_points,
            'spent_points' => $user->achievement_points_used
        ]);
    }

    function latest() {
        $user = Auth::user();
        $last_completed_achievement = $user->achievements()
                                        ->orderBy('created_at', 'desc')
                                        ->first();

        return rsponse()->json([
            self::SUCCESS => true,
            'last_completed_achievement' => $last_completed_achievement
        ]);


    }
}
