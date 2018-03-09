<?php

class DB_Functions
{
 
    public $conn;
 
    // constructor
    function __construct()
    {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    // destructor
    function __destruct()
    {
         
    }
 
    /*
     * Storing new user
     * returns user details
     */
    public function storeUser($nome, $cognome, $email, $password)
    {
        $db_password = password_hash($password, PASSWORD_BCRYPT); // encrypted password
 
        // prepare and execute statement to insert new user in DB
        $stmt = $this->conn->prepare("INSERT INTO utenti(nome, cognome, email, password) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $cognome, $email, $db_password);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result)
        {
            $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        }
        else
            return false;
    }
 
    /*
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password)
    {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ?");
 
        $stmt->bind_param("s", $email);
        
        // Try to execute prepered statement
        if ($stmt->execute()) 
        {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // verifying user password
            $db_password = $user['password'];
            
            // check for password equality
            if (password_verify($password, $db_password)) 
            {
                // user authentication details are correct
                return $user;
            }
        }
        else
            return NULL;
    }
 
    /*
     * Check if email is already in use by another user
     */
    public function emailAlreadyUsed($email)
    {
        $stmt = $this->conn->prepare("SELECT email from utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        $num_rows = $stmt->num_rows;
        $stmt->close();

        return ($num_rows > 0); // return true if email exist, false otherwise
    }
}
    
?>