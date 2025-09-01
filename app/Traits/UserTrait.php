<?php

namespace App\Traits;

use App\Models\Image;
use App\Models\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait UserTrait
{
    public $image_base = 'user';

    public $default_status = 'active';

    public $statuses = [
        'pending' => 'Pending',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $types = [
        'waiter' => 'Waiter',
        'barista' => 'Barista',
        'chef' => 'Chef',
        'rider' => 'Rider',
    ];

    /**
     * Get the image image.
     */
    public function imageUrl(): Attribute
    {
        $url = null;
        if (! empty($this->image) && Storage::disk('public')->exists($this->image)) {
            $url = Storage::disk('public')->url($this->image);
        }

        return Attribute::get(fn () => $url);
    }

    /**
     * Get the User's types.
     */
    protected function types(): Attribute
    {
        return Attribute::get(fn () => $this->types);
    }

    /**
     * Get the User's statues.
     */
    protected function statuses(): Attribute
    {
        return Attribute::get(fn () => $this->statuses);
    }

    /**
     * Get the type.
     */
    public function type(): Attribute
    {
        return Attribute::get(fn () => $this->is_barista ? 'barista' : ($this->is_chef ? 'chef' : ($this->is_waiter ? 'waiter' : null)));
    }

    /**
     * POLYMORPHIC RELATIONSHIP
     */

    /**
     * Get all of the User's change statuses.
     */
    public function changeStatuses()
    {
        return $this->morphMany(Status::class, 'statusable');
    }

    /**
     * Get all of the User's images.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
