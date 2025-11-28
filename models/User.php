<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $first_name;
    public $last_name;
    public $role;
    public $group_name;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, username, password, email, first_name, last_name, role, group_name 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->email = $row['email'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->role = $row['role'];
                $this->group_name = $row['group_name'];
                return true;
            }
        }
        return false;
    }

    public function getStudentsByGroup($group_name) {
        $query = "SELECT id, username, email, first_name, last_name, group_name 
                  FROM " . $this->table_name . " 
                  WHERE role = 'student' AND group_name = :group_name 
                  ORDER BY last_name, first_name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_name", $group_name);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, email=:email, 
                  first_name=:first_name, last_name=:last_name, role=:role, group_name=:group_name";

        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":group_name", $this->group_name);

        return $stmt->execute();
    }

    public function getAllUsers() {
        $query = "SELECT id, username, email, first_name, last_name, role, group_name, created_at FROM " . $this->table_name . " ORDER BY role DESC, last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCountByRole($role) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($row['total'] ?? 0);
    }

    public function getById($user_id) {
        $query = "SELECT id, username, email, first_name, last_name, role, group_name, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :user_id 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET email=:email, first_name=:first_name, last_name=:last_name, 
                  role=:role, group_name=:group_name";
        if (!empty($this->password)) { $query .= ", password=:password"; }
        $query .= " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":group_name", $this->group_name);
        $stmt->bindParam(":id", $this->id);
        if (!empty($this->password)) { $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT); $stmt->bindParam(":password", $hashedPassword); }
        return $stmt->execute();
    }

    public function delete($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
}
?>
