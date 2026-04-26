<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Pharmacy\Models\Medicine;
use Stringable;

class GetInventoryReport implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieves the full medicine inventory report. Returns all medicines with stock levels, pricing, and flags any medicines that are low in stock below the given threshold. Use this to answer questions about inventory status, low-stock alerts, or pricing.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $threshold = (int) ($request['low_stock_threshold'] ?? 10);

        $medicines = Medicine::query()
            ->orderBy('stock_quantity')
            ->get(['id', 'name', 'description', 'original_price', 'sell_price', 'stock_quantity', 'is_prescription_only']);

        $report = [
            'total_medicines' => $medicines->count(),
            'low_stock_threshold' => $threshold,
            'low_stock_count' => $medicines->where('stock_quantity', '<=', $threshold)->count(),
            'out_of_stock_count' => $medicines->where('stock_quantity', 0)->count(),
            'medicines' => $medicines->map(fn ($medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'original_price' => (float) $medicine->original_price,
                'sell_price' => (float) $medicine->sell_price,
                'stock_quantity' => $medicine->stock_quantity,
                'is_prescription_only' => $medicine->is_prescription_only,
                'status' => match (true) {
                    $medicine->stock_quantity === 0 => 'out_of_stock',
                    $medicine->stock_quantity <= $threshold => 'low_stock',
                    default => 'in_stock',
                },
            ])->values()->toArray(),
        ];

        return json_encode($report);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'low_stock_threshold' => $schema->integer(),
        ];
    }
}
