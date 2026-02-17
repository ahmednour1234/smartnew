<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Company;
use Illuminate\Validation\ValidationException;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['user_id']) && $data['user_id'] != $this->record->user_id) {
            $count = Company::where('user_id', $data['user_id'])
                ->where('id', '!=', $this->record->id)
                ->count();
            if ($count >= 60) {
                throw ValidationException::withMessages([
                    'user_id' => 'This user already has the maximum of 60 companies assigned.',
                ]);
            }
        }
        return $data;
    }
}
