<?php

for ($i = 1; $i < 6; $i++) {
    $response[$i]["error"] = true;
    $response[$i]["error_msg"] = "Nessun record corrisponde ai criteri di ricerca";
}

$response[0]["errore"] = "prova errore";

echo json_encode($response);

?>