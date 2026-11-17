<?php
/**
 * Base Controller
 * Induk dari semua controller yang menangani rendering view dan pemanggilan model
 */

class Controller {
    /**
     * Memanggil View dengan data yang diekstrak menjadi variabel
     */
    public function view(string $view, array $data = []): void {
        // Ekstrak array $data menjadi variabel lokal di view
        extract($data);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: File View <strong>{$view}.php</strong> tidak ditemukan di <code>{$viewFile}</code>.");
        }
    }

    /**
     * Memanggil dan menginisialisasi Model
     */
    public function model(string $model) {
        $modelFile = __DIR__ . '/../models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Error: File Model <strong>{$model}.php</strong> tidak ditemukan di <code>{$modelFile}</code>.");
        }
    }
}
