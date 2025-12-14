<?php
session_start();

// Always load db.php relative to this file's folder
require_once __DIR__ . '/../config/db.php';

function login($email, $password) {
    global $pdo;                       // use the $pdo from db.php

    if (!$pdo) {
        die('Database connection not initialized.');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];
        return true;
    }

    return false;
}

function register($name, $email, $password, $role) {
    global $pdo;

    $hashed_pass = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hashed_pass, $role]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit;
}

function uploadFile($file, $folder) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed) || $file['size'] > 2000000) {
        return false;
    }

    $filename = uniqid() . '.' . $ext;
    $path = __DIR__ . "/../uploads/$folder/$filename";

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    return false;
}