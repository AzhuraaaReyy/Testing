<?php

namespace App\Filament\Resources\Guru;

use App\Enums\Icon;
use App\Filament\Resources\DataMasterResource;
use App\Filament\Resources\Guru\Pages\CreateGuru;
use App\Filament\Resources\Guru\Pages\EditGuru;
use App\Filament\Resources\Guru\Pages\ListGuru;
use App\Filament\Resources\Guru\Pages\ViewGuru;
use App\Filament\Resources\Guru\Schemas\GuruForm;
use App\Filament\Resources\Guru\Schemas\GuruInfolist;
use App\Filament\Resources\Guru\Tables\GuruTable;
use App\Models\Guru;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * CRUD Guru (PRD 7). Slug "guru" dipakai App\Services\NavigationService.
 */
class GuruResource extends DataMasterResource
{
    protected static ?string $model = Guru::class;

    protected static ?string $slug = 'guru';

    protected static ?string $modelLabel = 'Guru';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static Icon $ikon = Icon::Presentation;

    protected static string $deskripsi = 'Kelola data Guru beserta Kelas yang diajar. Seorang Guru dapat mengajar lebih dari satu Kelas.';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return GuruForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GuruInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuruTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuru::route('/'),
            'create' => CreateGuru::route('/create'),
            'view' => ViewGuru::route('/{record}'),
            'edit' => EditGuru::route('/{record}/edit'),
        ];
    }
}
