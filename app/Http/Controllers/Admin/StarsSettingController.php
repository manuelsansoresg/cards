<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StarsSettingController extends Controller
{
    public function edit()
    {
        $setting = StarsSetting::first() ?? new StarsSetting([
            'stars_per_dollar' => 1,
            'paypal_mode' => 'sandbox',
            'header_title' => 'Admin Panel',
            'mercadopago_mode' => 'sandbox',
        ]);
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'stars_per_dollar' => 'required|numeric|min:0',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            'paypal_mode' => 'nullable|string',
            'header_title' => 'nullable|string',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'mercadopago_public_key' => 'nullable|string',
            'mercadopago_access_token' => 'nullable|string',
            'mercadopago_mode' => 'nullable|string',
        ]);
        $setting = StarsSetting::first();
        if (!$setting) {
            $setting = new StarsSetting();
        }
        // Evitar valores null en columnas no-null de la BD (excepto header_image que es archivo)
        foreach (['paypal_client_id','paypal_secret','paypal_mode','header_title','mercadopago_public_key','mercadopago_access_token','mercadopago_mode'] as $key) {
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }
        // Subida de imagen del header (a public/uploads)
        if ($request->hasFile('header_image')) {
            $file = $request->file('header_image');
            $uploadsPath = public_path('uploads');
            if (!is_dir($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }
            $filename = 'header_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadsPath, $filename);
            $data['header_image'] = 'uploads/'.$filename;
        } else {
            // No sobrescribir si no se sube archivo
            unset($data['header_image']);
        }

        $setting->fill($data);
        $setting->save();
        return redirect()->route('admin.settings.edit')->with('success','Configuración actualizada');
    }
}