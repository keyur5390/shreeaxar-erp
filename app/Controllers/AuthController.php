<?php
namespace App\Controllers;
use App\Support\Auth;
use App\Support\Flash;
class AuthController
{
    public function showLogin(): string { return view('auth.login', ['layout'=>false]); }
    public function login(): never { if (Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) redirect('/dashboard'); Flash::set('error', 'Invalid credentials or inactive user.'); redirect('/login'); }
    public function logout(): never { Auth::logout(); redirect('/login'); }
}
