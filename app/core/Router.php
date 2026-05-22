<?php
/**
 * Router - maps URL segments to Controller@action
 *
 * URL pattern: /controller/action/param1/param2
 * Default:     HomeController@index
 */
class Router {
    /**
     * Map of route -> [ControllerClass, action, roles]
     * roles = null means any authenticated user; [] means public
     */
    private array $routes = [
        // Auth
        'auth/login'    => ['AuthController',       'login',       []],
        'auth/logout'   => ['AuthController',       'logout',      null],

        // Home / Dashboard
        ''              => ['HomeController',        'index',       null],
        'home'          => ['HomeController',        'index',       null],

        // Profile
        'profile'       => ['ProfileController',    'index',       null],
        'profile/edit'  => ['ProfileController',    'edit',        null],

        // Employees (Admin only)
        'employees'                 => ['EmployeeController',   'index',    ['ADMIN']],
        'employees/create'          => ['EmployeeController',   'create',   ['ADMIN']],
        'employees/store'           => ['EmployeeController',   'store',    ['ADMIN']],
        'employees/edit'            => ['EmployeeController',   'edit',     ['ADMIN']],
        'employees/update'          => ['EmployeeController',   'update',   ['ADMIN']],
        'employees/toggle-status'   => ['EmployeeController',   'toggleStatus', ['ADMIN']],
        'employees/delete'          => ['EmployeeController',   'delete',   ['ADMIN']],

        // Leave
        'leave'             => ['LeaveController', 'index',   null],
        'leave/create'      => ['LeaveController', 'create',  null],
        'leave/store'       => ['LeaveController', 'store',   null],
        'leave/cancel'      => ['LeaveController', 'cancel',  null],
        'leave/approve'     => ['LeaveController', 'approve', ['ADMIN']],
        'leave/reject'      => ['LeaveController', 'reject',  ['ADMIN']],

        // Attendance
        'attendance'            => ['AttendanceController', 'index',   null],
        'attendance/update'     => ['AttendanceController', 'update',  ['ADMIN']],

        // AJAX Endpoints (return JSON)
        'ajax/employees/search'        => ['AjaxController',       'employeeSearch',       ['ADMIN']],
        'ajax/employees/toggle-status' => ['AjaxController',       'employeeToggleStatus', ['ADMIN']],
        'ajax/leave/approve'           => ['AjaxController',       'leaveApprove',         ['ADMIN']],
        'ajax/leave/reject'            => ['AjaxController',       'leaveReject',          ['ADMIN']],
        'ajax/attendance/update'       => ['AjaxController',       'attendanceUpdate',     ['ADMIN']],

    ];

    public function dispatch(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Strip the base directory if running in a subdirectory (e.g., /PHP/)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        $baseDir = str_replace('\\', '/', $baseDir);
        
        if ($baseDir !== '/' && strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
        }
        
        $uri = trim($uri, '/');

        // Try exact match first, then strip trailing segment for id-based routes
        $route = $this->resolve($uri);

        if ($route === null) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        [$controllerName, $action, $allowedRoles] = $route;

        // Authentication guard
        if ($allowedRoles === null) {
            // Must be logged in, any role
            Auth::requireLogin();
        } elseif (!empty($allowedRoles)) {
            // Must be logged in + specific role
            Auth::requireLogin();
            if (!in_array(Auth::role(), $allowedRoles, true)) {
                http_response_code(403);
                require APP_PATH . '/views/errors/403.php';
                return;
            }
        }
        // $allowedRoles === [] means public route

        // Load and instantiate controller
        $controllerFile = APP_PATH . "/controllers/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            throw new RuntimeException("Controller not found: {$controllerName}");
        }
        require_once $controllerFile;

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Action not found: {$controllerName}::{$action}");
        }

        $controller->$action();
    }

    private function resolve(string $uri): ?array {
        // Direct match
        if (isset($this->routes[$uri])) {
            return $this->routes[$uri];
        }

        // Match with trailing segment removed (for /controller/action/id patterns)
        $parts = explode('/', $uri);
        if (count($parts) >= 3) {
            $stripped = implode('/', array_slice($parts, 0, 2));
            if (isset($this->routes[$stripped])) {
                // Pass extra segments as $_GET['id']
                $_GET['id'] = $parts[2] ?? null;
                return $this->routes[$stripped];
            }
        }

        return null;
    }
}
