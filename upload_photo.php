<?php
    $files = array();
    $keys = array_keys($_FILES);
    for ($i = 0; $i < count($_FILES); $i++) {
        $params = elabora_immagine($_FILES[$keys[$i]]);
        array_push($files, $params);
    }
    echo json_encode($files);

    function elabora_immagine($file) {
        $name = $file["name"];
        $tmp_name = $file["tmp_name"];
        $path_info = pathinfo($name);
        $estensione = $path_info["extension"];
        $file_name = $path_info["filename"];
        $new_name = generate_random_filename("files/", $estensione);
        $new_path = "files/$new_name";

        // Spostare il file temporaneo nella directory files
        $moved = rename($tmp_name, $new_path);
        chmod($new_path, 0644);

        $params = array(
            "file_path" => $new_path,
            "file_name" => $file_name,
            "tmp_name" => $tmp_name,
            "moved" => $moved
        );
        return $params;
    }

    function get_filecount($directory) {
        $filecount = 0;
        $files = glob($directory . "*");
        if ($files) {
            $filecount = count($files);
        }
        return $filecount;
    }

    function generate_random_filename($directory, $extension) {
        while (true) {
            $filename = uniqid('file', true) . '.' . $extension;
            if (!file_exists($directory . $filename)) 
                return $filename;
        }
    }
?>