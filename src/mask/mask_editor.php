<?php

require_once __DIR__ . '/../library/json_parser.php';
require_once __DIR__ . '/../parameters/param_editor.php';


function get_mask_param(string $param_file, int $mask_pos): array
{
    try {
        $arr_param = load_param(get_most_recent_json(dirname($param_file)));
        if ($arr_param === []) {
            throw new Exception('No parameters found');
        }
    } catch (Exception $e) {
        return [];
    }

    // Retire les paramètres non utilisés (données avec juste le nom)
    foreach ($arr_param as $param) {
        if (count($param) < 2) {
            unset($arr_param[array_search($param, $arr_param)]);
        }
    }

    // Renvois le paramètre demandé
    // (0 = hôte, 1 = vsp, 2 = vs, 3 = rsp, 4 = rs)
    $tmp_count = 0;
    foreach ($arr_param as $param) {
        if ($mask_pos == $tmp_count)
            return $param;
        else
            $tmp_count++;
    }
    return [];
}

function mask_to_html(string $param_file, int $mask_num): string
{
    $arr_param = get_mask_param($param_file, $mask_num);
    if ($arr_param === []) {
        return '<p>No parameters found</p>';
    }

    $result = "";
    $depth = chr(65 + $mask_num);
    $id = 0;

    foreach ($arr_param as $param) {
        $id++;

        $result .= "<div class='mb-sm-auto row data-mask'>";
        $result .= "<label for='in_$depth$id' class='col-sm-5 col-form-label label-mask' name='$param'>$param</label>";
        $result .= "<div class='col-sm-6'>"; //Gère la largeur de l'input box
        $result .= "<div class='input-group mb-2'>"; //Gère la séparation entre les inputs box
        $result .= "<div class='input-group-text'>";
        $result .= "<input class='form-check-input checkbox-mask' value='' type='checkbox' aria-label='Checkbox de validation' id='chkval_$depth$id'>";
        $result .= "</div>";
        $result .= "<input type='text' class='form-control text-mask' id='in_$depth$id'>";
        $result .= "</div>";
        $result .= "</div>";
        $result .= "</div>";

    }

    return $result;
}

/**
 * Crée un fichier JSON contenant les paramètres du masque
 * @param array $data_arr Tableau contenant les paramètres du masque
 * @param string $path Chemin du fichier JSON à créer
 * @return string | bool
 */
function generate_json(array $data_arr, string $id, bool $ignore_blanks, string $path= __DIR__ . '/data/'): string
{

    $ignore_blanks_arr = array('ignore_blanks' => $ignore_blanks);

    $data_arr = $ignore_blanks_arr + $data_arr;

    $json = json_encode($data_arr, JSON_PRETTY_PRINT);
    /*
    echo $path;
    echo '\n';
    echo $json;
    echo '\n';*/

    try {
        file_put_contents($path . 'mask' . $id . '.json', $json);
    } catch (Exception $e) {
        return $e->getMessage();
    }
    return 'true';
}

/**
 * Charge un fichier JSON
 */
function load_json_mask(string $id, string $path= __DIR__ . '/data/'): string
{
    if (file_exists($path . 'mask'.$id.'.json'))
        $json = file_get_contents($path . 'mask'.$id.'.json');
    else
        $json = '{}';

    if (gettype($json) !== 'string') {
        return "";
    }
    return $json;
}

/**
 * Supprime un fichier JSON
 */
function delete_json_mask(string $id, string $path= __DIR__ . '/data/'): bool
{
    return unlink($path . 'mask'.$id.'.json');
}