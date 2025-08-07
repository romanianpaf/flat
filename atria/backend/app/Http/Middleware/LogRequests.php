<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Nu logăm cererile pentru resurse statice sau API calls
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        $this->logRequest($request, $response);

        return $response;
    }

    private function shouldSkipLogging(Request $request): bool
    {
        $skipPaths = [
            '/api/logs', // Nu logăm cererile pentru logs pentru a evita recursiunea
            '/api/admin/logs',
            '/api/admin/logs/export',
            '/api/admin/logs/stats',
        ];

        $skipMethods = ['OPTIONS'];

        return in_array($request->path(), $skipPaths) || 
               in_array($request->method(), $skipMethods) ||
               $request->is('*.css') ||
               $request->is('*.js') ||
               $request->is('*.png') ||
               $request->is('*.jpg') ||
               $request->is('*.jpeg') ||
               $request->is('*.gif') ||
               $request->is('*.ico') ||
               $request->is('*.svg');
    }

    private function logRequest(Request $request, Response $response): void
    {
        try {
            $level = $this->determineLogLevel($response);
            $action = $this->determineAction($request);
            $description = $this->createDescription($request, $response);

            Log::log($action, $description, $level, [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'request_data' => $this->sanitizeRequestData($request),
            ]);
        } catch (\Exception $e) {
            // Nu logăm erorile de logging pentru a evita recursiunea
        }
    }

    private function determineLogLevel(Response $response): string
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            return 'error';
        } elseif ($statusCode >= 400) {
            return 'warning';
        } elseif ($statusCode >= 200 && $statusCode < 300) {
            return 'success';
        } else {
            return 'info';
        }
    }

    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        if (str_starts_with($path, 'api/')) {
            $path = str_replace('api/', '', $path);
        }

        return strtolower($method) . '_' . str_replace('/', '_', $path);
    }

    private function createDescription(Request $request, Response $response): string
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();

        $user = auth()->user();
        $userName = $user ? $user->name : 'Guest';

        return "{$userName} a făcut o cerere {$method} la {$path} (Status: {$statusCode})";
    }

    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();

        // Eliminăm datele sensibile
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'access_token'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***HIDDEN***';
            }
        }

        return $data;
    }
}
