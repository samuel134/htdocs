<?php

class AccountController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function addAccount($accountData) {
        $sql = "INSERT INTO accounts (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($accountData);
    }

    public function editAccount($accountId, $accountData) {
        $sql = "UPDATE accounts SET username = :username, password = :password, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $accountData['id'] = $accountId;
        $stmt->execute($accountData);
    }

    public function deleteAccount($accountId) {
        $sql = "DELETE FROM accounts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $accountId]);
    }

    public function getAccount($accountId) {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $accountId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllAccounts() {
        $sql = "SELECT * FROM accounts";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}