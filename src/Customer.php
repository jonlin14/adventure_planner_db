<?php
    class Customer
    {
        private $name;
        private $password;
        private $id;
// Constructor of Class
        function __construct($name, $id = null, $password = "admin")
        {
            $this->name = $name;
            $this->id = $id;
            $this->password = $password;
        }

// Getters and Setters for private properties
        function getName()
        {
            return $this->name;
        }

        function setName($new_name)
        {
            $this->name = (string) $new_name;
        }

        function getId()
        {
            return $this->id;
        }

        function setId($new_id)
        {
            $this->id = (int) $new_id;
        }

        function getPassword()
        {
            return $this->password;
        }

        function setPassword($new_password)
        {
            $this->password = (string) $new_password;
        }
// Methods to interact with Database

// This function needs to be run before the save method!!!
// Only save if return == false!!!
        static function checkName($name){
            $query = $GLOBALS['DB']->query("SELECT name FROM customers;");
            $all_names = $query->fetchAll(PDO::FETCH_ASSOC);
            $exist = false;

            foreach($all_names as $username){
                if($username['name'] == $name){
                    $exist = true;
                }
            }
            return $exist;
        }
    //THE 'TOM' VERSION
    //     static function checkAvailable($check_username)
    //    {
    //        $statement = $GLOBALS['DB']->query("SELECT * FROM users WHERE name = '$check_username';");
    //        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    //        return empty($results);
    //    }

        function save(){
            $statement = $GLOBALS['DB']->query("INSERT INTO customers (name, password) VALUES ('{$this->getName()}', '{$this->getPassword()}') RETURNING id;");
            $result = $statement ->fetch(PDO::FETCH_ASSOC);
            $this->setId($result['id']);
        }

        static function getAll(){
            $statement = $GLOBALS['DB']->query("SELECT * FROM customers;");
            $all_customers = array();
            foreach($statement as $person){
                $name = $person['name'];
                $id = $person['id'];
                $password = $person['password'];
                $new_patron = new Customer($name, $id, $password);
                array_push($all_customers, $new_patron);
            }
            return $all_customers;
        }

        static function deleteAll(){
            $GLOBALS['DB']->exec("DELETE FROM customers *;");
        }

        static function find($search_id){
            $found_customer = null;
            $customers = Customer::getAll();
            foreach($customers as $person){
                if ($person->getId() == $search_id) {
                    $found_customer = $person;
                }
            }
            return $found_customer;
        }

        function updateName($new_name){
            $GLOBALS['DB']->exec("UPDATE customers SET name = '{$new_name}' WHERE id = {$this->getId()};");
            $this->setName($new_name);
        }

        function updatePassword($new_password){
            $GLOBALS['DB']->exec("UPDATE customers SET password = '{$new_password}' WHERE id = {$this->getId()};");
            $this->setPassword($new_password);
        }

        function delete(){
            $GLOBALS['DB']->exec("DELETE FROM customers WHERE id = {$this->getId()};");
        }

        function getActivityPreference($activity){
            $query = $GLOBALS['DB']->query("SELECT activity_pref FROM preferences WHERE activity_id = {$activity->getId()} AND customer_id = {$this->getId()};");

            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result['activity_pref'];
        }

        function setActivityPreference($activity, $preference){
            $GLOBALS['DB']->exec("INSERT INTO preferences (customer_id, activity_pref, activity_id, activity_name) VALUES ({$this->getId()}, {$preference}, {$activity->getId()}, '{$activity->getName()}');");
        }

        // function login($input_password){
        //     $query = $GLOBALS['DB']->query("SELECT password FROM customers WHERE name = '{$this->getName()}';");
        //
        //     $password = $query->fetch(PDO::FETCH_ASSOC);
        //     $match = false;
        //
        //     if($password['password'] == $input_password){
        //         $match = true;
        //         array_push($_SESSION['user_id'], $match);
        //     }
        //     return $match;//(TRUE = login)
        // }

        //THE 'TOM' WAY
        static function logInCheck($username, $password){
            $statement = $GLOBALS['DB']->query("SELECT * FROM customers WHERE name = '$username' AND password = '$password';");
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            $match_customer = null;
            foreach ($results as $result) {
                $match_customer = new Customer($result['name'], $result['password'], $result['id']);
            }
            return $match_customer;
        }



    }
 ?>
