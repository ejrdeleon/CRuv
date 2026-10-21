<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {--name= : The name of the administrator} {--email= : The email of the administrator} {--password= : The administrator password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Securely create a new administrator account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Administrator Full Name');
        $email = $this->option('email') ?? $this->ask('Administrator Email');
        $password = $this->option('password') ?? $this->secret('Administrator Password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin user [{$user->email}] created successfully with ID #{$user->id}.");

        return self::SUCCESS;
    }
}
