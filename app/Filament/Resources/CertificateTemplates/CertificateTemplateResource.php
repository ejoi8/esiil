<?php

namespace App\Filament\Resources\CertificateTemplates;

use App\Filament\Resources\CertificateTemplates\Pages\CreateCertificateTemplate;
use App\Filament\Resources\CertificateTemplates\Pages\Designer;
use App\Filament\Resources\CertificateTemplates\Pages\EditCertificateTemplate;
use App\Filament\Resources\CertificateTemplates\Pages\ListCertificateTemplates;
use App\Filament\Resources\CertificateTemplates\Schemas\CertificateTemplateForm;
use App\Filament\Resources\CertificateTemplates\Tables\CertificateTemplatesTable;
use App\Models\CertificateTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CertificateTemplateResource extends Resource
{
    protected static ?string $model = CertificateTemplate::class;

    protected static ?string $modelLabel = 'Certificate Template';

    protected static ?string $pluralModelLabel = 'Certificate Templates';

    protected static ?string $navigationLabel = 'Certificate Templates';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Certificates';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CertificateTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificateTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificateTemplates::route('/'),
            'create' => CreateCertificateTemplate::route('/create'),
            'designer' => Designer::route('/{record}/designer'),
            'edit' => EditCertificateTemplate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
