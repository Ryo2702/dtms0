<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('department');

        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('department');

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::exists('public/'.$user->avatar)) {
                Storage::delete('public/'.$user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated successfully.');
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::exists('public/'.$user->avatar)) {
            Storage::delete('public/'.$user->avatar);
        }

        $user->update(['avatar' => null]);

        return redirect()->route('profile.edit')->with('success', 'Avatar removed successfully.');
    }

    /**
     * Get profile statistics for the user.
     */
    public function getStats()
    {
        /** @var User $user */
        $user = Auth::user();

        $stats = [
            'total_documents_created' => 0,
            'total_documents_reviewed' => 0,
            'pending_reviews' => 0,
            'completed_reviews' => 0,
        ];

        // Only calculate stats for Staff and Head users
        if (in_array($user->type, ['Staff', 'Head'])) {
            $stats = [
                'total_documents_created' => Transaction::where('created_by', $user->id)->count(),
                'total_documents_reviewed' => TransactionReviewer::where('reviewer_id', $user->id)
                    ->whereIn('status', ['approved', 'rejected'])
                    ->count(),
                'pending_reviews' => TransactionReviewer::where('reviewer_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'completed_reviews' => TransactionReviewer::where('reviewer_id', $user->id)
                    ->where('status', 'approved')
                    ->whereHas('transaction', function($q) {
                        $q->where('transaction_status', 'completed');
                    })
                    ->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get recent activity for the user.
     */
    public function getRecentActivity()
    {
        /** @var User $user */
        $user = Auth::user();
        $activities = collect();

        if (in_array($user->type, ['Staff', 'Head'])) {
            // Recent transactions assigned to user for review
            $recentReviews = Transaction::whereHas('reviewers', function($q) use ($user) {
                    $q->where('reviewer_id', $user->id)->where('status', 'pending');
                })
                ->with(['creator', 'workflow'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'type' => 'review_assigned',
                        'description' => "Transaction assigned: {$transaction->workflow->transaction_name}",
                        'date' => $transaction->created_at,
                        'status' => $transaction->transaction_status,
                        'url' => route('transactions.show', $transaction->id),
                    ];
                });

            // Recent transactions created by user
            $recentCreated = Transaction::where('created_by', $user->id)
                ->with(['workflow'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'type' => 'document_created',
                        'description' => "Transaction created: {$transaction->workflow->transaction_name}",
                        'date' => $transaction->created_at,
                        'status' => $transaction->transaction_status,
                        'url' => route('transactions.show', $transaction->id),
                    ];
                });

            $activities = $recentReviews->concat($recentCreated)
                ->sortByDesc('date')
                ->take(10);
        }

        return $activities;
    }
}
