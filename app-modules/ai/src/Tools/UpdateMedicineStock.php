<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Pharmacy\Models\Medicine;
use Stringable;

class UpdateMedicineStock implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Updates the stock quantity of a specific medicine. Use this when the admin wants to restock a medicine. Provide the medicine ID and the quantity to add (positive number to restock). Returns the updated stock level.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $medicine = Medicine::find($request['medicine_id']);

        if (! $medicine) {
            return json_encode([
                'success' => false,
                'message' => "Medicine with ID {$request['medicine_id']} not found.",
            ]);
        }

        $quantityToAdd = (int) $request['quantity_to_add'];

        if ($quantityToAdd <= 0) {
            return json_encode([
                'success' => false,
                'message' => 'Quantity to add must be a positive number.',
            ]);
        }

        $previousStock = $medicine->stock_quantity;
        $medicine->increment('stock_quantity', $quantityToAdd);

        return json_encode([
            'success' => true,
            'medicine_id' => $medicine->id,
            'medicine_name' => $medicine->name,
            'previous_stock' => $previousStock,
            'added_quantity' => $quantityToAdd,
            'new_stock' => $medicine->fresh()->stock_quantity,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'medicine_id' => $schema->integer()->required(),
            'quantity_to_add' => $schema->integer()->required(),
        ];
    }
}
