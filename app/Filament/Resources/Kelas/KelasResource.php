<?php

namespace App\Filament\Resources\Kelas;

use App\Filament\Resources\DataMasterResource;
use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\Pages\ViewKelas;
use App\Filament\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Resources\Kelas\Schemas\KelasInfolist;
use App\Filament\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * CRUD Kelas (PRD 6). Slug "kelas" dipakai App\Services\NavigationService.
 */
class KelasResource extends DataMasterResource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $slug = 'kelas';

    protected static ?string $modelLabel = 'Kelas';

    protected static ?string $recordTitleAttribute = 'nama_kelas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return KelasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KelasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
            'view' => ViewKelas::route('/{record}'),
            'edit' => EditKelas::route('/{record}/edit'),
        ];
    }
}
