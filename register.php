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
            {
                require_once "Helper_Functions.php";
                $helper = new Helper_Functions();

                $response["user"] = $user;

                $result = $helper->sendEmail($user["email"], $user["nome"], $user["codice_conferma"]);
                $response["email"] = $result;
            }
            else
            {
                $response["error"] = true;
                $response["error_msg"] = "Si è verificato un errore imprevisto durante l'inserimento del nuovo utente";
            }

        }
    } 
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>