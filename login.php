<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();
    
    // json response array
    $response = array("error" => FALSE);
    
    // check if username and password was passed through POST method
    if (isset($_POST['username']) && isset($_POST['password'])) 
    {
        // receiving the post params
        $username = $_POST['username'];
        $password = $_POST['password'];
    
        // get the user by username and password
        $user = $db->getUserByUsernameAndPassword($username, $password);
    
        // check if user was found or not
        if ($user != false) {
            // user is found
            $response["error"] = FALSE;
            $response["user"]["id"] = $user["id"];
            $response["user"]["nome"] = $user["nome"];
            $response["user"]["cognome"] = $user["cognome"];
            $response["user"]["username"] = $user["username"];
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
        $response["error_msg"] = "I parametri username e password sono obbligatori.";
        echo json_encode($response);
    }
?>