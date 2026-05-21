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

        // Schedule
        'schedule'                          => ['ScheduleController', 'index',            null],
        'schedule/create-period'            => ['ScheduleController', 'createPeriod',     ['ADMIN']],
        'schedule/store-period'             => ['ScheduleController', 'storePeriod',      ['ADMIN']],
        'schedule/open-registration'        => ['ScheduleController', 'openRegistration', ['ADMIN']],
        'schedule/publish'                  => ['ScheduleController', 'publish',          ['ADMIN']],
        'schedule/register-slot'            => ['ScheduleController', 'registerSlot',     null],
        'schedule/assign'                   => ['ScheduleController', 'assign',           ['ADMIN']],
        'schedule/remove-assignment'        => ['ScheduleController', 'removeAssignment', ['ADMIN']],

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

        // Payroll
        'payroll'                   => ['PayrollController', 'index',       null],
        'payroll/create-period'     => ['PayrollController', 'createPeriod', ['ADMIN']],
        'payroll/store-period'      => ['PayrollController', 'storePeriod',  ['ADMIN']],
        'payroll/calculate'         => ['PayrollController', 'calculate',    ['ADMIN']],
        'payroll/publish'           => ['PayrollController', 'publish',      ['ADMIN']],
        'payroll/view'              => ['PayrollController', 'view',         null],

        // Tickets
        'tickets'           => ['TicketController', 'index',   null],
        'tickets/create'    => ['TicketController', 'create',  null],
        'tickets/store'     => ['TicketController', 'store',   null],
        'tickets/view'      => ['TicketController', 'view',    null],
        'tickets/update-status' => ['TicketController', 'updateStatus', ['ADMIN']],

        // AJAX Endpoints (return JSON)
        'ajax/employees/search'        => ['EmployeeController',   'ajaxSearch',       ['ADMIN']],
        'ajax/employees/toggle-status' => ['EmployeeController',   'ajaxToggleStatus', ['ADMIN']],
        'ajax/leave/approve'           => ['LeaveController',      'ajaxApprove',      ['ADMIN']],
        'ajax/leave/reject'            => ['LeaveController',      'ajaxReject',       ['ADMIN']],
        'ajax/attendance/update'       => ['AttendanceController', 'ajaxUpdate',       ['ADMIN']],

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
