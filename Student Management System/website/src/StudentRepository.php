<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class StudentRepository {
    private \PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    public function find(int $id): ?array {
        $query = $this->db->prepare('SELECT * FROM students WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch() ?: null;
    }
    public function page(string $search, string $sort, int $page): array {
        $sorts = ['newest' => 'created_at DESC, id DESC', 'oldest' => 'created_at ASC, id ASC', 'name_asc' => 'name ASC, id ASC', 'name_desc' => 'name DESC, id DESC'];
        $order = $sorts[$sort] ?? $sorts['newest'];
        // Escape SQL wildcards so searching for % or _ searches those literal characters.
        $needle = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $where = $search !== '' ? " WHERE name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR phone LIKE ? ESCAPE '!'" : '';
        $args = $search !== '' ? [$needle, $needle, $needle] : [];
        $count = $this->db->prepare('SELECT COUNT(*) FROM students' . $where);
        $count->execute($args);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / 10));
        $page = max(1, min($page, $pages));
        $query = $this->db->prepare('SELECT * FROM students' . $where . ' ORDER BY ' . $order . ' LIMIT 10 OFFSET ' . (($page - 1) * 10));
        $query->execute($args);
        return ['rows' => $query->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
    }
    public function create(array $data, ?string $photo): int {
        $query = $this->db->prepare('INSERT INTO students (name,email,phone,address,profile_picture) VALUES (?,?,?,?,?)');
        $query->execute([$data['name'],$data['email'],$data['phone'],$data['address'],$photo]);
        return (int) $this->db->lastInsertId();
    }
    public function update(int $id, array $data, ?string $replacement, bool $remove): bool {
        // Lock only the updated record, and remove old photos after a successful commit.
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare('SELECT profile_picture FROM students WHERE id = ? FOR UPDATE');
            $lock->execute([$id]);
            $row = $lock->fetch();
            if (!$row) { $this->db->rollBack(); return false; }
            $photo = $replacement ?? ($remove ? null : $row['profile_picture']);
            $query = $this->db->prepare('UPDATE students SET name=?,email=?,phone=?,address=?,profile_picture=? WHERE id=?');
            $query->execute([$data['name'],$data['email'],$data['phone'],$data['address'],$photo,$id]);
            $this->db->commit();
            if ($photo !== $row['profile_picture']) PhotoStore::remove($row['profile_picture']);
            return true;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }
    public function delete(int $id): bool {
        $this->db->beginTransaction();
        try {
            $query = $this->db->prepare('SELECT profile_picture FROM students WHERE id=? FOR UPDATE');
            $query->execute([$id]);
            $row = $query->fetch();
            if (!$row) { $this->db->rollBack(); return false; }
            $this->db->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
            $this->db->commit();
            PhotoStore::remove($row['profile_picture']);
            return true;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }
    public function stats(): array {
        return $this->db->query('SELECT COUNT(*) AS total, COALESCE(SUM(profile_picture IS NOT NULL),0) AS photos, COALESCE(SUM(DATE(created_at)=CURRENT_DATE),0) AS today FROM students')->fetch();
    }
}
