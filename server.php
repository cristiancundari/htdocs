<?php

    if(isset($_GET["operazione"])) {
        $op = $_GET["operazione"];

        // json response array
        $response = array("error" => false);

        require_once "DB_Functions.php";
        $db = new DB_Functions();

        switch ($op) {
            case 'login':
                login();
                break;
            case 'registrazione':
                registrazione();
                break;
            case 'verifica':
                verifica();
                break;
            case 'email_registrazione':
                email_registrazione();
                break;
            case 'email_recupero':
                email_recupero();
                break;
            case 'cambia_password':
                cambia_password();
                break;
            default:
                $response["error"] = true;
                $response["error_msg"] = "Specificare il tipo di operazione che si vuole effettuare.";
                break;
        }

        echo json_encode($response);
    }

    // Ritorna le informazione dell'utente se le credenziali sono corrette
    function login(){
        global $response;

        // check if email and password was passed through POST method
        if (isset($_POST['email']) && isset($_POST['password'])) 
        {
            // receiving the post params
            $email = $_POST['email'];
            $password = $_POST['password'];
        
            // get the user by email and password
            $user = getUserByEmailAndPassword($email, $password);
        
            // check if user was found or not
            if ($user != false) {
                // user is found
                $response["user"] = $user;
            } else {
                // user is not found with the credentials
                $response["error"] = true;
                $response["error_msg"] = "Le credenziali di accesso non sono corrette. Riprova.";
            }
        } else {
            // required post params is missing
            $response["error"] = true;
            $response["error_msg"] = "I parametri email e password sono obbligatori.";
        }
    }

    // Registra un nuovo utente date le credenziali
    function registrazione(){
        global $response;

        if (isset($_POST["email"]) && isset($_POST["password"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];

            if (emailAlreadyUsed($email)) {
                $response["error"] = true;
                $response["error_msg"] = "L'email inserita è già in uso. Riprova.";
                return;
            }

            $user = storeUser($email, $password);
            if ($user) {
                $response["user"] = $user;

                $_POST["codice"] = $user["codice"];

                $result = email_registrazione();

                $response["email"] = $result;
            } else {
                $response["error"] = true;
                $response["error_msg"] = "Si è verificato un errore imprevisto durante la registrazione del nuovo utente.";
            }
        } 
        else
        {
            $response["error"] = true;
            $response["error_msg"] = "I parametri richiesti sono obbligatori.";
        }
    }

    function verifica() {
        global $response;

        if (isset($_GET['e']) && isset($_GET['c'])) 
        {
            $email = $_GET["e"];
            $codice = $_GET["c"];

            $result = accountVerification($email, $codice);

            if ($result == 1) {
                $response["error"] = true;
                $response["error_msg"] = "Questo account non può essere verificato.";
            }
        } else {
            // required post params is missing
            $response["error"] = true;
            $response["error_msg"] = "I parametri richiesti sono obbligatori."; 
        }
    }

    function email_registrazione() {
        global $response;

        if(isset($_POST["email"]) && isset($_POST["codice"])) {
            require_once "Helper_Functions.php";
            $helper = new Helper_Functions();

            $email = $_POST["email"];
            $codice = $_POST["codice"];

            // Mail it
            $result = $helper->sendEmail($email, $codice);

            if (!$result) {
                $response["error"] = true;
                $response["error_msg"] = "Non è stato possibile inviare l'email.";
            }
        } else {
            $response["error"] = true;
            $response["error_msg"] = "I parametri richiesti sono obbligatori.";
        }

        return $result;
    }

    function email_recupero(){
        global $response, $db;

        if(isset($_POST["email"])) {
            $email = $_POST["email"];

            // Prepare statement
            $stmt = $db->conn->prepare("SELECT codice_conferma FROM utenti WHERE email = ?");
    
            $stmt->bind_param("s", $email);
            
            // Try to execute prepered statement
            if ($stmt->execute()) {
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
    
                // verifying user password
                $codice = $user['codice_conferma'];
            }

            if (!$codice) {
                $response["error"] = true;
                $response["error_msg"] = "L'email inserita non è corretta. Riprova.";
                return;
            }

            require_once "Helper_Functions.php";
            $helper = new Helper_Functions();

            // Mail it
            $result = $helper->sendEmailRecupero($email, $codice);

            if (!$result)
            {
                $response["error"] = true;
                $response["error_msg"] = "Non è stato possibile inviare l'email.";
            }
            else
                $response["codice"] = $codice;
        } else {
            $response["error"] = true;
            $response["error_msg"] = "I parametri richiesti sono obbligatori.";
        }
    }

    function cambia_password(){
        global $response;

        if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["codice"]))
        {
            $email = $_POST["email"];
            $password = $_POST["password"];
            $codice = $_POST["codice"];

            if (!changePassword($email, $password, $codice))
            {
                $response["error"] = true;
                $response["error_msg"] = "Errore imprevisto durante l'aggiornamento della password.";
            }
        }
        else
        {
            $response["error"] = true;
            $response["error_msg"] = "I parametri richiesti sono obbligatori.";
        }
    }

    // Ritorna l'utente con le credenziali passate
    function getUserByEmailAndPassword($email, $password) {
        global $db;

        // Prepare statement
        $stmt = $db->conn->prepare("SELECT * FROM utenti WHERE email = ?");
 
        $stmt->bind_param("s", $email);
        
        // Try to execute prepered statement
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // verifying user password
            $db_password = $user['password'];
            
            // check for password equality
            if (password_verify($password, $db_password)) {
                // user authentication details are correct
                return $user;
            }
        }
        return null;
    }

    // Inserisce un nuovo utente nel database
    function storeUser($email, $password) {
        global $db;

        $db_password = password_hash($password, PASSWORD_BCRYPT); // encrypted password
        $codice = genera_codice();
        $data = date("Y-m-d");

        // prepare and execute statement to insert new user in DB
        $stmt = $db->conn->prepare("INSERT INTO utenti(email, password, codice_conferma, data_registrazione) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $db_password, $codice, $data);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $user["email"] = $email;
            $user["codice"] = $codice;
            return $user;
        }

        return false;
    }

    // Verifica l'account dell'utente
    function accountVerification($email, $codice) {
        global $db;
        
        // check if it's already verified
        $new_codice = genera_codice();

        // Prepare statement
        $stmt = $db->conn->prepare("UPDATE utenti SET verificato = 1, codice_conferma = ? WHERE email = ? AND codice_conferma = ? AND verificato = 0");

        $stmt->bind_param("sss", $new_codice, $email, $codice);

        // Try to execute prepered statement
        if ($stmt->execute()) 
            return 0;
            
        return 1;
    }

    function changePassword($email, $password, $codice) {
        global $db;

        $new_codice = genera_codice();

        $db_password = password_hash($password, PASSWORD_BCRYPT); // encrypted password

        // Prepare statement
        $stmt = $db->conn->prepare("UPDATE utenti SET password = ?, codice_conferma = ? WHERE email = ? AND codice_conferma = ?");
 
        $stmt->bind_param("ssss", $db_password, $new_codice, $email, $codice);
        
        // Try to execute prepered statement
        if ($stmt->execute()) 
        {
            $result = $stmt->affected_rows;
            $stmt->close();
            return $result > 0;
        }
        
        return false;
    }

    function emailAlreadyUsed($email) {
        global $db;

        $stmt = $db->conn->prepare("SELECT email from utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        $num_rows = $stmt->num_rows;
        $stmt->close();

        return ($num_rows > 0); // return true if email exist, false otherwise
    }

    function genera_codice() {
        return substr(md5(uniqid("")),8,6);
    }

?>