<?php
/**
 * Class App (Routing Engine)
 * Membaca URL dan menentukan Controller, Method, dan Parameter yang dieksekusi
 */

class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected array $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Cek apakah ada Controller yang diminta
        if (!empty($url[0])) {
            $controllerCandidate = ucfirst($url[0]) . 'Controller';
            $controllerPath = __DIR__ . '/../controllers/' . $controllerCandidate . '.php';
            
            if (file_exists($controllerPath)) {
                $this->controller = $controllerCandidate;
                unset($url[0]);
            }
        }

        // Require file controller yang ditentukan
        $controllerFilePath = __DIR__ . '/../controllers/' . $this->controller . '.php';
        if (file_exists($controllerFilePath)) {
            require_once $controllerFilePath;
            $this->controller = new $this->controller();
        } else {
            require_once __DIR__ . '/../controllers/HomeController.php';
            $this->controller = new HomeController();
        }

        // 2. Cek apakah ada Method yang diminta (pastikan callable, public, bukan magic method)
        if (isset($url[1])) {
            $methodCandidate = $url[1];
            if (strpos($methodCandidate, '__') !== 0 && 
                method_exists($this->controller, $methodCandidate) && 
                is_callable([$this->controller, $methodCandidate])) {
                $this->method = $methodCandidate;
                unset($url[1]);
            } else {
                // Method tidak valid pada controller ini, buang agar tidak jadi parameter
                unset($url[1]);
            }
        }

        // 3. Ambil sisa elemen URL sebagai Parameter
        $this->params = $url ? array_values($url) : [];

        // 4. Jalankan controller, method, dan kirimkan params dengan perlindungan TypeError
        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (TypeError | ArgumentCountError $e) {
            // Tangani manipulasi parameter via URL (misal string diteruskan ke parameter bertipe integer)
            $coercedParams = array_map(function($param) {
                return is_numeric($param) ? (int)$param : 0;
            }, $this->params);

            try {
                call_user_func_array([$this->controller, $this->method], $coercedParams);
            } catch (Throwable $e2) {
                // Jika masih gagal, arahkan aman ke method index
                if (method_exists($this->controller, 'index') && is_callable([$this->controller, 'index'])) {
                    call_user_func([$this->controller, 'index']);
                } else {
                    header("Location: " . url(''));
                    exit;
                }
            }
        } catch (Throwable $e) {
            error_log("Error in App router: " . $e->getMessage());
            header("Location: " . url(''));
            exit;
        }
    }

    /**
     * Memecah query parameter 'url' menjadi array segmen
     */
    public function parseUrl(): array {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            if (str_contains($url, '?')) {
                $url = explode('?', $url)[0];
            }
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}
