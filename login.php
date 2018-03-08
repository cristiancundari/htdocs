<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();
    
    // json response array
    $response = array("error" => FALSE);
    
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
            $response["error"] = FALSE;
            $response["user"]["id"] = $user["id"];
            $response["user"]["nome"] = $user["nome"];
            $response["user"]["cognome"] = $user["cognome"];
            $response["user"]["email"] = $user["email"];
            echo json_encode($response);
        } else {
            // user is not found with the credentials
            $response["error"] = TRUE;
            $response["error_msg"] = "Le credenziali di accesso non sono corrette. Riprova.";
            echo json_encode($response);
        }
    } else {
        // required post params is missing
        $response["error"] = TRUE;
        $response["error_msg"] = "I parametri email e password sono obbligatori.";
        echo json_encode($response);
    }
?>