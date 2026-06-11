<?php

namespace Sitakgmbh\LaraBase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('users')->updateOrInsert(
			['username' => 'admin'],
			[
				'username'   => 'admin',
				'email'      => 'admin@localhost',
				'password'   => Hash::make('admin'),
				'auth_type'  => 'local',
				'firstname'  => 'Admin',
				'lastname'   => 'User',
				'is_enabled' => true,
				'created_at' => now(),
				'updated_at' => now(),
			]
		);

		$user = \App\Models\User::where('username', 'admin')->first();
		$user->assignRole('admin');
	}
}