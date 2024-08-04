<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $loggedId = Auth::id();
        $user = User::find($loggedId);

        if (! $user) {
            return redirect()->route('profile');
        }

        return view('admin.profile.home', ['user' => $user]);
        
    }

    public function save(Request $request)
    {
        $loggedId = Auth::id();
        $user = User::find($loggedId);

        if (! $user) {
            return redirect()->route('profile');
        }

        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation'
        ]);

        if (! empty($data['name'])) {
            $validator = $this->validatorUpdateName($data);
            if ($validator->fails()) {
                return redirect()->route('profile', ['user' => $loggedId])->withErrors($validator)->withInput();
            }

            $user->name = $data['name'];

        }
        
        if (! empty($data['email'])) {
            $validator = $this->validatorUpdateEmail($data);
            if ($validator->fails()) {
                return redirect()->route('profile', ['user' => $loggedId])->withErrors($validator)->withInput();
            }
            if ($user->email != $data['email']) {
                $hasEmail = User::where('email', $data['email'])->get();
                if (count($hasEmail) === 0) {
                    $user->email = $data['email'];
                } else {
                    $validator->errors()->add('email', 'este email não está disponível, escolha outro');
                    return redirect()->route('profile', ['user' => $loggedId])->withErrors($validator)->withInput();
                }
            }
        }

        if (! empty($data['password'])) {
            $validator = $this->validatorUpdatePassword($data);
            if ($validator->fails()) {
                return redirect()->route('profile', ['user' => $loggedId])->withErrors($validator)->withInput();
            }

            $user->password = Hash::make($data['password']);

        }
        
        $user->save();

        return redirect()->route('profile')->with('warning', 'Informações salvas com sucesso!');
    }

    private function validatorUpdateName($data)
    {
        return Validator::make($data, [
            'name'     => ['string', 'max:100'],
        ]);

    }

    private function validatorUpdateEmail($data)
    {
        return Validator::make($data, [
            'email'    => ['string', 'email', 'max:200'],
        ]);

    }

    private function validatorUpdatePassword($data)
    {
        return Validator::make($data, [
            'password' => ['string', 'min:4', 'confirmed']
        ]);

    }
}
