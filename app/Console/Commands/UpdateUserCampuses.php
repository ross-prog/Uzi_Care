<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserCampuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-campuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing users with sample campus values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campuses = [
            'Main Campus',
            'North Campus', 
            'South Campus',
            'East Campus',
            'West Campus'
        ];

        $users = User::whereNull('campus')->get();
        
        if ($users->isEmpty()) {
            $this->info('No users found without campus assignment.');
            return;
        }

        foreach ($users as $index => $user) {
            $campus = $campuses[$index % count($campuses)];
            $user->update(['campus' => $campus]);
            $this->info("Updated user {$user->name} with campus: {$campus}");
        }

        $this->info("Successfully updated {$users->count()} users with campus assignments.");
    }
}
