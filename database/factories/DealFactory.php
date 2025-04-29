<?php

namespace Database\Factories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['flight', 'hotel', 'package', 'cruise', 'car'];
        $type = $this->faker->randomElement($types);
        
        $details = [];
        if ($type === 'flight') {
            $details = [
                'departure_airport' => $this->faker->city() . ' (' . strtoupper($this->faker->lexify('???')) . ')',
                'arrival_airport' => $this->faker->city() . ' (' . strtoupper($this->faker->lexify('???')) . ')',
                'departure_date' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
                'return_date' => $this->faker->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
                'airline' => $this->faker->company(),
            ];
        } elseif ($type === 'hotel') {
            $details = [
                'location' => $this->faker->city() . ', ' . $this->faker->country(),
                'checkin_date' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
                'checkout_date' => $this->faker->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
                'rating' => $this->faker->numberBetween(1, 5),
                'amenities' => $this->faker->randomElements(['WiFi', 'Pool', 'Gym', 'Breakfast', 'Parking', 'Restaurant'], $this->faker->numberBetween(1, 6)),
            ];
        }
        
        $currencies = ['USD', 'EUR', 'GBP'];
        $sources = ['Skyscanner', 'Amadeus', 'Kayak', 'Expedia', 'Booking.com'];
        
        return [
            'title' => ucfirst($type) . ' deal: ' . $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $type,
            'price' => $this->faker->randomFloat(2, 50, 2000),
            'currency' => $this->faker->randomElement($currencies),
            'source_id' => $this->faker->uuid(),
            'source' => $this->faker->randomElement($sources),
            'details' => $details,
            'valid_until' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'url' => $this->faker->url(),
        ];
    }
} 