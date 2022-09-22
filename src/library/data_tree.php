<?php // /** @noinspection DuplicatedCode */

require_once __DIR__ . '/../library/json_parser.php';
require_once __DIR__ . '/../library/load_ini.php';


/**
 * Renvois un string HTML contenant l'arbre de données.
 * @param string $path
 * @return string
 */
function show_all_data_tree_in_detail(string $path): string
{
    $cookie_data = cookie_to_array('param-list');

    $data_string = file_get_contents($path);
    try {
        $data = json_decode($data_string, true);
        if ($data === null) {
            throw new Exception('JSON parse error : ' . $path);
        }
    }
    catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }

    $result_str = '<ol>';
    $data_children_count = count($data['children']);
    $main_level = 'A';
    $main_id = 0;

    foreach (range(0, $data_children_count - 1) as $i)
    {
        $main_id = $main_id + 1;

        $result_str .= show_data_tree_in_detail(get_data_from_index($data, $i), $main_level,
            $main_level.$main_id, $cookie_data);
    }
    $result_str .= '</ol>';
    return $result_str;
}

/** Fonction principale de création de l'arborescence de données
 * Utilise la récursivité pour créer l'arborescence
 * @param array|string $data
 * @param string $level
 * @param string $final_id
 * @param array $cookie_data
 * @return string
 */
function show_data_tree_in_detail($data, string $level, string $final_id = '', array $cookie_data): string
{

    $result = '';

    # Utilisé pour sauter les arrays inutiles avec uniquement le nom

    if (is_array($data)){
        if (array_key_exists('name', $data) && count($data) <= 2) {
            foreach ($data as $key => $value) {
                $result .= show_data_tree_in_detail($value, $level, $final_id, $cookie_data);
                $final_id++;
            }
            return $result;

            # Utilisé pour sauter les arrays vides
        } elseif (!array_key_exists('name', $data)) {
            foreach ($data as $key => $value) {
                $result .= show_data_tree_in_detail($value, $level, $final_id, $cookie_data);
                $final_id++;
            }
            return $result;
        }
    }
    else
        return $result;

    $li_id = $final_id;

    $level++;
    $id = 1;

    $final_id = "{$final_id}_{$level}{$id}";

    $button_html_start = "<button class=\"btn btn-primary\" type=\"button\" data-bs-toggle=\"collapse\"
                        data-bs-target=#{$final_id} aria-expanded=\"false\" aria-controls={$final_id}>";
    $button_html_end = '</button>';
    $button_expand_start = "<button type=\"button\" id=col_{$final_id} class=\"btn btn-primary btn-custom-toggle\" data-name-changed=0
                        >Développer tout</button>";
    $button_expand_end = '</button>';
    $card_html_start = "<div class=\"collapse\" id={$final_id}><div class=\"card card-body\">";
    $card_html_end = '</div></div>';

    $result .= $button_html_start;

    #If...Elseif...Else utilisé pour déterminer le nom du bouton
    if (key_exists('mkey', $data)){
        $result .= $data['mkey'];
    }
    elseif (key_exists('name', $data)){
        $result .= $data['name'];
    }
    else{
        $result .= '<span class="text-danger">Erreur</span>';
    }

    $result .= $button_html_end;

    if ($level == 'B')
    {
        $result .= $button_expand_start;
        $result .= $button_expand_end;
    }

    $result .= $card_html_start;

    foreach ($data as $key => $value) {

        if ($key != 'children' && is_string($key) &&
            //in_array($key, $cookie_data['param-list'], $level))
            search_for_data_by_level($key, $cookie_data, $level))
        {
            $result .= "{$key} : {$value}<br>";
        }
        else{
            $id++;
            $result .= show_data_tree_in_detail($value, $level, $final_id, $cookie_data);
            $final_id++;
        }
    }

    $result .= $card_html_end;

    if ($level == 'B')
        return "<li id=li_{$li_id}>{$result}</li>";
    else
        return "<ul id=li_{$li_id}>{$result}</ul>";
}

/**
 * Vérifie si un paramètre est présent dans le tableau en fonction du niveau
 * @param $value
 * @param $cookie_arr
 * @param $level
 * @return bool
 */
function search_for_data_by_level($value, $cookie_arr, $level): bool
{
    // l'utilisation directe de "in_array" dans un "if" multiplie par 5 le temps d'exécution
    if (isset($cookie_arr[$level])) {
        if (in_array($value, $cookie_arr[$level], true))
            return true;
        else
            return false;
    }
    elseif (in_array('allParam1nArray', $cookie_arr, true))
        return true;
    else
        return false;
}

/**
 * Récupère un cookie et le transforme en array
 * @param $name
 * @return array
 */
function cookie_to_array($name): array
{
    if(!empty($_COOKIE[$name]))
    {
        $cookie = $_COOKIE[$name];
        $cookie = stripcslashes($cookie);
        $cookie_arr = json_decode($cookie, true);

        $final_arr = [];
        foreach ($cookie_arr as $key => $value) {
            $level = substr($key, 4, 1);
            $final_arr[++$level][] = $value;
        }
        return $final_arr;
    }
    else
        return ['allParam1nArray' => 'allParam1nArray'];
}

function is_array_of_integers($array): bool
{
    /**
     * Vérifie si un array est uniquement composé de nombres entiers
     */
    foreach ($array as $key => $value) {
        if (!is_int($key))
        {
            return false;
        }
    }
    return true;
}

function is_last_array($array)
{
    /**
     * Cette fonction existe uniquement, car développé pour PHP 7.2
     *  https://www.php.net/manual/fr/function.array-key-last.php
     *  Remplaçable avec array_key_last() dans PHP 7.3+
     */

    if (!is_array($array) || empty($array))
    {
        return null;
    }
    return array_keys($array)[count($array) -1];
}

function get_all_name_by_level_recursive($data, $level): array
    {
        $result = [];
        if ($level == 0)
        {
            $result[] = $data['name'];
        }
        else
        {
            foreach ($data['children'] as $child)
            {
                $result = array_merge($result, get_all_name_by_level_recursive($child, $level - 1));
            }
        }
        return $result;
    }

function get_all_host_name($data): Array
{
    return get_all_name_by_level_recursive($data, 1);
}

function get_all_vs_pool_name($data): Array
{
    return get_all_name_by_level_recursive($data, 2);
}

function get_all_vs_name($data): Array
{
    return get_all_name_by_level_recursive($data, 3);
}

function get_all_pool_name($data): Array
{
    return get_all_name_by_level_recursive($data, 4);
}

function get_all_node_name($data): Array
{
    return get_all_name_by_level_recursive($data, 5);
}