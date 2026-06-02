<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Andrei Popescu',
                'email' => 'andrei.popescu@example.ro',
                'contact' => '0722 111 222',
                'gender' => 'male',
                'dob' => '1990-05-14',
                'city' => 'București',
                'source' => 'word_of_mouth',
                'goal' => 'fitness',
                'status' => 'active',
            ],
            [
                'name' => 'Maria Ionescu',
                'email' => 'maria.ionescu@example.ro',
                'contact' => '0733 222 333',
                'gender' => 'female',
                'dob' => '1995-08-22',
                'city' => 'Cluj-Napoca',
                'source' => 'promotions',
                'goal' => 'fatloss',
                'status' => 'active',
            ],
            [
                'name' => 'Ion Dumitrescu',
                'email' => 'ion.dumitrescu@example.ro',
                'contact' => '0744 333 444',
                'gender' => 'male',
                'dob' => '1985-12-01',
                'city' => 'Timișoara',
                'source' => 'others',
                'goal' => 'body_building',
                'status' => 'active',
            ],
            [
                'name' => 'Elena Constantin',
                'email' => 'elena.constantin@example.ro',
                'contact' => '0755 444 555',
                'gender' => 'female',
                'dob' => '2000-03-17',
                'city' => 'Iași',
                'source' => 'word_of_mouth',
                'goal' => 'fitness',
                'status' => 'inactive',
            ],
            [
                'name' => 'Mihai Georgescu',
                'email' => 'mihai.georgescu@example.ro',
                'contact' => '0766 555 666',
                'gender' => 'male',
                'dob' => '1992-11-09',
                'city' => 'Brașov',
                'source' => 'promotions',
                'goal' => 'weightgain',
                'status' => 'active',
            ],
        ];

        foreach ($members as $data) {
            Member::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
