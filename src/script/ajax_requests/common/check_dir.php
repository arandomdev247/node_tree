<?php

/*
 * Fichier de vérification de l'existence d'un dossier
 */

require_once '../../../library/load_ini.php';

try {
    define("PATH_TO_DATA_FILE", get_data_path_from_forti_requests());
    if (PATH_TO_DATA_FILE == "") {
        echo -1;
    }
    else {
        $files = scandir(PATH_TO_DATA_FILE);
        $files = array_diff($files, array('.', '..'));

        if (!empty($files)) {
            echo count($files);
        }
        else {
            echo -1;
        }
    }
}
catch (Exception $e) {
    echo(-1);
}
