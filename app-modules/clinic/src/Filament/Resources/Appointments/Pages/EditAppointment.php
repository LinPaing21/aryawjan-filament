<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Stella\Clinic\Enums\AppointmentStatus;
use Stella\Clinic\Filament\Resources\Appointments\AppointmentResource;
use Stella\Clinic\Models\MedicalRecord;
use Stella\Clinic\Services\AppointmentService;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $medicalRecord = $this->record->medicalRecord;

        if ($medicalRecord) {
            $data['symptoms'] = $medicalRecord->symptoms;
            $data['final_diagnosis'] = $medicalRecord->final_diagnosis;
            $data['doctor_notes'] = $medicalRecord->doctor_notes;
            $data['prescriptions'] = $medicalRecord->prescriptions;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $oldScheduledAt = $this->record->scheduled_at;
        $newScheduledAt = $data['scheduled_at'];
        logger()->info('Mutate Form Data Before Save', [
            'old_scheduled_at' => $oldScheduledAt,
            'new_scheduled_at' => $newScheduledAt,
        ]);
        if ($oldScheduledAt !== $newScheduledAt) {
            $data['token_number'] = app(AppointmentService::class)->calculateTokenNumber($data['scheduled_at'], $data['doctor_id']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->status !== AppointmentStatus::Completed) {
            return;
        }

        $data = $this->form->getRawState();

        if (empty($data['symptoms'])) {
            return;
        }

        $medicalRecord = $this->record->medicalRecord;
        $payload = [
            'symptoms' => $data['symptoms'],
            'final_diagnosis' => $data['final_diagnosis'],
            'doctor_notes' => $data['doctor_notes'] ?? null,
            'prescriptions' => $data['prescriptions'] ?? null,
        ];

        if ($medicalRecord) {
            $medicalRecord->update($payload);
        } else {
            MedicalRecord::create([
                'patient_id' => $this->record->patient_id,
                'doctor_id' => $this->record->doctor_id,
                'triage_log_id' => $this->record->triage_log_id,
                ...$payload,
            ]);
        }
    }
}
