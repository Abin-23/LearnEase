<?php
header('Content-Type: application/json');
require_once "User.php";

$data = json_decode(file_get_contents("php://input"), true);
$user = new User();

if(!isset($data['action'])) {
    echo json_encode(["success"=>false,"message"=>"No action specified"]);
    exit;
}

$action = $data['action'];

switch($action){
    case "register":
        if(isset($data['name'],$data['email'],$data['password'])){
            $name = trim($data['name']);
            $email = trim($data['email']);
            $password = trim($data['password']);

            if(empty($name) || empty($email) || empty($password)){
                echo json_encode(["success"=>false,"message"=>"All fields are required"]);
                exit;
            }
            if(strlen($password)<6){
                echo json_encode(["success"=>false,"message"=>"Password must be at least 6 characters"]);
                exit;
            }
            echo json_encode($user->register($name,$email,$password));
        } else echo json_encode(["success"=>false,"message"=>"All fields are required"]);
        break;

    case "login":
        if(isset($data['email'],$data['password'])){
            $email = trim($data['email']);
            $password = trim($data['password']);
            if(empty($email) || empty($password)){
                echo json_encode(["success"=>false,"message"=>"All fields are required"]);
                exit;
            }
            echo json_encode($user->login($email,$password));
        } else echo json_encode(["success"=>false,"message"=>"All fields are required"]);
        break;

    case "logout":
        echo json_encode($user->logout());
        break;

    default:
        echo json_encode(["success"=>false,"message"=>"Invalid action"]);
}
?>
