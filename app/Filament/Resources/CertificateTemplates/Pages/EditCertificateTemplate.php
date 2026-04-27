<?php

namespace App\Filament\Resources\CertificateTemplates\Pages;

use App\Filament\Resources\CertificateTemplates\CertificateTemplateResource;
use App\Models\CertificateTemplate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditCertificateTemplate extends EditRecord
{
    protected static string $resource = CertificateTemplateResource::class;

    protected static ?string $breadcrumb = 'Settings';

    public function getHeading(): string
    {
        return 'Template Settings';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecordTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('designer')
                ->label('Open Designer')
                ->icon(Heroicon::OutlinedPaintBrush)
                ->url($this->getResourceUrl('designer')),
            ReplicateAction::make('duplicate')
                ->label('Duplicate')
                ->icon(Heroicon::OutlinedSquare2Stack)
                ->color('gray')
                ->modalDescription('Create a new template from this design with a fresh internal key.')
                ->mutateRecordDataUsing(function (array $data, CertificateTemplate $record): array {
                    $data['name'] = $record->duplicateName();
                    $data['key'] = $record->duplicateKey();

                    return $data;
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
