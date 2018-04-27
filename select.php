<?php

    // json response array
    $response = array("error" => FALSE);

    if(isset($_POST["prep_query"]) && isset($_POST["param_type"]) && isset($_POST["params"]))
    {
        require_once "DB_Functions.php";
        $db = new DB_Functions();
    
        // Prepare statement
        $stmt = $db->conn->prepare($_POST["prep_query"]);

        $params = json_decode($_POST["params"]);
 
        if(count($params))
            $stmt->bind_param($_POST["param_type"], ...$params);

        // Try to execute prepered statement
        if ($stmt->execute())
        {
            $hasResult = $stmt->get_result();
            if ($hasResult)
            {
                $result = $hasResult->fetch_all(MYSQLI_ASSOC);
            }
            else
            {
                $result = true;
                $response["id"] = $db->conn->insert_id;
            }
            $stmt->close();

            if($result)
            {
                $response["result"] = $result;
            }
            else
            {
                $response["error"] = true;
                $response["error_msg"] = "Nessun record corrisponde ai criteri di ricerca";
            }
        }
        else
        {
            $response["error"] = true;
            $response["error_msg"] = "Si è verificato un errore imprevisto, riprova.";
        }
    }
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>