<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\SuperAdmin;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class AdminAccounts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.super-admin.pages.admin-accounts';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Admin accounts';

    protected static ?string $navigationGroup = 'Platform tools';

    protected static ?string $title = 'Admin accounts';

    protected static ?int $navigationSort = 4;

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('createAdmin')
                ->label('Create administrator')
                ->icon('heroicon-o-plus')
                ->form($this->accountFormSchema())
                ->action(fn (array $data) => SuperAdmin::query()->create($data))
                ->successNotificationTitle('Administrator created.'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SuperAdmin::query()->latest())
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('created_at')->label('Added')->dateTime()->sortable(),
            ])
            ->actions([
                TableAction::make('editAdmin')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (SuperAdmin $record): array => [
                        'name' => $record->name,
                        'email' => $record->email,
                        'password' => '',
                    ])
                    ->form($this->accountFormSchema(editing: true))
                    ->action(function (SuperAdmin $record, array $data): void {
                        $record->name = $data['name'];
                        $record->email = $data['email'];

                        if (filled($data['password'] ?? null)) {
                            $record->password = Hash::make($data['password']);
                        }

                        $record->save();
                    })
                    ->successNotificationTitle('Administrator updated.'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<int, mixed> */
    private function accountFormSchema(bool $editing = false): array
    {
        return [
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique('super_admins', 'email', ignoreRecord: $editing),
            TextInput::make('password')
                ->label($editing ? 'New password (optional)' : 'Password')
                ->password()
                ->revealable()
                ->minLength(8)
                ->required(! $editing),
        ];
    }
}
