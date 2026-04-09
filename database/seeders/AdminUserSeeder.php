<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make the first user an admin
        $user = User::query()->first();
        
        if ($user) {
            $user->update(['is_admin' => true]);
            $this->command->info("User '{$user->name}' has been set as admin!");
        } else {
            $this->command->warn('No users found. Please create a user first.');
        }
    }
}
