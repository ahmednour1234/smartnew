<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    public function authenticated(): ?RedirectResponse
    {
        return redirect('/public/admin');
    }

    public function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Contracts\Auth\Authenticatable $tenant = null): string
    {
        $url = parent::getUrl($parameters, $isAbsolute, $panel, $tenant);
        
        // Replace /admin with /public/admin in the URL
        if (Str::contains($url, '/admin')) {
            $url = str_replace('/admin', '/public/admin', $url);
        }
        
        return $url;
    }

    protected function getFormAction(): string
    {
        $action = parent::getFormAction();
        
        // Replace /admin with /public/admin in the form action URL
        if (Str::contains($action, '/admin')) {
            $action = str_replace('/admin', '/public/admin', $action);
        }
        
        return $action;
    }
}
