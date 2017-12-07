<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;

class SocialAccountController extends Controller
{
  public function redirectToProvider($provider)
  {
    return Socialite::driver($provider)->redirect();
  }

  public function handleProviderCallback($provider)
  {
    try {
      $user = Socialite::driver($provider)->user();
    } catch (Exception $e) {
      return redirect('/login');
    }

    $authUser = $this->findOrCreateUser($user, $provider);

    Auth::login($authUser, true);

    return redirect($this->redirectTo);
  }

  public function findOrCreateUser($user, $provider)
  {
    $account = SocialAccount::where('provider_name', $provider)->where('provider_id', $user->getId())->first();

    if ($account) {
      return $account->user;
    } else {
      $authUser = User::where('email', $user->getEmail())->first();

      if (! $authUser) {
        $authUser = User::create([
          'email' => $user->getEmail(),
          'name' => $user->getName(),
        ]);
      }

      $authUser->accounts()->create([
        'provider_id' => $user->getId(),
        'provider_name' => $user->getName(),
      ]);

      return $authUser;
    }
  }
}
