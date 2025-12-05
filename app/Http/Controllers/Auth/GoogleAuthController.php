<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->with(['hd' => 'gainline.co.uk']) // Restrict to gainline.co.uk domain
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Verify the user is from gainline.co.uk domain
            if (!str_ends_with($googleUser->getEmail(), '@gainline.co.uk')) {
                return redirect()->route('login')
                    ->with('error', 'Access restricted to gainline.co.uk users only.');
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(24)),
                ]
            );

            // Assign default role if new user
            if ($user->wasRecentlyCreated) {
                $user->assignRole('member');
            }

            // Gainline users get access to all projects
            if ($user->isGainlineUser() && !$user->hasRole('admin')) {
                $user->assignRole('gainline_user');
            }

            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Logout user
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
