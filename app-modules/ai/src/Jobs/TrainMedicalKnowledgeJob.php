<?php

namespace Stella\Ai\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use Stella\Ai\Models\MedicalKnowledge;
use Filament\Notifications\Notification;
use App\Models\User;

class TrainMedicalKnowledgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public array $data,
        public User $user
    ) {}

    public function handle(): void
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $filePath = Storage::disk('local')->path($this->data['medical_knowledge']);
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            $chunkSize = 500;
            $overlapSize = 100;

            $chunks = [];
            $start = 0;
            $textLength = mb_strlen($text, 'UTF-8');

            while ($start < $textLength) {
                $chunk = mb_substr($text, $start, $chunkSize, 'UTF-8');
                $chunks[] = trim($chunk);

                $start += ($chunkSize - $overlapSize);

                if ($start + $overlapSize >= $textLength) {
                    break;
                }
            }

            if ($start < $textLength) {
                $chunks[] = trim(mb_substr($text, $start, null, 'UTF-8'));
            }

            foreach (array_chunk($chunks, 50) as $batch) {
                $response = Embeddings::for($batch)->generate(model: 'gemini-embedding-2-preview');
                $embeddings = $response->embeddings;

                foreach ($batch as $index => $chunk) {
                    MedicalKnowledge::create([
                        'content' => $chunk,
                        'embedding' => $embeddings[$index],
                        'metadata' => [
                            'filename' => $this->data['original_filename'],
                            'filepath' => $this->data['medical_knowledge'],
                            'created_by' => $this->user->id,
                            'remark' => $this->data['remark'] ?? null,
                        ],
                    ]);
                }
            }

            Notification::make()
                ->title('Knowledge Training Completed')
                ->body("Successfully trained from {$this->data['original_filename']}.")
                ->success()
                ->send();
        } catch (\Throwable $th) {
            \Log::error('Medical Knowledge Training Failed: '.$th->getMessage(), [
                'user_id' => $this->user->id,
                'data' => $this->data,
                'exception' => $th,
            ]);

            Notification::make()
                ->title('Knowledge Training Failed')
                ->body("Failed to train from {$this->data['original_filename']}. Please check the logs.")
                ->danger()
                ->send();

            throw $th;
        }
    }
}
