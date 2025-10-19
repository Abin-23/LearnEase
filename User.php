<?php
require_once "Database.php";

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($name, $email, $password) {
        $stmt = $this->db->conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if($stmt->rowCount() > 0) return ["success"=>false,"message"=>"Email already exists"];

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        if($stmt->execute([$name,$email,$hashed])) {
            return ["success"=>true,"message"=>"Registration successful"];
        }
        return ["success"=>false,"message"=>"Registration failed"];
    }

    public function login($email,$password) {
        $stmt = $this->db->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user) return ["success"=>false,"message"=>"Email not registered"];
        if(!password_verify($password,$user['password'])) return ["success"=>false,"message"=>"Incorrect password"];

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        if($user['role']=='admin') $_SESSION['is_admin']=true;

        return ["success"=>true,"message"=>"Login successful","role"=>$user['role']];
    }

    public function logout() {
        session_start();
        session_destroy();
        return ["success"=>true,"message"=>"Logged out successfully"];
    }
}
?>
