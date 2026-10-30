<?php
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
 
class R2FileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
 
        $file = $request->file('file');
        $key = 'users/'. Str::uuid() . '.' . $file->extension();
 
        Storage::disk('r2')->put($key, fopen($file->getRealPath(), 'r'));
 
        // Save the key + owner ID in a database for a real application.
        return response()->json(['key' => $key], 201);
    }
 
    public function download(Request $request, string $name)
    {
        $key = 'users/' . $name;
        abort_unless(Storage::disk('r2')->exists($key), 404);
        return Storage::disk('r2')->download($key);
    }
}
