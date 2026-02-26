<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;

class Login extends BaseLogin
{
    public function authenticated(): ?RedirectResponse
    {
        return redirect($this->getPanel()->getUrl());
    }
}
