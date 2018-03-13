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
        $codice = substr(md5(uniqid("")),8,6);
        $data = date("Y-m-d");

        // prepare and execute statement to insert new user in DB
        $stmt = $this->conn->prepare("INSERT INTO utenti(nome, cognome, email, password, codice_conferma, data_registrazione) VALUES(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nome, $cognome, $email, $db_password, $codice, $data);
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

    public function accountVerification($email, $codice)
    {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ? AND codice_conferma = ?");
 
        $stmt->bind_param("ss", $email, $codice);
        
        // Try to execute prepered statement
        if ($stmt->execute()) 
        {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // get verified variable from db
            $verificato = $user['verificato'];
            
            // check if it's already verified
            if (!$verificato)
            {
                // Prepare statement
                $stmt = $this->conn->prepare("UPDATE utenti SET verificato = 1 WHERE id = ?");

                $stmt->bind_param("i", $user["id"]);

                // Try to execute prepered statement
                if ($stmt->execute()) 
                    return 0;
                else
                    return 1;
            }
            else
                return 2;
        }
        else
            return 1;
    }

    public function cambiaPassword($email, $password)
    {
        $db_password = password_hash($password, PASSWORD_BCRYPT); // encrypted password

        // Prepare statement
        $stmt = $this->conn->prepare("UPDATE utenti SET password = ? WHERE email = ?");
 
        $stmt->bind_param("ss", $db_password, $email);
        
        // Try to execute prepered statement
        if ($stmt->execute()) 
        {
            $result = $stmt->affected_rows;
            $stmt->close();
            return $result > 0;
        }
        else
            return null;
    }
}
    
?>