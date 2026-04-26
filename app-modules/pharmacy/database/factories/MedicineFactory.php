<?php

namespace Stella\Pharmacy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Stella\Pharmacy\Models\Medicine;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Stella\Pharmacy\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Medicine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalPrice = $this->faker->randomFloat(2, 10, 500);
        $sellPrice = $originalPrice * 1.25; // 25% profit margin

        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->paragraph(),
            'original_price' => $originalPrice,
            'sell_price' => $sellPrice,
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'is_prescription_only' => $this->faker->boolean(20), // 20% chance
        ];
    }
}
