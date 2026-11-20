<?php

namespace App\Filament\Resources\Siswa;

use App\Enums\Icon;
use App\Filament\Resources\DataMasterResource;
use App\Filament\Resources\Siswa\Pages\CreateSiswa;
use App\Filament\Resources\Siswa\Pages\EditSiswa;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Filament\Resources\Siswa\Pages\ViewSiswa;
use App\Filament\Resources\Siswa\Schemas\SiswaForm;
use App\Filament\Resources\Siswa\Schemas\SiswaInfolist;
use App\Filament\Resources\Siswa\Tables\SiswaTable;
use App\Models\Siswa;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * CRUD Siswa (PRD 5). Slug "siswa" dipakai App\Services\NavigationService.
 */
class SiswaResource extends DataMasterResource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $slug = 'siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static Icon $ikon = Icon::Users;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SiswaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiswaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiswaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiswa::route('/'),
            'create' => CreateSiswa::route('/create'),
            'view' => ViewSiswa::route('/{record}'),
            'edit' => EditSiswa::route('/{record}/edit'),
        ];
    }
}
