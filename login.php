<?php
session_start();

require_once__DIR__.'/config/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=$_POST['email'];
    $password=$_POST['password'];
    
    $statement=$db->prepare("SELECT*FROM users WHERE email = :email");
}