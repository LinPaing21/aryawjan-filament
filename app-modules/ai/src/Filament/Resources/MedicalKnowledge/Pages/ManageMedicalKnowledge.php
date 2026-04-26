<?php

namespace Stella\Ai\Filament\Resources\MedicalKnowledge\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use Stella\Ai\Filament\Resources\MedicalKnowledge\MedicalKnowledgeResource;
use Stella\Ai\Models\MedicalKnowledge;
use Filament\Schemas\Schema;

class ManageMedicalKnowledge extends ManageRecords
{
    protected static string $resource = MedicalKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('train_knowledge')
            ->label('Train Knowledge')
            ->schema(fn(Schema $schema) => MedicalKnowledgeResource::form($schema))
            ->action(function (array $data): void {
                \Filament\Notifications\Notification::make()
                    ->title('Training Started')
                    ->body('The medical knowledge training has been queued. You will be notified once it is complete.')
                    ->info()
                    ->send();

                \Stella\Ai\Jobs\TrainMedicalKnowledgeJob::dispatch($data, auth()->user());
            }),
        ];
    }
}
