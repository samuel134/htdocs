<?php

class Account {
    private $userId;
    private $username;
    private $email;
    private $password;
    private $createdAt;

    public function __construct($userId, $username, $email, $password, $createdAt) {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }
}