<?php

require_once __DIR__ . '/../library/json_parser.php';
require_once __DIR__ . '/../mask/mask_editor.php';


function load_mask_summary(array $data, string $mask_id): string{

    $result = "";

    $data = $data['children'];

    $json_mask = json_decode(load_json_mask($mask_id), true);
    if (!empty($json_mask)){
        $result = summary_generator($data, $json_mask);
    }

    return $result;
}

function summary_generator(array $data, array $mask): string
{
    try {
        $filtered_data = error_finder($data, $mask);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    if (empty($filtered_data)){
        return "Toutes les valeurs sont valides";
    }

    $mask_id = 1;

    $result = "<table id='table-summary'>";
    $result .= '<thead>';
    $result .= "<tr>";
    $result .= "<th>N°</th>";
    $result .= "<th>Chemin</th>";
    $result .= "<th>Clé</th>";
    $result .= "<th>Valeur</th>";
    $result .= "<th>Filtre</th>";
    $result .= "</tr>";
    $result .= "</thead>";

    $result .= "<tbody>";
    foreach ($filtered_data as $key => $value) {
        $result .= "<td>".$mask_id++."</td>";
        $result .= "<td class='path-of-mask'>".$key."</td>";
        $result .= "<td>".$value['key']."</td>";
        $result .= "<td>".$value['value']."</td>";
        $result .= "<td>".$value['filter']."</td>";
        $result .= "</tr>";
    }
    $result .= "</tbody>";

    $result .= "</table>";

    return $result;
}


function recursive_keys(array $arr, string $path = "")
{
    foreach ($arr as $key => $val) {
        $new_path = $path;
        if (isset($arr['mkey']) && count($arr) > 2) {
            $new_path .= $arr['mkey'] . ' -> ';
        }
        if (is_array($val)) {
            recursive_keys($val, $new_path);
        } else {
            echo $new_path . $key . ' = ' . $val . '<br>';
        }
    }
}

/**
 * @throws Exception
 */
function error_finder(array $data, array $mask = array()): array
{
    $final_result = array();

    $ignore_blanks = $mask['ignore_blanks'] ?? false; // Si ignore_blanks est défini, on ignore les valeurs vides
    unset($mask['ignore_blanks']); // On supprime ignore_blanks du mask pour ne pas l'utiliser dans le filtre

    foreach ($mask as $key => $value)
        if ($value['checked']) {
            $level = str_replace('chkval_', '', $key)[0];
            if (strlen($level) != 1)
                throw new Exception("Erreur dans le masque");
            $level = ord($level) - ord('A');
            if ($level < 0)
                throw new Exception("Erreur dans le masque");

            $final_result = array_merge($final_result, error_finder_recurse($data, $ignore_blanks, $value['value'], $value['name'], $level));
        }

    return $final_result;

    //TODO : Lire le tableau et comparer avec le masque, si checked, vérifier. Si vérification ne correspond pas, mettre en rouge.

}

function error_finder_recurse(array $data, bool $ignore_blanks = false, string $value_to_filter = '',
                              string $key_to_filter = '', int $level=0, int $current_level=0, string $path = '',
                              array $result = array()): array
{
    $new_result = $result;
    $new_path = $path;

    if (array_key_exists('name', $data) && count($data) <= 2) {
        foreach ($data as $key => $val)
            if (is_array($val))
                return (array_merge($new_result, error_finder_recurse($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, $current_level, $new_path, $result)));
    }
    elseif(!array_key_exists('name', $data)) {
        foreach ($data as $key => $val)
            $new_result = array_merge($new_result, error_finder_recurse($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, $current_level, $new_path, $result));
        return $new_result;
    }
    else
    {
        if (isset($data['mkey']) && count($data) > 2) {
            $new_path .= $data['mkey'] . ' -> ';
        }
        foreach ($data as $key => $val) {

            if (is_array($val) && $current_level < $level) {
                $new_result = array_merge($new_result, error_finder_recurse($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, ++$current_level, $new_path, $result));
            } elseif ($current_level == $level) {
                if ($key === $key_to_filter &&
                    !(strval($val) === $value_to_filter) &&
                    !($ignore_blanks && is_blank($val))) {
                    $new_result[$new_path] = ['key' => $key, 'value' => strval($val), 'filter' => $value_to_filter];
                }
            }
        }
        return $new_result;
    }
    return $new_result;
}

function is_blank($val): bool
{
    return $val === '::' || $val === 0 || $val === '';
}

function mask_finder(array $arr, string $value_to_filter = '', string $key_to_filter = '', string $path = '')
{
    foreach ($arr as $key => $val) {
        $new_path = $path;
        if (isset($arr['mkey']) && count($arr) > 2) {
            $new_path .= $arr['mkey'] . ' -> ';
        }
        if (is_array($val)) {
            mask_finder($val, $value_to_filter, $key_to_filter, $new_path);
        } else {

            if (custom_str_contains($key, $key_to_filter) &&
                custom_str_contains($val, $value_to_filter) !== false) {
                echo $new_path . $key . ' = ' . $val . '<br>';
            }
        }
    }
}

function mask_finder_v2(array $arr, string $value_to_filter, string $key_to_filter,
                        string $path = '', string $result = ''): string
{
    $new_result = $result;
    foreach ($arr as $key => $val) {

        $new_path = $path;
        if (isset($arr['mkey']) && count($arr) > 2) {
            $new_path .= $arr['mkey'] . ' -> ';
        }
        if (is_array($val)) {
            $new_result .= mask_finder_v2($val, $value_to_filter, $key_to_filter, $new_path, $result);
        } else {

            if (custom_str_contains($key, $key_to_filter) &&
                custom_str_contains($val, $value_to_filter) !== false) {
                $new_result .= $new_path . $key . ' = ' . $val . '<br>';
            }
        }
    }
    return $new_result;
}

function mask_finder_v3(array $arr, string $value_to_filter, string $key_to_filter,
                        string $path = '', array $result = array()): array
{
    $new_result = $result;
    foreach ($arr as $key => $val) {

        $new_path = $path;
        if (isset($arr['mkey']) && count($arr) > 2) {
            $new_path .= $arr['mkey'] . ' -> ';
        }
        if (is_array($val)) {
            $new_result = array_merge($new_result, mask_finder_v3($val, $value_to_filter, $key_to_filter, $new_path, $result));
        } else {

            if (custom_str_contains($key, $key_to_filter) &&
                custom_str_contains($val, $value_to_filter) !== false) {
                $new_result[$new_path] = $key . ' = ' . $val;
            }
        }
    }
    return $new_result;
}


/**
 * Filtre et affiche le chemin des clés d'un tableau (UTILISE ECHO)
 * @param array $arr
 * @param string $filter
 * @param string $path
 * @return void
 *
 */
function recursive_key_filtered(array $arr, string $filter = "", string $path = "")
{
    foreach ($arr as $key => $val) {
        $new_path = $path;
        if (isset($arr['mkey']) && count($arr) > 2) {
            $new_path .= $arr['mkey'] . ' -> ';
        }
        if (is_array($val)) {
            recursive_key_filtered($val, $filter, $new_path);
        } else {
            //echo (custom_str_contains($new_path, $filter) ? 'true' : 'false') . ' :  ' . $key . ' = ' . $filter . ' | ';
            if (custom_str_contains($val, $filter) !== false) {
                echo $new_path . $key . ' = ' . $val . '<br>';
            }
            //else
            //echo 'NOT PRESENT !!! ' . $new_path . $key . ' = ' . $val . '<br>';
        }
    }
}



function custom_str_contains($haystack, $needle): bool
{
    return empty($needle) || strpos($haystack, $needle) !== false;
}