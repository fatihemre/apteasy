<?php

namespace Eva\Model;

use Eva\Entity\FlatEntity;
use Eva\Library\Database;

class Flat
{
    use Database;

    public function fetch(int $id): FlatEntity|false
    {
        $flat = $this->connection->prepare("
SELECT f.*, (SELECT count(r.id) FROM residents AS r WHERE flat_id=f.id AND r.deleted_at IS NULL) as count FROM flats AS f 
WHERE f.id=:flat_id AND f.deleted_at is null
");
        $flat->execute(['flat_id' => $id]);
        $flat->setFetchMode(\PDO::FETCH_CLASS, FlatEntity::class);
        return $flat->fetch();
    }

    public function fetchAll(int $building_id): array|false
    {
        $sth = $this->connection->prepare("
SELECT f.*,(SELECT count(r.id) FROM residents AS r WHERE flat_id=f.id AND r.deleted_at IS NULL) as count FROM flats AS f 
WHERE f.building_id=:building_id AND f.deleted_at is null");
        $sth->execute([
            'building_id' => $building_id
        ]);
        $sth->setFetchMode(\PDO::FETCH_CLASS, FlatEntity::class);
        return $sth->fetchAll();
    }

    public function insert(int $building_id, array $variables): int|false
    {
        $sth = $this->connection->prepare("INSERT INTO flats (building_id, display_name, amount) VALUES (:building_id, :display_name, :amount)");
        $sth->execute([
            'building_id' => $building_id,
            'display_name' => $variables['display_name'],
            'amount' => $variables['amount']
        ]);
        return $this->connection->lastInsertId();
    }

    public function update(int $flat_id, array $variables): bool
    {
        $sth = $this->connection->prepare("UPDATE flats SET display_name=:display_name, amount=:amount, updated_at=NOW() WHERE id=:flat_id");
        return $sth->execute([
            'display_name' => $variables['display_name'],
            'amount' => $variables['amount'],
            'flat_id'=>$flat_id
        ]);
    }

    public function remove(int $id): bool
    {
        $sth = $this->connection->prepare("UPDATE flats SET updated_at = NOW(), deleted_at = NOW() WHERE id=:flat_id");
        return $sth->execute(['flat_id'=>$id]);
    }

    public function removePermanently(int $id): bool
    {
        return $this->connection->prepare("DELETE FROM flats WHERE id=:flat_id")->execute(['flat_id'=>$id]);
    }
}