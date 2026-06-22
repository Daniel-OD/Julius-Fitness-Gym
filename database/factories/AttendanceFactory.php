<?php

namespace Database\Factories;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', 'now');
        $checkIn = (clone $date)->setTime(9, 0);

        return [
            'user_id' => User::factory(),
            'date' => $date->format('Y-m-d'),
            'check_in' => $checkIn,
            'check_out' => (clone $checkIn)->modify('+8 hours'),
            'method' => AttendanceMethod::Manual,
            'status' => AttendanceStatus::Present,
            'note' => null,
        ];
    }
}
