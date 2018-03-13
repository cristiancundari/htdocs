<?php
    require_once "Helper_Functions.php";
    $helper = new Helper_Functions();

    $response = array("error" => FALSE);

    if(isset($_POST["email"]) && isset($_POST["nome"]) && isset($_POST["codice"]))
    {
        $email = $_POST["email"];
        $nome = $_POST["nome"];
        $codice = $_POST["codice"];

        // Mail it
        $result = $helper->sendEmail($email, $nome, $codice);

        if (!$result)
        {
            $response["error"] = true;
            $response["error_msg"] = "Non è stato possibile inviare l'email.";
        }
    }
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>