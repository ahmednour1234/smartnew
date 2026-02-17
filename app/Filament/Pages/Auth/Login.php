<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;

class Login extends BaseLogin
{
    protected function getRedirectUrl(): string
    {
        return '/public/admin';
    }
}
