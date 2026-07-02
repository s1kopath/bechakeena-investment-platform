<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class RegisterUser
{
    /**
     * Create a new investor account and fire the Registered event
     * (which dispatches the email-verification notification).
     *
     * @param  array{name: string, email: string, phone: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(fn () => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // hashed via the model's cast
        ]));

        event(new Registered($user));

        return $user;
    }
}
