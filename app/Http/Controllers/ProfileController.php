<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Remove email from the fillable data to prevent changes
        $request->user()->fill([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'province' => $request->province,
            'location' => $request->location,
        ]);

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();
            
            // Logout the user first
            Auth::logout();
            
            // Handle account deletion with our custom logic
            $user->deleteAccount();

            // Clean up the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('status', 'account-deleted');
        } catch (\Exception $e) {
            return back()->withErrors(['deletion_error' => 'There was an error deleting your account. Please try again.'])->withInput();
        }
    }

    /**
     * Update delivery details
     */
    public function updateDelivery(Request $request)
    {
        try {
            $user = auth()->user();
            
            $user->update([
                'address' => $request->address,
                'phone' => $request->phone,
                'province' => $request->province,
                'location' => $request->location
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Delivery details updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update delivery details: ' . $e->getMessage()
            ], 500);
        }
    }
}
