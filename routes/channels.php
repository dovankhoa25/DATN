<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('bill.{bill_id}', function ($user, $bill_id) {
//     return $user->bills->contains('id', $bill_id);
// });

Broadcast::channel('bill.{bill_id}', function ($user, $bill_id) {
    return true;
});
