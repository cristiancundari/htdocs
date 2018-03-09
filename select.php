<?php

    // json response array
    $response = array("error" => FALSE);

    if(isset($_POST["prep_query"]) && isset($_POST["param_type"]) && isset($_POST["params"]))
    {
        require_once "DB_Functions.php";
        $db = new DB_Functions();
    
        // Prepare statement
        $stmt = $this->conn->prepare($_POST["prep_query"]);

        $params = json_decode($_POST["params"]);
 
        $stmt->bind_param($_POST["param_type"], ...$params);

        die(json_encode($response));

        // Try to execute prepered statement
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $response["result"] = $result;
        }
        else
        {
            $response["error"] = true;
            $response["error_msg"] = "Si è verificato un errore imprevisto, riprova.";
        }
    }

    echo json_encode($response);
?>