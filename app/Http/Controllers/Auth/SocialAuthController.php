<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $driver = $provider === 'linkedin' ? 'linkedin-openid' : $provider;

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle the OAuth provider callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $driver = $provider === 'linkedin' ? 'linkedin-openid' : $provider;

        try {
            $socialUser = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }

        // Check if social account already exists
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // Log in the existing user
            Auth::login($socialAccount->user);

            return redirect()->route('dashboard');
        }

        // Check if user exists with this email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link social account to existing user
            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            Auth::login($user);

            return redirect()->route('dashboard');
        }

        // Create new user and social account
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
        ]);

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Validate the OAuth provider.
     */
    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['github', 'google', 'linkedin'])) {
            abort(404);
        }
    }
}
