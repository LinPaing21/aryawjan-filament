<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Pharmacy\Models\Medicine;
use Stringable;

class SearchMedicines implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Searches the clinic pharmacy inventory for medicines by name. Returns matching medicines with their sell price, stock quantity, and whether they require a prescription. Use this to check availability and pricing before adding prescriptions to a medical record.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        logger()->info('SearchMedicines called with query: ' . $request['query']);
        $results = Medicine::query()
            ->where('name', 'ilike', '%'.$request['query'].'%')
            ->where('stock_quantity', '>', 0)
            ->get(['id', 'name', 'description', 'sell_price', 'stock_quantity', 'is_prescription_only']);

        if ($results->isEmpty()) {
            return json_encode([
                'found_in_inventory' => false,
                'query' => $request['query'],
                'message' => "'{$request['query']}' is not currently stocked in the clinic pharmacy. You may still include it in suggested_meds based on clinical guidelines.",
            ]);
        }

        return json_encode([
            'found_in_inventory' => true,
            'medicines' => $results->toArray(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
        ];
    }
}
