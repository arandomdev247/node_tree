/**
 * Fichier de chargement des différents masques
 */

import {set_status} from "../common/status_writer.js";
import {get_id_from_filename} from '../common/id_getter.js';

$(document).on('DOMContentLoaded', async() => {
    console.log("Loading mask page");
    const mask_id = get_id_from_filename();
    set_status('#mask-status', "Chargement des paramètre du masque...", 'secondary');
    await first_mask_load();
    set_status('#mask-status', "Chargement des paramètres enregistrés...", 'secondary');
    await load_mask_param(mask_id);
    await loading_click_mask_event(mask_id);
    set_status('#mask-status', "Chargement terminé !", 'primary');
    console.log("Mask page loaded");
});

/**
 * Fonction de création des events sur les boutons de la page des masques
 * @return {Promise<void>}
 */
async function loading_click_mask_event(mask_id){
    $('#btn-mask-save').click(async() => {await save_mask(mask_id);});
    $('#btn-mask-reset').click(async() => {await reset_mask(mask_id);});
    $('#btn-host-select-all').click(async() => {await toggle_checkboxes('#host', true);});
    $('#btn-host-select-none').click(async() => {await toggle_checkboxes('#host', false);});
    $('#btn-vsp-select-all').click(async() => {await toggle_checkboxes('#vsp', true);});
    $('#btn-vsp-select-none').click(async() => {await toggle_checkboxes('#vsp', false);});
    $('#btn-vs-select-all').click(async() => {await toggle_checkboxes('#vs', true);});
    $('#btn-vs-select-none').click(async() => {await toggle_checkboxes('#vs', false);});
    $('#btn-rsp-select-all').click(async() => {await toggle_checkboxes('#rsp', true);});
    $('#btn-rsp-select-none').click(async() => {await toggle_checkboxes('#rsp', false);});
    $('#btn-rs-select-all').click(async() => {await toggle_checkboxes('#rs', true);});
    $('#btn-rs-select-none').click(async() => {await toggle_checkboxes('#rs', false);});
}

/**
 * Chargement du masque à l'ouverture de la page
 */
async function first_mask_load()
{
    let err_ret = false;
    let tmp_mask = "";

    for (let id_mask_count = 0; id_mask_count < 5; id_mask_count++) {
        tmp_mask = await get_mask(id_mask_count);
        if (tmp_mask === "" || tmp_mask === null) {
            err_ret = true;
        }

    }
    if (err_ret)
        console.log("Error while loading masks");
    else {
        console.log("Masks loaded");
        toggle_all_buttons();
    }
}

async function load_mask_param(mask_id)
{
    let data_str = await get_mask_load(mask_id);

    if (typeof data_str !== "string" || data_str === "")
        return false;

    let data_arr = JSON.parse(data_str);

    $('#mask-ignore-blanks').prop('checked', data_arr.ignore_blanks);

    for (const dataArrKey in data_arr) {
        $(document).find('#' + dataArrKey).prop('checked', data_arr[dataArrKey]["checked"]);
        $(document).find('#' + dataArrKey.replace('chkval', 'in')).val(data_arr[dataArrKey]["value"]);
    }
    return true;
}

/**
 * Sauvegarde les paramètres du masque
 * @return {Promise<void>}
 */
async function save_mask(mask_id)
{
    console.log("Saving mask");

    let mask_data = {};
    let ignore_blanks = false;

    ignore_blanks = $('#mask-ignore-blanks').prop('checked');

    $('.data-mask', '.mask-list').each(function () {
        let tmp_id_mask = $(this).parent().attr('id');
        let tmp_name = $(this).find('.label-mask').attr('name');
        let tmp_id = $(this).find('.checkbox-mask').attr('id');
        let tmp_checked = $(this).find('.checkbox-mask').prop('checked');
        let tmp_value = $(this).find('.text-mask').val();

        mask_data[tmp_id] = {
            "mask": tmp_id_mask,
            "name": tmp_name,
            "value": tmp_value,
            "checked": tmp_checked,
            "_comment": "Donnee generee pour : '" + tmp_name + "' dans '" + tmp_id_mask + "'"
        };
    });
    console.log(ignore_blanks);
    if (await post_mask(mask_data, mask_id, ignore_blanks)) {
        console.log("Mask saved");
        set_status('#mask-status', "Masque sauvegardé !", "success");
    }
    else {
        console.log("Error while saving mask");
        set_status('#mask-status', "Erreur lors de la sauvegarde du masque !", "error");
    }
}

/**
 * Réinitialise le masque
 * @return {Promise<void>}
 */
async function reset_mask(mask_id)
{
    console.log("Resetting mask");
    $('.data-mask').each(function () {
        $(this).find('.checkbox-mask').prop('checked', false);
        $(this).find('.text-mask').val('')
        });
    let result = await post_mask_delete(mask_id);
    console.log(result);
    if (result == true) {
        console.log("Mask reset");
        set_status('#mask-status', "Masque réinitialisé !", "success");
    }
    else {
        console.log("Error while resetting mask");
        set_status('#mask-status', "Erreur lors de la réinitialisation du masque !", "error");
    }
}

/**
 * Communication avec le serveur pour sauvegarder le masque
 * @param mask_post_data
 * @param mask_id
 * @param ignore_blanks
 * @return {Promise<unknown>}
 */
async function post_mask(mask_post_data, mask_id, ignore_blanks){
    return new Promise((resolve, reject) => {

        let json_string = JSON.stringify(mask_post_data);
        $.ajax({
            type: 'POST',
            url: 'src/script/ajax_requests/mask/post_mask_data.php',
            data: {data: json_string, id: mask_id, ignore_blanks: ignore_blanks},
            cache: false,
            success: function (ret) {
                console.log("return : " + ret);
                if (ret === 'true') {
                    resolve(true);
                }
                else {
                    set_status('#mask-status', "Erreur lors de la sauvegarde du masque !", "error");
                    reject(false);
                }
            }
        })
            .catch(function (err) {
                console.log(err);
                return reject(false);
        })
    });
}

/**
 * Communication avec le serveur pour réinitialiser le masque
 * @param mask_id
 * @return {Promise<unknown>}
 */
async function post_mask_delete(mask_id)
{
    console.log("Mask id : " + mask_id);
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST',
            url: 'src/script/ajax_requests/mask/post_mask_delete.php',
            data: {'id': mask_id},
            cache: false,
            success: function () {
                return resolve(true);
            }
        })
            .catch(function (err) {
                console.log(err);
                return reject(false);
        })
    });
}

/**
 * Communication avec le serveur pour récupérer le masque
 * @param mask_id
 * @return {Promise<unknown>}
 */
async function get_mask_load(mask_id){
    console.log("Mask id : " + mask_id);
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST',
            url: 'src/script/ajax_requests/mask/get_mask_load.php',
            data: {'id': mask_id},
            cache: false,
            success: function (data) {
                return resolve(data);
            }
        })
            .catch(function (err) {
                console.log(err);
                return reject(false);
        })
    });
}

/*
/**
 * Affichage du status de chargement et de modification du masque
 * @param status
 * @param type
function set_status(status, type="default")
{
    if (type === "error")
        $('#mask-status').html("Statut : <span style='color: red'><b>" + status + "</b></span>");
    else if (type === "success")
        $('#mask-status').html("Statut : <span style='color: green'><b>" + status + "</b></span>");
    else if (type === "primary")
        $('#mask-status').html("Statut : <span><b>" + status + "</b></span>");
    else if (type === "secondary")
        $('#mask-status').html("Statut : <span><i>" + status + "</i></span>");
    else
        $('#mask-status').html("Statut : <span>" + status + "</span>");
}
*/


/**
 * Active les checkbox des masques
 */
async function toggle_checkboxes(selector, checked_value=true) {
    if (typeof checked_value != "boolean")
        checked_value = true;
    $('.form-check-input', selector).each(function() {
        $(this).prop('checked', checked_value);
    });
}


/**
 * Active les boutons d'enregistrement et de modification de paramètres.
 */
function toggle_buttons(selector, enable = true){
    $(selector).each(function () {
        $(this).toggleClass('disabled', !enable);
    });
}

function toggle_all_buttons(enable = true){
    $('.buttons button').each(function () {
        $(this).toggleClass('disabled', !enable);
    });
}

async function get_mask(mask_id){

    let mask_url = "src/script/ajax_requests/mask/";
    let mask_html = "";

    switch (mask_id){
        case 0:
            mask_url += "get_mask_host.php";
            mask_html = "#host";
            break;
        case 1:
            mask_url += "get_mask_vsp.php";
            mask_html = "#vsp";
            break;
        case 2:
            mask_url += "get_mask_vs.php";
            mask_html = "#vs";
            break;
        case 3:
            mask_url += "get_mask_rsp.php";
            mask_html = "#rsp";
            break;
        case 4:
            mask_url += "get_mask_rs.php";
            mask_html = "#rs";
            break;
        default:
            mask_url += "get_mask_host.php";
            mask_html = "#host";
            break;
    }

    console.log(mask_url);

    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: mask_url,
            success: function(result) {
                $(mask_html).html(result);
                return resolve(result);
            }
        })
            .catch(function(error) {
                console.log(error);
                return reject(false);
            })
    });
}


/** Affiche tous les attributs d'une requête
 * Utilisé uniquement pour le développement.
 * @param $node
 */
function getAttributes ( $node ) {
    $.each( $node[0].attributes, function ( index, attribute ) {
        console.log(attribute.name+':'+attribute.value);
    } );
}