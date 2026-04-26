<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Stella\Clinic\Filament\Resources\Invoices\InvoiceResource;
use Stella\Clinic\Filament\Resources\MedicalRecords\MedicalRecordResource;
use Stella\Clinic\Models\Invoice;

class EditMedicalRecord extends EditRecord
{
    protected static string $resource = MedicalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->hidden(fn ($record) => $record->invoice()->exists())
                ->form([
                    TextInput::make('consultation_fee')
                        ->numeric()
                        ->default(5000)
                        ->required()
                        ->label('Consultation Fee (0 if Pharmacy Request)'),
                ])
                ->action(function (array $data, $record) {
                    $medicineFee = 0;

                    if (is_array($record->prescriptions)) {
                        foreach ($record->prescriptions as $prescription) {
                            if (isset($prescription['is_available_in_clinic']) && $prescription['is_available_in_clinic']) {
                                $price = (float) ($prescription['unit_price'] ?? 0);
                                $quantity = (float) ($prescription['quantity'] ?? 1);
                                $medicineFee += ($price * $quantity);
                            }
                        }
                    }

                    Invoice::create([
                        'medical_record_id' => $record->id,
                        'patient_id' => $record->patient_id,
                        'consultation_fee' => $data['consultation_fee'],
                        'medicine_fee' => $medicineFee,
                        'status' => 'unpaid',
                    ]);
                }),

            Action::make('view_invoice')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn ($record) => $record->invoice()->exists())
                ->url(fn ($record) => InvoiceResource::getUrl('index', ['tableSearch' => $record->id])),

            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
