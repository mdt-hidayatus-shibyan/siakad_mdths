<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Update last_seen_at jika belum ada atau sudah lebih dari 60 detik yang lalu (throttling query)
            if (!$user->last_seen_at || Carbon::parse($user->last_seen_at)->diffInSeconds(now()) >= 60) {
                User::where('id', $user->id)->update([
                    'last_seen_at' => Carbon::now()
                ]);
            }
        }

        return $next($request);
    }
}
