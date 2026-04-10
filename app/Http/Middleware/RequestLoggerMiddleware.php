<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RequestLoggerMiddleware
{
    protected array $ignoredPatterns = [
        'assets/*',
        'storage/*',
        'build/*',
        'livewire/*',
    ];

    protected array $ignoredContains = [
        '/_debugbar',
        '/build/',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldLog($request)) {
            $this->writeLog($request);
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        if ($request->isMethod('OPTIONS')) {
            return false;
        }

        foreach ($this->ignoredPatterns as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        $path = $request->getPathInfo();
        foreach ($this->ignoredContains as $keyword) {
            if (str_contains($path, $keyword)) {
                return false;
            }
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension && in_array(strtolower($extension), ['css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'webp'], true)) {
            return false;
        }

        return true;
    }

    protected function writeLog(Request $request): void
    {
        try {
            $payload = Arr::except($request->all(), ['password', 'password_confirmation']);

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'http_request',
                'table_name' => 'general_request',
                'record_id' => (string) Str::uuid(),
                'old_values' => null,
                'new_values' => null,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'request_payload' => empty($payload) ? null : $payload,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
