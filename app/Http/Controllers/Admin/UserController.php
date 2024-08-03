<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:edit-users');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10);
        $loggedId = Auth::id();

        return view('admin.users.index', ['users' => $users, 'loggedId' => $loggedId]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation'
        ]);

        $validator = $this->validatorCreate($data);

        if ($validator->fails()) {
            return redirect()->route('users.create')->withErrors($validator)->withInput();
        }

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return redirect()->route('users.index', ['user' => $user]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('users.index');
        }

        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('users.index');
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
                return redirect()->route('users.edit', ['user' => $id])->withErrors($validator)->withInput();
            }

            $user->name = $data['name'];

        }
        
        if (! empty($data['email'])) {
            $validator = $this->validatorUpdateEmail($data);
            if ($validator->fails()) {
                return redirect()->route('users.edit', ['user' => $id])->withErrors($validator)->withInput();
            }
            if ($user->email != $data['email']) {
                $hasEmail = User::where('email', $data['email'])->get();
                if (count($hasEmail) === 0) {
                    $user->email = $data['email'];
                } else {
                    $validator->errors()->add('email', 'email já existe, escolha outro');
                    return redirect()->route('users.edit', ['user' => $id])->withErrors($validator)->withInput();
                }
            }
        }

        if (! empty($data['password'])) {
            $validator = $this->validatorUpdatePassword($data);
            if ($validator->fails()) {
                return redirect()->route('users.edit', ['user' => $id])->withErrors($validator)->withInput();
            }

            $user->password = Hash::make($data['password']);

        }
        
        $user->save();

        return redirect()->route('users.index', ['user' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $loggedId = Auth::id();

        if ($loggedId == $id) {
            return redirect()->route('users.index');
        }

        $user = User::find($id);
        $user->delete();
        
        return redirect()->route('users.index');
    }

    private function validatorCreate($data)
    {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed']
        ]);

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
