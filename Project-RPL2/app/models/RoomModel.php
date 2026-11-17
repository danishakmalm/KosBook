<?php
/**
 * RoomModel / KamarModel Alias
 * Mempertahankan backward compatibility untuk pemanggilan RoomModel
 */

require_once __DIR__ . '/KamarModel.php';

class RoomModel extends KamarModel {

    public function getAllRooms(): array {
        return $this->getAll();
    }

    public function getRoomById(int $id): ?array {
        return $this->getById($id);
    }

    public function getAvailableRooms(): array {
        return $this->getAll('tersedia');
    }
}
