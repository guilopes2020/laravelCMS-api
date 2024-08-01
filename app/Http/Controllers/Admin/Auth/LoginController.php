<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Authenticatable;

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
        echo "Tela de Login";
    }
}
