<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Pengembalian;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'pengembalian_id' => Pengembalian::factory(),
            'order_id' => 'PAY-' . $this->faker->unique()->numerify('#####'),
            'snap_token' => $this->faker->uuid,
            'amount' => $this->faker->numberBetween(10000, 500000),
            'status' => 'settlement',
            'payment_type' => 'credit_card',
            'transaction_time' => $this->faker->dateTime,
            'payload' => [],
        ];
    }
}
