<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();

    // json response array
    $response = array("error" => FALSE);

    // check if email and verification_code was passed through POST method
    if (isset($_GET['e']) && isset($_GET['c'])) 
    {
        $email = $_GET["e"];
        $codice = $_GET["c"];

        $result = $db->accountVerification($email, $codice);

        switch ($result) {
            case 1:
                $response["error"] = true;
                $response["error_msg"] = "Si è verificato un errore imprevisto durante la verifica dell'account.";
                break;
            case 2:
                $response["error"] = true;
                $response["error_msg"] = "L'account è già stato verificato.";
                break;
        }
    } else {
        // required post params is missing
        $response["error"] = TRUE;
        $response["error_msg"] = "I parametri richiesti sono obbligatori"; 
    }
    
    echo json_encode($response);
?>