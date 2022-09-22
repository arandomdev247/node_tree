/**
 * Fichier de chargement et d'enregistrement de données pour la page de paramètres
 */

import {load_cookie_param, reset_cookie_param, save_cookie_param} from "../common/cookie_manager.js";
import {set_status} from "../common/status_writer.js";
import {toggle, toggle_list} from "../button_action.js";

$(document).on('DOMContentLoaded', async() => {
    console.log('write_param');
    set_status('#param-status', 'Chargement des paramètres...', 'secondary');
    console.log('end write_param');
    loading_click_param_event();
    console.log("Loading param page");
    await first_param_load();
    console.log("Param page loaded");
    set_status('#param-status', 'Paramètres chargés', 'primary');
});

function loading_click_param_event(){
    $('#btn-save').click(async() => {await save_btn_click();});
    $('#btn-reset').click(async() => {await reset_btn_click();});
    $('#btn-refresh').click(async() => {await refresh_btn_click();});
    $('#btn-select-all').click(() => {toggle_selection(true);});
    $('#btn-select-none').click(() => {toggle_selection(false);});
}

/**
 * Charge les données lors du premier chargement de la page.
 * Si aucun fichier JSON n'est présent, une tentative de création de fichier JSON est effectuée.
 * @returns {Promise<void>}
 */
async function first_param_load(){
    await reset_param();
    let is_dir_empty = await check_dir();
    if (is_dir_empty < 0){
        console.log("Dossier vide");
        $("#param-list").html("<b>Aucun fichier JSON détecté !</b>");
        set_status('#param-status', 'Aucun fichier JSON détecté !', 'error');
    }
    else{
        console.log("Fichier trouvé : " + is_dir_empty);
        await load_param()
            .then(() => console.log("Fichier chargé"))
            .then(() => load_cookie())
            .then(() => toggle_buttons());
    }
}

function load_cookie(){
    if (load_cookie_param() === false){
        toggle_selection();
    }
}

/**
 * Met en état de chargement les balises HTML en attendant le chargement des données.
 */
function reset_param(){
    $("#param-list").html("<i>Chargement...</i>");
}

/**
 * Charge les données du fichier JSON.
 * @return {Promise<unknown>}
 */
async function load_param(){
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: "src/script/ajax_requests/parameter/get_param.php",
            success: function(result) {
                $("#param-list").html(result);
                return resolve(true);
            }
        })
            .catch(function(error) {
                console.log(error);
                return reject(false);
            })
    });
}

/**
 * Rafraichit les données du fichier JSON.
 * @return {Promise<unknown>}
 */
async function refresh_param(){
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: "src/script/ajax_requests/parameter/get_param_refresh.php",
            success: function(result) {
                if (result === true || result === "1"){
                    return resolve(true);
                }
                else {
                    return reject(false);
                }
            }
        })
            .catch(function(error) {
                console.log(error);
                return reject(false);
            })
    });
}

/**
 * Vérifie que le dossier contenant les fichiers JSON n'est pas vide.
 * @return {Promise<int>}
 */
function check_dir() {
    return new Promise((resolve, reject) =>
    {
        $.ajax({
            url: "src/script/ajax_requests/common/check_dir.php",
            success: function (result) {
                return resolve(result);
            }
        })
            .catch(function () {
                return reject(-1);
            })
    });
}

function save_btn_click(){
    save_cookie_param();
    set_status('#param-status', 'Paramètres sauvegardés', 'success');
}

function reset_btn_click(){
    reset_cookie_param();
    toggle_selection();
    set_status('#param-status', 'Paramètres réinitialisés', 'success');
}

async function refresh_btn_click(){
    reset_param();
    console.log("Refresh param");
    toggle_buttons(false);
    console.log("Buttons disabled");
    await refresh_param()
        .then(() => console.log("Fichier rafraichit"))
        .then(() => load_param())
        .then(() => console.log("Fichier chargé"))
        .then(() => load_cookie_param())
        .then(() => console.log("Cookie chargé"))
        .then(() => toggle_buttons())
        .then(() => console.log("Boutons activés"))
        .then(() => set_status('#param-status', 'Fichier rafraichit', 'success'))
}

/**
 * Active les boutons d'enregistrement et de modification de paramètres.
 */
function toggle_buttons(enable = true) {
    $('.buttons button').each(function () {
        $(this).toggleClass('disabled', !enable);
    });
}

function toggle_selection(checked_value=true){

    if (typeof checked_value != 'boolean'){
        checked_value = true;
    }

    let tbody_selector = $('#param-list>table>tbody');

    let max_depth = tbody_selector.find('tr:first td').length;
    let depth = 0;
    let id = 1;

    while(depth < max_depth) {
        let id_name = $('#chk_' + String.fromCharCode(65 + depth) + id);
        if (id_name.length > 0) {
            let param_name = id_name.attr('data');
            id++;
            id_name.prop('checked', checked_value);
        }
        else {
            depth++;
            id = 1;
        }
    }
}

/*
TODO : Faire le fichier JS pour le chargement via AJAX
Penser à utiliser load_data.js comme référence
 */