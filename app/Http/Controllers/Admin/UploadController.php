<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use App\Models\MediaUpload;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = Upload::with('categoria')->orderBy('id','desc')->paginate(20);
        return view('admin.uploads.index', compact('uploads'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('admin.uploads.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'stars_cost' => 'nullable|integer|min:0',
            // El checkbox envía 'on' cuando está marcado; evitamos la validación booleana estricta
            'is_free' => 'nullable',
            // La BD requiere categoria_id NOT NULL, lo hacemos obligatorio
            'categoria_id' => 'required|integer|exists:categorias,id',
            // Solo permitimos imágenes o videos por extensión
            'media_files' => 'required',
            'media_files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,bmp,mp4,mov,avi,mkv,webm,m4v',
        ]);

        $upload = new Upload();
        $upload->user_id = Auth::id();
        $upload->title = $data['title'];
        $upload->price = $data['price'] ?? 0;
        // Si es gratuito, precio y stars a 0 (checkbox)
        $isFree = $request->has('is_free');
        $upload->is_free = $isFree;
        $upload->stars_cost = $isFree ? 0 : ($data['stars_cost'] ?? 0);
        // Inicializamos como 'image'; se recalculará tras subir archivos
        $upload->type = 'image';
        $upload->categoria_id = $data['categoria_id'];
        $upload->save();

        if ($request->hasFile('media_files')) {
            $order = 0;
            $foundVideo = false;
            $foundImage = false;
            foreach ($request->file('media_files') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                // Detectar por extension (solo image/video)
                $imageExts = ['jpg','jpeg','png','gif','webp','bmp'];
                $videoExts = ['mp4','mov','avi','mkv','webm','m4v'];
                $isImage = in_array($ext, $imageExts);
                $isVideo = in_array($ext, $videoExts);
                $dir = $isImage ? 'uploads/images' : 'uploads/videos';
                $name = Str::uuid()->toString().'.'.$ext;
                $path = $dir.'/'.$name;

                // store under public
                $targetDir = public_path($dir);
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $name);

                MediaUpload::create([
                    'upload_id' => $upload->id,
                    'file_path' => $path,
                    'file_type' => $isImage ? 'image' : 'video',
                    'sort_order' => $order++,
                ]);

                $foundVideo = $foundVideo || $isVideo;
                $foundImage = $foundImage || $isImage;
            }

            // Si hay al menos un video, el tipo del upload es 'video'; si no, 'image'.
            $upload->type = $foundVideo ? 'video' : 'image';
            $upload->save();
        }

        return redirect()->route('admin.uploads.index')->with('success','Tarjeta creada con archivos');
    }

    public function edit(Upload $upload)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $media = MediaUpload::where('upload_id', $upload->id)->orderBy('sort_order')->get();
        return view('admin.uploads.edit', compact('upload','categorias','media'));
    }

    public function update(Request $request, Upload $upload)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'stars_cost' => 'nullable|integer|min:0',
            'is_free' => 'nullable',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'media_files.*' => 'nullable|file',
        ]);

        $upload->title = $data['title'];
        $upload->price = $data['price'] ?? 0;
        $isFree = $request->has('is_free');
        $upload->is_free = $isFree;
        $upload->stars_cost = $isFree ? 0 : ($data['stars_cost'] ?? 0);
        // El tipo se recalcula si se suben nuevos archivos
        $upload->categoria_id = $data['categoria_id'];
        $upload->save();

        if ($request->hasFile('media_files')) {
            $order = MediaUpload::where('upload_id', $upload->id)->max('sort_order') + 1;
            $foundVideo = false;
            $foundImage = false;
            foreach ($request->file('media_files') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $imageExts = ['jpg','jpeg','png','gif','webp','bmp'];
                $videoExts = ['mp4','mov','avi','mkv','webm','m4v'];
                $isImage = in_array($ext, $imageExts);
                $isVideo = in_array($ext, $videoExts);
                $dir = $isImage ? 'uploads/images' : 'uploads/videos';
                $name = Str::uuid()->toString().'.'.$ext;
                $path = $dir.'/'.$name;
                $targetDir = public_path($dir);
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $name);
                MediaUpload::create([
                    'upload_id' => $upload->id,
                    'file_path' => $path,
                    'file_type' => $isImage ? 'image' : 'video',
                    'sort_order' => $order++,
                ]);

                $foundVideo = $foundVideo || $isVideo;
                $foundImage = $foundImage || $isImage;
            }

            $upload->type = $foundVideo ? 'video' : 'image';
            $upload->save();
        }

        return redirect()->route('admin.uploads.index')->with('success','Tarjeta actualizada');
    }

    public function destroy(Upload $upload)
    {
        // delete attached media files
        $media = MediaUpload::where('upload_id', $upload->id)->get();
        foreach ($media as $m) {
            $full = public_path($m->file_path);
            if (file_exists($full)) {
                @unlink($full);
            }
            $m->delete();
        }
        $upload->delete();
        return redirect()->route('admin.uploads.index')->with('success','Tarjeta eliminada');
    }

    public function destroyMedia(MediaUpload $media)
    {
        $full = public_path($media->file_path);
        if (file_exists($full)) {
            @unlink($full);
        }
        $media->delete();
        return back()->with('success','Archivo eliminado');
    }
}