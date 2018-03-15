<?php
    require_once "Helper_Functions.php";
    $helper = new Helper_Functions();

    $response = array("error" => false);

    if(isset($_POST["email"]))
    {
        $email = $_POST["email"];
        $codice = strtoupper(substr(md5(uniqid("")),8,6));

        // Mail it
        $result = $helper->sendEmailRecupero($email, $codice);

        if (!$result)
        {
            $response["error"] = true;
            $response["error_msg"] = "Non è stato possibile inviare l'email.";
        }
        else
            $response["codice"] = $codice;
    }
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>