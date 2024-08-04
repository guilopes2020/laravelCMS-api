<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use Authenticatable;

    protected $redirectTo = '/painel';

    public function __construct()
    {
        $this-> middleware('guest')->except('logout');
    }

    public function index()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->only([
            'email',
            'password'
        ]);

        $validator = $this->validator($data);

        if ($validator->fails()) {
            return redirect()->route('login')->withErrors($validator)->withInput();
        }

        $authData = ['email' => $data['email'], 'password' => $data['password']];

        $data['remember_token'] = $request->input('remember', false);

        if (! Auth::attempt($authData, $data['remember_token'])) {
            $validator->errors()->add('password', 'email e/ou senha inválidos');
            return redirect()->route('login')->withInput()->withErrors($validator);
        }

        return redirect()->route('admin');
        
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email'    => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:4'],
        ]);
    }
}
