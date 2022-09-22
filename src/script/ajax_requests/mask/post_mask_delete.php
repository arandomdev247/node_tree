<?php

require_once '../../../mask/mask_editor.php';

$id = json_decode(stripcslashes($_POST['id']), true);

if (empty($id)) {
    echo('Mask name is empty');
}

$deleted = delete_json_mask($id);

echo $deleted ? 'true' : 'false';