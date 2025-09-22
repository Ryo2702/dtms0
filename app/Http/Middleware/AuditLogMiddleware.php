<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (Auth::check()) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Log the request
     */
    protected function logRequest(Request $request, Response $response): void
    {
        // Skip logging for certain routes to avoid noise
        $skipRoutes = [
            'notifications.counts',
            'livewire/',
            '_debugbar/',
            'telescope/',
            'audit-logs', // Prevent logging the audit log viewing itself
        ];

        $path = $request->path();
        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($path, $skipRoute)) {
                return;
            }
        }

        // Only log successful responses and important actions
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $action = $this->determineAction($request);
        
        // Only proceed if we have a valid action
        if (!$action) {
            return;
        }
        
        $description = $this->generateDescription($request, $action);

        // Only log if we have a meaningful description
        if ($description) {
            AuditLog::log($action, $description);
        }
    }

    /**
     * Determine the action based on the request
     */
    protected function determineAction(Request $request): ?string
    {
        $method = $request->method();
        $route = $request->route();
        
        if (!$route) {
            return null;
        }

        $routeName = $route->getName();
        $path = $request->path();

        // Login/Logout actions
        if ($routeName === 'login' && $method === 'POST') {
            return 'login';
        }
        if ($routeName === 'logout') {
            return 'logout';
        }

        // Document actions
        if (str_contains($path, 'documents')) {
            if ($method === 'POST' && str_contains($path, '/approve')) {
                return 'approve';
            }
            if ($method === 'POST' && str_contains($path, '/reject')) {
                return 'reject';
            }
            if ($method === 'POST' && str_contains($path, '/forward')) {
                return 'forward';
            }
            if ($method === 'GET' && str_contains($path, '/download')) {
                return 'download';
            }
            if ($method === 'POST' && !str_contains($path, '/approve') && !str_contains($path, '/reject') && !str_contains($path, '/forward')) {
                return 'create';
            }
            if ($method === 'PUT' || $method === 'PATCH') {
                return 'update';
            }
            if ($method === 'DELETE') {
                return 'delete';
            }
        }

        // User management actions
        if (str_contains($path, 'users') || str_contains($path, 'staff')) {
            if ($method === 'POST') {
                return 'create';
            }
            if ($method === 'PUT' || $method === 'PATCH') {
                return 'update';
            }
            if ($method === 'DELETE') {
                return 'delete';
            }
        }

        // Department actions
        if (str_contains($path, 'departments')) {
            if ($method === 'POST') {
                return 'create';
            }
            if ($method === 'PUT' || $method === 'PATCH') {
                return 'update';
            }
            if ($method === 'DELETE') {
                return 'delete';
            }
        }

        // Profile updates
        if (str_contains($path, 'profile') && ($method === 'PUT' || $method === 'PATCH')) {
            return 'update';
        }

        return null;
    }

    /**
     * Generate a human-readable description
     */
    protected function generateDescription(Request $request, string $action): ?string
    {
        $user = Auth::user();
        $path = $request->path();
        $route = $request->route();
        
        // Safety check for user
        if (!$user) {
            return null;
        }
        
        switch ($action) {
            case 'login':
                return "User {$user->name} logged into the system";
                
            case 'logout':
                return "User {$user->name} logged out of the system";
                
            case 'create':
                if (str_contains($path, 'documents')) {
                    return "Created a new document for review";
                }
                if (str_contains($path, 'users') || str_contains($path, 'staff')) {
                    return "Created a new user account";
                }
                if (str_contains($path, 'departments')) {
                    return "Created a new department";
                }
                break;
                
            case 'update':
                if (str_contains($path, 'documents')) {
                    return "Updated document information";
                }
                if (str_contains($path, 'users') || str_contains($path, 'staff')) {
                    return "Updated user account information";
                }
                if (str_contains($path, 'departments')) {
                    return "Updated department information";
                }
                if (str_contains($path, 'profile')) {
                    return "Updated profile information";
                }
                break;
                
            case 'delete':
                if (str_contains($path, 'documents')) {
                    return "Deleted a document";
                }
                if (str_contains($path, 'users') || str_contains($path, 'staff')) {
                    return "Deleted a user account";
                }
                if (str_contains($path, 'departments')) {
                    return "Deleted a department";
                }
                break;
                
            case 'approve':
                return "Approved a document review";
                
            case 'reject':
                return "Rejected a document review";
                
            case 'forward':
                return "Forwarded a document to another department";
                
            case 'download':
                return "Downloaded a document";
        }

        return null;
    }
}
