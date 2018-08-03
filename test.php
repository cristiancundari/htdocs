<?php
    // json response array
    $response = array();

    $error = false;

    if(isset($_POST["prep_query"]) && isset($_POST["param_type"]) && isset($_POST["params"]))
    {
        require_once "DB_Functions.php";
        $db = new DB_Functions();
    
        $prep_querys = json_decode($_POST["prep_query"]);
        $param_types = json_decode($_POST["param_type"]);
        $params = json_decode($_POST["params"]);

        $transaction = isset($_POST["transaction"]) ? json_decode($_POST["transaction"])[0] : false;

        if ($transaction === true)
            $db->conn->begin_transaction();

        for ($i = 0; $i < count($prep_querys); $i++)
        {
            $err = true;

            // Prepare statement
            $stmt = $db->conn->prepare($prep_querys[$i]);

            if ($stmt) {

                $paramsi = $params[$i];
        
                if(count($paramsi))
                    $bind = $stmt->bind_param($param_types[$i], ...$paramsi);

                // Try to execute prepered statement
                if ($bind && $stmt->execute())
                {
                    $hasResult = $stmt->get_result();
                    if ($hasResult)
                    {
                        $result = $hasResult->fetch_all(MYSQLI_ASSOC);
                    }
                    else
                    {
                        $result = true;
                        $response["queries"][$i]["id"] = $db->conn->insert_id;
                    }
                    $stmt->close();
                    unset($stmt);

                    $err = false;
                    $response["queries"][$i]["error"] = false;
                    $response["queries"][$i]["result"] = $result;
                }
                else
                {
                    $response["queries"][$i]["error"] = true;
                    $response["queries"][$i]["error_msg"] = "Si è verificato un errore imprevisto: " . $stmt->error;;
                    $response["error_msg"] = $response["queries"][$i]["error_msg"];
                }
            }

            $error = $error || $err;


        }

        if ($transaction === true) {
            if ($error)
                $db->conn->rollback();
            else
                $db->conn->commit();
        }

        $response["error"] = $error;
    } else {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    $db->conn->close();
    unset($db->conn);
    unset($db);

    $stampa = json_encode($response);
    echo $stampa;
?>