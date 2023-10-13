<?php
	namespace Pilot {
		class Router {

			private string $root;
			function __construct(string $root) {
				if (!file_exists($root)) { (New \Pilot\Utilities\Errors)->echo("router", "root"); }
				$this->root = $root;
			}

			function GET(string $route, mixed $view) { if ($_SERVER['REQUEST_METHOD'] == 'GET') { $this->route($route, $view); } }
			function POST(string $route, mixed $view) { if ($_SERVER['REQUEST_METHOD'] == 'POST') { $this->route($route, $view); } }
			function PUT(string $route, mixed $view) { if ($_SERVER['REQUEST_METHOD'] == 'PUT') { $this->route($route, $view); } }
			function PATCH(string $route, mixed $view) { if ($_SERVER['REQUEST_METHOD'] == 'PATCH') { $this->route($route, $view); } }
			function DELETE(string $route, mixed $view) { if ($_SERVER['REQUEST_METHOD'] == 'DELETE') { $this->route($route, $view); } }
			function ANY(string $route, mixed $view) { $this->route($route, $view); }

			function route(string $route, mixed $view) {
				$callback = $view;
				if ($callback === null) { header("Location: /404"); }
				if (!is_callable($callback)) { if (!strpos($view, '.php')) { $view .= '.php'; } }
				if ($route == "/404") { include_once $this->root . "/$view"; exit(); }
				$request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
				$request_url = rtrim($request_url, '/');
				$request_url = strtok($request_url, '?');
				$route_parts = explode('/', $route);
				$request_url_parts = explode('/', $request_url);
				array_shift($route_parts);
				array_shift($request_url_parts);
				if ($route_parts[0] == '' && count($request_url_parts) == 0) {
					// Callback function
					if (is_callable($callback)) { call_user_func_array($callback, []); exit(); }
					include_once $this->root . "/$view";
					exit();
				}
				if (count($route_parts) != count($request_url_parts)) { return; }
				$parameters = [];
				for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
					$route_part = $route_parts[$__i__];
					if (preg_match("/^[$]/", $route_part)) {
						$route_part = ltrim($route_part, '$');
						array_push($parameters, $request_url_parts[$__i__]);
						$$route_part = $request_url_parts[$__i__];
					} else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
						return;
					}
				}
				// Callback function
				if (is_callable($callback)) { call_user_func_array($callback, $parameters); exit(); }
				include_once $this->root . "/$view";
				exit();
			}
			function out($text) { echo htmlspecialchars($text); }

			function set_csrf() {
				session_start();
				if (!isset($_SESSION["csrf"])) { $_SESSION["csrf"] = bin2hex(random_bytes(50)); }
				echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
			}

			function is_csrf_valid() {
				session_start();
				if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) { return false; }
				if ($_SESSION['csrf'] != $_POST['csrf']) { return false; }
				return true;
			}

		}
	}

?>
