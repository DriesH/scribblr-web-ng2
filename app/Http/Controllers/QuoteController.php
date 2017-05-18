<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Quote;
use App\Child;
use App\Classes\ShortIdGenerator;
use Validator;
use ColorThief\ColorThief;
use Auth;
use Image;

class QuoteController extends Controller
{
    /*
    | Get all quotes for user
    */
    function getAllQuotes(){
        $userId = Auth::user()->id;
        $quotes = Quote::whereHas('children', function($query) use($userId) {
            $query->where('children.user_id', $userId);
        })
        ->get();

        if (!$quotes) {
            return self::RespondModelNotFound();
        }

        return response()->json([
            self::SUCCESS => true,
            'quotes' => $quotes
        ]);

    }


    /*
    | Create a new quote.
    */
    function new(Request $request, ShortIdGenerator $shortIdGenerator, $childShortId)
    {
        $validator = Validator::make($request->all(), [
            'quote' => self::REQUIRED,
            'story' => 'max:1000',
            'img_original' => self::REQUIRED . '|url',
            'img_baked' => self::REQUIRED . '|image',

        ]);

        if ($validator->fails()) {
            return self::RespondValidationError($request, $validator);
        }

        //check if child belongs to current user
        $child = Child::where('short_id', $childShortId)->first();
        if (!$child) {
            return self::RespondModelNotFound();
        }

        $quote = new Quote();
        do {
            $quoteShortId = $shortIdGenerator->generateId(8);
        } while ( count( Quote::where('short_id', $quoteShortId)->first()) >= 1 );
        $quote->short_id = $quoteShortId;
        $quote->quote = $request->quote;
        if ($request->story) {
            $quote->story = $request->story;
        }
        $quote->child_id = $child->id;
        $quote->img_main_color = self::getMainColor($request->img_original);
        $quote->save();

        //images
        self::addQuoteOriginal($quote, $request->img_original);
        self::addQuoteBaked($quote, $request->img_baked);

        return response()->json([
            self::SUCCESS => true,
            'quote' => $quote,
            self::ACHIEVEMENT => self::checkAchievementProgress(self::ADD_SCRIBBLE)
        ]);

    }

    private function addQuoteOriginal($quote, $img_original){

        $quote->lqip = self::getSmallSizeImage($img_original);

        $img_original_url_id = sha1($img_original);
        $quote->addMediaFromUrl($img_original)
        ->withCustomProperties(['url_id' => $img_original_url_id])
        ->toMediaLibrary('original');

        $quote->img_original_url_id = $img_original_url_id;

        $quote->save();
    }

    private function addQuoteBaked($quote, $img_baked){
        $img_baked_url_id = sha1($img_baked->getPathName());

        $quote->addMedia($img_baked)
        ->withCustomProperties(['url_id' => $img_baked_url_id])
        ->toMediaLibrary('baked');

        $quote->img_baked_url_id = $img_baked_url_id;
        $quote->save();
    }

    private function getSmallSizeImage($image) {
        return Image::make($image)
        ->resize(5, null, function ($constraint) {
            $constraint->aspectRatio();
        })
        ->encode('data-url')
        ->encoded;
    }

    /*
    | Delete a quote by shortId.
    | @params {$shortId}
    */
    function delete($childShortId, $quoteShortId)
    {
        $quoteToDelete = Quote::where('short_id', $quoteShortId)->first();
        if (!$quoteToDelete) {
            return self::RespondModelNotFound();
        }
        $quoteToDelete->delete();

        return response()->json([
            self::SUCCESS => true
        ]);
    }

    function getMainColor($image_url) {
        $dominantColor_rgb = ColorThief::getColor($image_url, 100);

        $dominantColor_hex = sprintf("#%02x%02x%02x", $dominantColor_rgb[0], $dominantColor_rgb[1], $dominantColor_rgb[2]);

        return $dominantColor_hex;
    }

    // function newQuote(Request $request, $childShortId) {
    //     $validator = Validator::make($request->all(), [
    //         'link' => 'url',
    //     ]);
    //
    //     if ($validator->fails()) {
    //         return self::RespondValidationError($request, $validator);
    //     }
    //
    //     $user = Auth::user();
    //     $child = Child::where('short_id', $childShortId)->whereHas('user', function($query) use($user) {
    //         $query->where('users.id', $user->id);
    //     })->first();
    //
    //     if (!$child) {
    //         return self::RespondModelNotFound();
    //     }
    //
    //     $imageURL = $request->link;
    //     try {
    //         $child->addMediaFromUrl($imageURL)->toMediaLibrary('edited_images');
    //     } catch (Exception $e) {
    //         return response()->json([
    //             self::SUCCESS => false,
    //             self::ERROR_TYPE => 'failed to download/save image'
    //         ]);
    //     }
    //
    //
    //     return response()->json([
    //         self::SUCCESS => true,
    //         'media' => $child->getMedia('edited_images')
    //     ]);
    // }

    function getQuoteOriginalImage(Request $request, $childShortId, $quoteShortId, $img_original_url_id) {
        $quote = Quote::where('short_id', $quoteShortId)->first();

        if (!$quote) {
            return self::RespondModelNotFound();
        }

        if ($quote->img_original_url_id != $img_original_url_id) {
            return response()->json([
                self::SUCCESS => false,
                self::ERROR_TYPE => self::ERROR_TYPE_IMAGE_NOT_FOUND
            ]);
        }

        return Image::make($quote->getMedia('original')[0]->getPath())->response();
    }

    function getQuoteBakedImage(Request $request, $childShortId, $quoteShortId, $img_baked_url_id) {
        $quote = Quote::where('short_id', $quoteShortId)->first();

        if (!$quote) {
            return self::RespondModelNotFound();
        }

        if ($quote->img_baked_url_id != $img_baked_url_id) {
            return response()->json([
                self::SUCCESS => false,
                self::ERROR_TYPE => self::RROR_TYPE_IMAGE_NOT_FOUND
            ]);
        }

        return Image::make($quote->getMedia('baked')[0]->getPath())->response();
    }
}
