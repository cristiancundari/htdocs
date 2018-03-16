<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();
    
    // json response array
    $response = array("error" => False);
    
    // check if email and password was passed through POST method
    if (isset($_POST['email']) && isset($_POST['password'])) 
    {
        // receiving the post params
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        // get the user by email and password
        $user = $db->getUserByEmailAndPassword($email, $password);
    
        // check if user was found or not
        if ($user != false) {
            // user is found
            $response["user"]["id_utente"] = $user["id_utente"];
            $response["user"]["nome"] = $user["nome"];
            $response["user"]["cognome"] = $user["cognome"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["verificato"] = $user["verificato"];
            $response["user"]["codice"] = $user["codice_conferma"];
        } else {
            // user is not found with the credentials
            $response["error"] = True;
            $response["error_msg"] = "Le credenziali di accesso non sono corrette. Riprova.";
        }
    } else {
        // required post params is missing
        $response["error"] = True;
        $response["error_msg"] = "I parametri email e password sono obbligatori.";
    }

    echo json_encode($response);
?>