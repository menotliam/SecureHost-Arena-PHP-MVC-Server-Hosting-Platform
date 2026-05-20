<?php
class App {
    protected $currentController = 'Pages';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();
        // @file_put_contents('../public/routing.log', date('H:i:s') . ' URL: ' . implode('/', $url) . "\n", FILE_APPEND);

        // Special routing: map /admin/<name> or single-segment admin<name> to controller Admin<Name> if that controller exists
        if (isset($url[0]) && strtolower($url[0]) === 'admin') {
            if (isset($url[1]) && file_exists('../app/controllers/Admin' . ucwords($url[1]) . '.php')) {
                $this->currentController = 'Admin' . ucwords($url[1]);
                unset($url[0]);
                unset($url[1]);
            } else {
                $this->currentController = 'Admin';
                unset($url[0]);
            }
            // Re-index $url so next segment becomes $url[0]
            $url = array_values($url);
        } elseif (isset($url[0]) && preg_match('/^admin([A-Za-z0-9_]+)$/i', $url[0], $m) && file_exists('../app/controllers/Admin' . ucwords($m[1]) . '.php')) {
            // Support single-segment routes like /admincontacts -> AdminContacts controller
            $this->currentController = 'Admin' . ucwords($m[1]);
            unset($url[0]);
            $url = array_values($url);
        } elseif (isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . '.php')) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
            $url = array_values($url);
        }

        require_once '../app/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // After re-indexing, decide which method to call with safe fallbacks
        if (isset($url[0]) && $url[0] !== '') {
            $candidate = $url[0];

            if (method_exists($this->currentController, $candidate)) {
                // URL explicitly names an existing method
                $this->currentMethod = $candidate;
                unset($url[0]);
                $url = array_values($url);
            } elseif (is_numeric($candidate) && method_exists($this->currentController, 'show')) {
                // URL like /users/123 -> map to show(123)
                $this->currentMethod = 'show';
                // keep params as-is so show receives the id
            } else {
                // No such method; try sensible fallbacks
                if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && method_exists($this->currentController, 'update')) {
                    $this->currentMethod = 'update';
                } elseif (method_exists($this->currentController, 'index')) {
                    $this->currentMethod = 'index';
                } else {
                    // Try other common names
                    $fallbacks = ['show', 'view', 'list', 'all'];
                    $found = false;
                    foreach ($fallbacks as $fb) {
                        if (method_exists($this->currentController, $fb)) {
                            $this->currentMethod = $fb;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
                        echo '404 Not Found - Method not available on controller: ' . htmlspecialchars($candidate);
                        exit;
                    }
                }
            }
        } else {
            // No method segment provided
            if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && method_exists($this->currentController, 'update')) {
                $this->currentMethod = 'update';
            } elseif (!method_exists($this->currentController, $this->currentMethod)) {
                if (method_exists($this->currentController, 'index')) {
                    $this->currentMethod = 'index';
                } else {
                    $fallbacks = ['show', 'view', 'list', 'all'];
                    $found = false;
                    foreach ($fallbacks as $fb) {
                        if (method_exists($this->currentController, $fb)) {
                            $this->currentMethod = $fb;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
                        echo '404 Not Found - No default method available on controller: ' . htmlspecialchars($this->currentController);
                        exit;
                    }
                }
            }
        }

        $this->params = $url ? array_values($url) : [];

        if (!method_exists($this->currentController, $this->currentMethod)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
            echo '404 Not Found - Controller method missing: ' . htmlspecialchars($this->currentMethod);
            exit;
        }

        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
