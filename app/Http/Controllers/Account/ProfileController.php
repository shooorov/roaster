<?php

namespace App\Http\Controllers\Account;

use App\Http\Cache\CacheImage;
use App\Http\Cache\CacheUser;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile(Request $request)
    {
        $profile = $request->user();

        $params = [
            'profile' => $profile,
        ];
        // dd($params);
        // die();

        return Inertia::render('Profile/Profile', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  User  $user
     * @return Response
     */
    public function update(Request $request)
    {
        // dd($request->all());
        // die();
        $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'mobile' => ['nullable', 'string', 'max:13', 'min:11'],
            'address' => ['nullable', 'string', 'max:255'],
            'image_removed' => ['required', 'boolean'],
            // 'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        DB::beginTransaction();

        $user = User::find(Auth::id());
        $user->name = $request->name;
        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->update();

        if ($request->image_file) {
            if ($request->hasFile('image_file')) {
                if ($request->file('image_file')->isValid()) {
                    $image_base = (new User)->image_base.'/'.$user->id;
                    $thumbnail_base = 'public/'.$image_base;

                    Storage::makeDirectory($image_base);
                    Storage::makeDirectory($thumbnail_base);

                    $image_path = $request->file('image_file')->store($image_base);

                    // create image manager with desired driver
                    $manager = new ImageManager(config('image.driver'));

                    // read image from file system
                    $image_mng = $manager->make(Storage::path($image_path));

                    // resize image proportionally to 300px width
                    $image_mng->scale(width: 134);

                    // save modified image in new format
                    $image_mng->toPng()->save(Storage::path('public/'.$image_path));

                    $image = new Image(['path' => $image_path]);

                    $user->image = $image_path;
                    $user->save();

                    if (! ($user->images()->save($image))) {
                        return back()->with('success', 'Profile image updating failed!.');
                    }
                }
            }
        } else {
            if ($user->latest_image && $request->image_removed) {
                foreach ($user->images as $image) {
                    if (Storage::exists($image->path)) {
                        unlink(storage_path('app/public/thumbnails/'.$image->path));
                        unlink(storage_path('app/public/'.$image->path));
                    }
                    $image->delete();
                }
            }
        }

        DB::commit();
        CacheUser::forget();
        CacheImage::forget();

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Show the application profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function password(Request $request)
    {
        $profile = $request->user();

        $params = [
            'profile' => $profile,
        ];

        return Inertia::render('Profile/Password', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  User  $user
     * @return Response
     */
    public function updatePassword(Request $request)
    {
        $user = User::find(Auth::id());
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::beginTransaction();
        $user->password = Hash::make($request->new_password);
        $user->update();

        DB::commit();
        CacheUser::forget();

        return back()->with('success', __('Password modified successfully!'));
    }
}
