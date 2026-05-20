<?php
  class User {
    private $db;

    public function __construct(){
      $this->db = new Database;
    }

    // Register user
    public function register($data){
      $this->db->query('INSERT INTO users (username, email, password, full_name, role) VALUES(:username, :email, :password, :full_name, :role)');
      $this->db->bind(':username', $data['username']);
      $this->db->bind(':email', $data['email']);
      $this->db->bind(':password', $data['password']);
      $this->db->bind(':full_name', $data['full_name'] ?? $data['username']);
      $this->db->bind(':role', $data['role'] ?? 'member');

      // Execute
      if($this->db->execute()){
        return true;
      } else {
        return false;
      }
    }

    // Login user
    public function login($username, $password){
      $this->db->query('SELECT * FROM users WHERE username = :username');
      $this->db->bind(':username', $username);

      $row = $this->db->single();

      if($row){
        $hashed_password = $row->password;
        if(password_verify($password, $hashed_password)){
          return $row;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    // Find user by email
    public function findUserByEmail($email){
      $this->db->query('SELECT * FROM users WHERE email = :email');
      $this->db->bind(':email', $email);
      $row = $this->db->single();

      if($this->db->rowCount() > 0){
        return true;
      } else {
        return false;
      }
    }

    // Find user by username
    public function findUserByUsername($username){
      $this->db->query('SELECT * FROM users WHERE username = :username');
      $this->db->bind(':username', $username);
      $row = $this->db->single();

      if($this->db->rowCount() > 0){
        return true;
      } else {
        return false;
      }
    }

    public function countAllUsers() {
      $this->db->query('SELECT COUNT(*) AS total FROM users');
      $row = $this->db->single();
      return $row ? (int) $row->total : 0;
    }

    public function countNewUsersSince($days = 30) {
      $safeDays = max(1, (int) $days);
      $this->db->query('SELECT COUNT(*) AS total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ' . $safeDays . ' DAY)');
      $row = $this->db->single();
      return $row ? (int) $row->total : 0;
    }

    public function getRecentUsers($limit = 5) {
      $safeLimit = max(1, (int) $limit);
      $this->db->query('
        SELECT id, username, email, full_name, role, status, avatar, created_at
        FROM users
        WHERE username <> :guest_username
        ORDER BY created_at DESC
        LIMIT :limit
      ');
      $this->db->bind(':guest_username', 'guest_contact');
      $this->db->bind(':limit', $safeLimit);
      return $this->db->resultSet();
    }

    public function getAdminUsers($filters = [], $page = 1, $perPage = 10) {
      $keyword = trim($filters['keyword'] ?? '');
      $status = trim($filters['status'] ?? '');
      $role = trim($filters['role'] ?? '');
      $offset = max(0, ((int) $page - 1) * (int) $perPage);

      $conditions = [];
      if ($keyword !== '') {
        $conditions[] = '(full_name LIKE :keyword OR username LIKE :keyword OR email LIKE :keyword)';
      }
      if (in_array($status, ['active', 'banned'], true)) {
        $conditions[] = 'status = :status';
      }
      if (in_array($role, ['admin', 'member'], true)) {
        $conditions[] = 'role = :role';
      }

      $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);
      $sql = '
        SELECT id, username, email, full_name, role, status, avatar, created_at
        FROM users' . $whereSql . '
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
      ';

      $this->db->query($sql);
      if ($keyword !== '') {
        $this->db->bind(':keyword', '%' . $keyword . '%');
      }
      if (in_array($status, ['active', 'banned'], true)) {
        $this->db->bind(':status', $status);
      }
      if (in_array($role, ['admin', 'member'], true)) {
        $this->db->bind(':role', $role);
      }
      $this->db->bind(':limit', (int) $perPage);
      $this->db->bind(':offset', $offset);

      return $this->db->resultSet();
    }

    public function countAdminUsers($filters = []) {
      $keyword = trim($filters['keyword'] ?? '');
      $status = trim($filters['status'] ?? '');
      $role = trim($filters['role'] ?? '');

      $conditions = [];
      if ($keyword !== '') {
        $conditions[] = '(full_name LIKE :keyword OR username LIKE :keyword OR email LIKE :keyword)';
      }
      if (in_array($status, ['active', 'banned'], true)) {
        $conditions[] = 'status = :status';
      }
      if (in_array($role, ['admin', 'member'], true)) {
        $conditions[] = 'role = :role';
      }

      $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);
      $this->db->query('SELECT COUNT(*) AS total FROM users' . $whereSql);

      if ($keyword !== '') {
        $this->db->bind(':keyword', '%' . $keyword . '%');
      }
      if (in_array($status, ['active', 'banned'], true)) {
        $this->db->bind(':status', $status);
      }
      if (in_array($role, ['admin', 'member'], true)) {
        $this->db->bind(':role', $role);
      }

      $row = $this->db->single();
      return $row ? (int) $row->total : 0;
    }

    public function updateRole($userId, $role) {
      if (!in_array($role, ['admin', 'member'], true)) {
        return false;
      }

      $this->db->query('UPDATE users SET role = :role WHERE id = :id');
      $this->db->bind(':role', $role);
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }

    public function updateStatus($userId, $status) {
      if (!in_array($status, ['active', 'banned'], true)) {
        return false;
      }

      $this->db->query('UPDATE users SET status = :status WHERE id = :id');
      $this->db->bind(':status', $status);
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }

    public function getUserById($id) {
      $this->db->query('SELECT id, username, email, full_name, avatar, role, status, created_at, password FROM users WHERE id = :id LIMIT 1');
      $this->db->bind(':id', (int) $id);
      return $this->db->single();
    }

    public function updateProfile($userId, $fullName, $email) {
      $this->db->query('UPDATE users SET full_name = :full_name, email = :email WHERE id = :id');
      $this->db->bind(':full_name', trim($fullName));
      $this->db->bind(':email', trim($email));
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }

    public function updatePassword($userId, $hashedPassword) {
      $this->db->query('UPDATE users SET password = :password WHERE id = :id');
      $this->db->bind(':password', $hashedPassword);
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }

    public function updateAvatar($userId, $avatarFilename) {
      $this->db->query('UPDATE users SET avatar = :avatar WHERE id = :id');
      $this->db->bind(':avatar', $avatarFilename);
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }

    public function isEmailUsedByAnother($email, $excludeUserId) {
      $this->db->query('SELECT id FROM users WHERE email = :email AND id <> :exclude_id LIMIT 1');
      $this->db->bind(':email', trim($email));
      $this->db->bind(':exclude_id', (int) $excludeUserId);
      $row = $this->db->single();
      return (bool) $row;
    }

    public function deleteUserById($userId) {
      $this->db->query('DELETE FROM users WHERE id = :id LIMIT 1');
      $this->db->bind(':id', (int) $userId);
      return $this->db->execute();
    }
  }
