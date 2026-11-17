<?php
/**
 * HomeController
 * Mengelola halaman utama (Landing Page) KosBook
 */

class HomeController extends Controller {

    public function index(): void {
        $kamarModel = $this->model('KamarModel');
        $allRooms = $kamarModel->getAll();

        // Ambil kamar rekomendasi (prioritaskan kamar yang berstatus tersedia)
        $availableRooms = array_values(array_filter($allRooms, fn($r) => $r['status'] === 'tersedia'));
        if (count($availableRooms) >= 3) {
            $featuredRooms = array_slice($availableRooms, 0, 3);
        } else {
            $otherRooms = array_values(array_filter($allRooms, fn($r) => $r['status'] !== 'tersedia'));
            $featuredRooms = array_slice(array_merge($availableRooms, $otherRooms), 0, 3);
        }

        $data = [
            'title' => 'KosBook - Sistem Booking Kamar Kos Online',
            'appName' => APP_NAME,
            'activePage' => 'home',
            'hero' => [
                'headline' => 'Temukan & Booking Kamar Kos Impianmu Lebih Cepat',
                'subheadline' => 'Platform pencarian dan pemesanan kamar kos mahasiswa terpercaya, transparan, dan mudah tanpa ribet.',
            ],
            'statistics' => [
                ['number' => '6 Unit', 'label' => 'Total Kamar', 'icon' => 'bi-door-open'],
                ['number' => '100%', 'label' => 'Bebas Biaya Admin', 'icon' => 'bi-cash-coin'],
                ['number' => 'Cepat', 'label' => 'Konfirmasi Online', 'icon' => 'bi-lightning-charge'],
                ['number' => 'Aman', 'label' => 'Terverifikasi Pemilik', 'icon' => 'bi-shield-check'],
            ],
            'featuredRooms' => $featuredRooms,
            'features' => [
                [
                    'title' => 'Pencarian & Filter Pintar',
                    'desc' => 'Cari kamar berdasarkan ketersediaan (Tersedia/Booking/Terisi) dan rentang tarif sewa per bulan.',
                    'icon' => 'bi-search'
                ],
                [
                    'title' => 'Booking Langsung Online',
                    'desc' => 'Pesan kamar kapan saja tanpa perlu survey bolak-balik. Cek ketersediaan kamar secara real-time.',
                    'icon' => 'bi-calendar-check'
                ],
                [
                    'title' => 'Verifikasi Langsung Pemilik',
                    'desc' => 'Admin pemilik kos memverifikasi pesananmu secara transparan melalui panel pengelola.',
                    'icon' => 'bi-check-circle'
                ]
            ],
            'flash' => AuthHelper::getFlash()
        ];

        // Render struktur View
        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('home/index', $data);
        $this->view('layouts/footer', $data);
    }
}
