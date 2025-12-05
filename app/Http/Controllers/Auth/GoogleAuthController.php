<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

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
            $googleUser = Socialite::driver('google')->stateless()->user();

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

            // Assign default role if new user (only if roles exist)
            if ($user->wasRecentlyCreated && Role::where('name', 'member')->exists()) {
                $user->assignRole('member');
            }

            // Gainline users get access to all projects (only if role exists)
            if ($user->isGainlineUser() && !$user->hasRole('admin') && !$user->hasRole('gainline_user')) {
                if (Role::where('name', 'gainline_user')->exists()) {
                    $user->assignRole('gainline_user');
                }
            }

            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
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
