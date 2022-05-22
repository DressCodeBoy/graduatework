<?php
class User {

    // подключение к базе данных и имя таблицы 
    private $conn;

    // свойства объекта 
    public $user_id;
    public $user_department;

    public function __construct($db) {
        $this->conn = $db;
    }

    // данный метод используется в раскрывающемся списке 
    function read_department() {
        $stmt = $this->conn->prepare("SELECT * FROM departments;");
        $stmt->execute();
        return $stmt;
    }

}