<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();

    // json response array
    $response = array("error" => false);

    if (isset($_POST["email"]) && isset($_POST["password"]))
    {
        $email = $_POST["email"];
        $password = $_POST["password"];

        if (!$db->cambiaPassword($email, $password))
        {
            $response["error"] = true;
            $response["error_msg"] = "Errore imprevisto durante l'aggiornamento della password";
        }
    }
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);

?>