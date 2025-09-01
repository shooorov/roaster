<?php

namespace App\Traits;

use App\Models\Image;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait ImageTrait
{
    /**
     * Get the Model's image.
     */
    // public function latest_image()
    // {
    //     return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    // }

    /**
     * Get all of the Model's images.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the Trait image.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    // public function image(): Attribute
    // {
    //     $path = null;
    //     if ($this->latest_image && Storage::exists('thumbnails/' . $this->latest_image->path)) {
    //         $path = Storage::url('thumbnails/' . $this->latest_image->path);
    //     }
    //     return Attribute::get(fn () => $path);
    // }
}
