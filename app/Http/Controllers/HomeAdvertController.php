<?php

namespace App\Http\Controllers;

use App\Models\HomeAdvertBanner;
use App\Traits\RequestHelpers\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class HomeAdvertController extends Controller
{
    use APIResponse;
    public function createBanner (Request $request){
        $request->validate([
           'title'      => 'required|string|max:255',
           'image'      => 'required|image|max:5000',
           'link'       => 'nullable|string|max:255',
           'link_name'  => 'nullable|string|max:255',
        ]);

        // Process the iage
        $img = Image::make($request->image)->resize(600, 270)->encode('jpg');
        $name = Str::random(50).'_'.$request->image->getClientOriginalName();;
        Storage::disk('images')->put($name, $img);

       $banner =  HomeAdvertBanner::create([
           'title'      =>  $request->title,
           'image'      =>  $name,
           'link'       =>  $request->link,
           'link_name'  =>  $request->link_name
        ]);

        return $this->successResponse([
            'errorCode'     => 'SUCCESS',
            'data'          => $banner
        ], 201);
    }

    public function fetchBanners($limit){
        $banners = HomeAdvertBanner::orderBy('id', 'DESC')->limit($limit)->get();

        return $this->showAll($banners, 200);
    }

    public function fetchBanner($id){
        $banner = HomeAdvertBanner::findOrFail($id);

        return $this->showOne($banner, 200);
    }

    public function deleteBanner($id){
        $banner = HomeAdvertBanner::findOrFail($id);
        // Delete the image
        File::delete(public_path("uploads/images/$banner->image"));

        // Delete the record
        $banner->delete();
        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'message'       =>  'Banner deleted'
        ], 202);
    }


    public function updateBanner(Request $request, $id){
        $banner = HomeAdvertBanner::findOrFail($id);
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'image'      => 'nullable|image|max:5000',
            'link'       => 'nullable|string|max:255',
            'link_name'  => 'nullable|string|max:255',
        ]);


        $banner->fill($request->only([
            'title',
            'image',
            'link',
            'link_name',
        ]));

        if ($request->image){
            // Delete the old image
            File::delete(public_path("uploads/images/$banner->image"));
            // Process the iage
            $img = Image::make($request->image)->resize(600, 270)->encode('jpg');
            $filename = Str::random(50).'_'.$request->image->getClientOriginalName();;
            Storage::disk('images')->put($filename, $img);

            $banner->image = $filename;
        }
        $banner->save();

        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'data'          =>  $banner
        ], 202);
    }

}
