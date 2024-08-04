<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    public function index()
    {
        $settings = [];

        $dbsettings = Setting::get();

        foreach($dbsettings as $dbsetting) {
            $settings[$dbsetting['name']] = $dbsetting['content'];
        }

        // $loggedId = Auth::id();
        // $user = User::find($loggedId);

        // if (! $user) {
        //     return redirect()->route('profile');
        // }

        return view('admin.settings.home', ['settings' => $settings]);
    }

    public function save(Request $request)
    {
        $data = $request->only([
            'title',
            'subtitle',
            'email',
            'bgcolor',
            'textcolor'
        ]);

        if (! empty($data['title'])) {
            $validator = $this->validatorUpdateTitle($data);
            if ($validator->fails()) {
                return redirect()->route('settings')->withErrors($validator)->withInput();
            }

            // $setting->title = $data['title'];

        }

        if (! empty($data['subtitle'])) {
            $validator = $this->validatorUpdateSubTitle($data);
            if ($validator->fails()) {
                return redirect()->route('settings')->withErrors($validator)->withInput();
            }

            // $setting->subtitle = $data['subtitle'];

        }
        
        if (! empty($data['email'])) {
            $validator = $this->validatorUpdateEmail($data);
            if ($validator->fails()) {
                return redirect()->route('settings')->withErrors($validator)->withInput();
            }
            // if ($setting->email != $data['email']) {
            //     $hasEmail = User::where('email', $data['email'])->get();
            //     if (count($hasEmail) === 0) {
            //         $setting->email = $data['email'];
            //     } else {
            //         $validator->errors()->add('email', 'este email não está disponível, escolha outro');
            //         return redirect()->route('settings')->withErrors($validator)->withInput();
            //     }
            // }
        }

        if (! empty($data['bgcolor'])) {
            $validator = $this->validatorUpdateBgColor($data);
            if ($validator->fails()) {
                return redirect()->route('settings')->withErrors($validator)->withInput();
            }

            // $setting->name = $data['bgcolor'];

        }

        if (! empty($data['textcolor'])) {
            $validator = $this->validatorUpdateTextColor($data);
            if ($validator->fails()) {
                return redirect()->route('settings')->withErrors($validator)->withInput();
            }

            // $setting->textcolor = $data['textcolor'];

        }
        
        foreach($data as $item => $value) {
            Setting::where('name', $item)->update([
                'content' => $value
            ]);
        }

        return redirect()->route('settings')->with('warning', 'Informações salvas com sucesso!');
    }

    private function validatorUpdateTitle($data)
    {
        return Validator::make($data, [
            'title'     => ['string', 'max:100'],
        ]);

    }

    private function validatorUpdateSubTitle($data)
    {
        return Validator::make($data, [
            'subtitle'     => ['string', 'max:100'],
        ]);

    }

    private function validatorUpdateEmail($data)
    {
        return Validator::make($data, [
            'email'    => ['string', 'email', 'max:200'],
        ]);

    }

    private function validatorUpdateBgColor($data)
    {
        return Validator::make($data, [
            'bgcolor'     => ['string', 'regex:/#[a-zA-Z0-9]{6}/i'],
        ]);

    }

    private function validatorUpdateTextColor($data)
    {
        return Validator::make($data, [
            'textcolor'     => ['string', 'regex:/#[a-zA-Z0-9]{6}/i'],
        ]);

    }
    
}
