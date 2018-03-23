<?php
    // json response array
    $response = array();

    if(isset($_POST["prep_query"]) && isset($_POST["param_type"]) && isset($_POST["params"]))
    {
        require_once "DB_Functions.php";
        $db = new DB_Functions();
    
        $prep_querys = json_decode($_POST["prep_query"]);
        $param_types = json_decode($_POST["param_type"]);
        $params = json_decode($_POST["params"]);

        for ($i = 0; $i < count($prep_querys); $i++)
        {
            // Prepare statement
            $stmt = $db->conn->prepare($prep_querys[$i]);

            $paramsi = $params[$i];
    
            if(count($paramsi))
                $stmt->bind_param($param_types[$i], ...$paramsi);

            // Try to execute prepered statement
            if ($stmt->execute()) 
            {
                $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);;
                $stmt->close();

                if($result)
                {
                    $response[$i]["error"] = false;
                    $response[$i]["result"] = $result;
                }
                else
                {
                    $response[$i]["error"] = true;
                    $response[$i]["error_msg"] = "Nessun record corrisponde ai criteri di ricerca";
                }
            }
            else
            {
                $response[$i]["error"] = true;
                $response[$i]["error_msg"] = "Si è verificato un errore imprevisto, riprova.";
            }
        }
    } else {
        $response[0]["error"] = true;
        $response[0]["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>