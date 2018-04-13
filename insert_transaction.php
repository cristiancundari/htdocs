<?php

    // json response array
    $response = array("error" => FALSE);

    if(isset($_POST["prep_query"]) && isset($_POST["param_type"]) && isset($_POST["params"]))
    {
        require_once "DB_Functions.php";
        $db = new DB_Functions();
    
        $queries = explode(';', $_POST["prep_query"]);
        $param_types = explode(';', $_POST["param_type"]);
        $params = json_decode($_POST["params"]);
        
        $start = 0;

        $db->conn->begin_transaction();

        $result = true;

        $lastId;

        foreach ($queries as $index => $query) {
            $stmt = $db->conn->prepare($query);
            $count = strlen($param_types[$index]);
            $params_slice = array_slice($params, $start, $count);
            $stmt->bind_param($param_types[$index], ...$params_slice);
            if (!$stmt->execute())
            {
                $result = false;
                break;
            }
            $lastId = $db->conn->insert_id;
            $start += $count;
        }

        if($result)
        {
            $db->conn->commit();
            $response["result"] = $result;
            $response["id"] = $lastId;
        }
        else
        {
            $db->conn->rollback();
            $response["error"] = true;
            $response["error_msg"] = "Problema di inserimento";
        }
        $db->conn->close();
    }
    else
    {
        $response["error"] = true;
        $response["error_msg"] = "I parametri richiesti sono obbligatori";
    }

    echo json_encode($response);
?>