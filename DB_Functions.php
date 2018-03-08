<?php

class DB_Functions
{
 
    private $conn;
 
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
    public function storeUser($nome, $cognome, $username, $password)
    {
        $db_password = password_hash($passwordm, PASSWORD_BCRYPT); // encrypted password
 
        // prepare and execute statement to insert new user in DB
        $stmt = $this->conn->prepare("INSERT INTO users(nome, cognome, username, password, data_creazione) VALUES(?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $nome, $cognome, $username, $db_password);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result)
        {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        }
        else
            return false;
    }
 
    /*
     * Get user by username and password
     */
    public function getUserByUsernameAndPassword($username, $password)
    {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE username = ?");
 
        $stmt->bind_param("s", $username);
        
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
     * Check if username is already in use by another user
     */
    public function usernameAlreadyUsed($username)
    {
        $stmt = $this->conn->prepare("SELECT username from users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        $num_rows = $stmt->num_rows;
        $stmt->close();

        return ($num_rows > 0); // return true if username exist, false otherwise
    }
}
    
?>