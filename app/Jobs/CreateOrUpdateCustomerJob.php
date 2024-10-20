<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOrUpdateCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function handle()
    {
        $customer = Customer::where('email', $this->user->email)->first();

        if ($customer) {
            $customer->update([
                'user_id' => $this->user->id,
            ]);
        } else {
            Customer::create([
                'name' => $this->user->name ?? 'Unknown',
                'email' => $this->user->email,
                'phone_number' => '0982950550',
                'user_id' => $this->user->id,
            ]);
        }
    }
}
