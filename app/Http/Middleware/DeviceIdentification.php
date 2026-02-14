<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DeviceIdentification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for device ID in header (for API/mobile requests)
        $deviceId = $request->header('X-Device-ID');
        
        // If not in header, check query parameter
        if (!$deviceId) {
            $deviceId = $request->query('device_id');
        }
        
        // If not in query, check session
        if (!$deviceId) {
            $deviceId = Session::get('device_id');
        }
        
        // If still no device ID, generate one for this session
        if (!$deviceId) {
            $deviceId = $this->generateDeviceId($request);
            Session::put('device_id', $deviceId);
        }
        
        // Store device ID in request attributes for easy access
        $request->attributes->set('device_id', $deviceId);
        
        // Also store in session if not already there
        if (!Session::has('device_id')) {
            Session::put('device_id', $deviceId);
        }
        
        return $next($request);
    }
    
    /**
     * Generate a unique device ID based on request info
     */
    private function generateDeviceId(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = substr($request->userAgent() ?? 'unknown', 0, 50);
        $timestamp = now()->format('YmdHis');
        $random = bin2hex(random_bytes(4));
        
        // Create a hash-based device ID
        $hash = substr(md5("{$ip}:{$userAgent}:{$timestamp}"), 0, 8);
        
        return "web-{$hash}-{$random}";
    }
}
