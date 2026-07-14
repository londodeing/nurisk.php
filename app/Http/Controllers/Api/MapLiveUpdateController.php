<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MapLiveUpdateController extends Controller
{
    /**
     * Server-Sent Events (SSE) endpoint for Live Map Updates
     * Pushes real-time coordinate changes to Flutter without polling.
     */
    public function stream(Request $request): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            // Keep connection alive
            while (true) {
                // In production, this would listen to Redis PubSub or Database events.
                // For MVP demo, we will stream a heartbeat and occasionally simulated events.
                
                $data = json_encode([
                    'type' => 'heartbeat',
                    'timestamp' => now()->toIso8601String()
                ]);
                
                echo "event: heartbeat\n";
                echo "data: {$data}\n\n";
                
                // Flush buffer
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                
                // Check if connection is aborted to exit loop
                if (connection_aborted()) {
                    break;
                }
                
                sleep(10); // Wait 10 seconds before next heartbeat
            }
        });
        
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Important for Nginx

        return $response;
    }
}
