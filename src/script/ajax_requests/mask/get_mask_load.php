<?php

require_once '../../../mask/mask_editor.php';

$id = json_decode(stripcslashes($_POST['id']), true);

if (empty($id))
    echo('{}');
else {
    echo load_json_mask($id);
}