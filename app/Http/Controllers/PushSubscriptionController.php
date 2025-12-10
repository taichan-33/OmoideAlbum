<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends Controller
{
    /**
     * Store the Push Subscription.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required',
            'keys.auth' => 'required',
            'keys.p256dh' => 'required',
        ]);

        $user = Auth::user();
        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];

        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 200);
    }

    /**
     * Delete the Push Subscription.
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required',
        ]);

        $user = Auth::user();
        $user->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true], 200);
    }

    /**
     * Update Notification Preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        $preferences = $request->input('preferences', []);

        // Merge with existing or overwrite? Let's overwrite for simplicity but validate keys if needed.
        // Assuming preferences is an array of boolean flags: ['post_interacted' => true, 'trip_updated' => false]

        $user->notification_preferences = $preferences;
        $user->save();

        return back()->with('success', 'Notification preferences updated.');
    }
}
