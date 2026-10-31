<?php

namespace App\Http\Controllers;

use App\Models\SecurityActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActiveSessionController extends Controller
{
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if (config('session.driver') !== 'database') {
            return back()->withErrors(['session' => 'Individual session management requires the database session driver.']);
        }

        $session = DB::table(config('session.table', 'sessions'))
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $session || $session->id === $request->session()->getId()) {
            return back()->withErrors(['session' => 'That session is no longer available.']);
        }

        DB::table(config('session.table', 'sessions'))->where('id', $session->id)->delete();
        $this->log($request, 'session_terminated', 'An active session was terminated.');

        return back()->with('success', 'The selected session was signed out.');
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        if (config('session.driver') !== 'database') {
            return back()->withErrors(['session' => 'Other-session management requires the database session driver.']);
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        $this->log($request, 'sessions_terminated', 'All other active sessions were terminated.');

        return back()->with('success', 'All other sessions were signed out.');
    }

    private function log(Request $request, string $type, string $description): void
    {
        SecurityActivityLog::create([
            'user_id' => $request->user()->id,
            'activity_type' => $type,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
