<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Post;

class ShareController extends Controller
{
    function sharePost($childShortId, $postShortId) {
        $userId = Auth::user()->id;
        $post = Post::whereHas('child', function($query) use($userId) {
            $query->where('children.user_id', $userId);
        })
        ->where('short_id', $postShortId)
        ->first();

        if (!$post) {
            return self::RespondModelNotFound();
        }

        $post->is_shared = true;
        $post->save();

        return response()->json([
            self::SUCCESS => true,
            'post' => $post,
            self::ACHIEVEMENT => self::checkAchievementProgress(self::SHARE_SCRIBBLE)
        ]);
    }

    function getSharedPost($childShortId, $postShortId, $img_baked_url_id) {
        $post = Post::where('short_id', $postShortId)
                ->where('img_baked_url_id', $img_baked_url_id)
                ->where('is_shared', true)
                ->first(['quote', 'story', 'img_baked_url_id', 'is_memory']);

        if (!$post) {
            return response()->json([
                self::SUCCESS => false,
                self::ERROR_TYPE => self::ERROR_TYPE_IMAGE_NOT_FOUND
            ], 400);
        }

        return response()->json([
            self::SUCCESS => true,
            'post' => $post
        ]);
    }
}
