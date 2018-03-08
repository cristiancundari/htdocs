<?php
    require_once "DB_Functions.php";
    $db = new DB_Functions();
    
    // json response array
    $response = array("error" => false);

    if (isset($_POST["nome"]) && isset($_POST["cognome"]) && isset($_POST["email"]) && isset($_POST["password"]))
    {
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        if ($db->emailAlreadyUsed($email))
        {
            $response["error"] = true;
            $response["error_msg"] = "L'email inserita è già in uso, riprova.";
        }
        else
        {
            $user = $db->storeUser($nome, $cognome, $email, $password);
            if ($user)
                $response["user"] = $user;
            else{
                $response["error"] = true;
                $response["error_msg"] = "Si è verificato un errore imprevisto durante l'inserimento del nuovo utente";
            }

        }

        

    } 
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "Dati richiesti mancanti";
    }

    echo json_encode($response);
?>