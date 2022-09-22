/**
 * Fichier permettant le chargement des données dans la page d'accueil.
 */

import {toggle_list,toggle, force_toggle} from "../button_action.js";

$(document).on('DOMContentLoaded', async() => {
    $('#expand-all-data').click(async() => {await expand_all();});
    $('#shrink-all-data').click(async() => {await shrink_all();});
    $(document).on("click", ".btn-custom-toggle", function (event) {toggle(this);});
    await reset_all();
    await first_load();


});

function set_header_button_status(status) {
    console.log('INFO: set_header_button_status('+status+')');
    if (status) {
        console.log('INFO: set_header_button_status(true)');
        $('#refresh-data').removeClass('disabled');
        $('#expand-all-data').removeClass('disabled');
        $('#shrink-all-data').removeClass('disabled');
    } else {
        console.log('INFO: set_header_button_status(false)');
        $('#refresh-data').addClass('disabled');
        $('#expand-all-data').addClass('disabled');
        $('#shrink-all-data').addClass('disabled');
    }
}

function create_event_for_generated_buttons()
{
    let buttons = $('btn-custom-toggle')
    for(let i = 0; i < buttons.length; i++)
    {
        $(buttons[i]).click(async() => {await toggle(buttons[i]);});
    }
}

/** Fonction permettant d'étendre toutes les sections de données.
 *
 * @return {Promise<void>}
 */
async function expand_all() {
    await set_header_button_status(false);
    let all_buttons = $('.btn-custom-toggle');
    for (let i = 0; i < all_buttons.length; i++) {
        await force_toggle(all_buttons[i], true);
    }
    await set_header_button_status(true);
}

async function shrink_all() {

    await set_header_button_status(false);
    let all_buttons = $('.btn-custom-toggle');
    for (let i = 0; i < all_buttons.length; i++) {
        await force_toggle(all_buttons[i], false);
    }
    await set_header_button_status(true);

}

/**
 * Charge les données lors du premier chargement de la page.
 * Si aucun fichier JSON n'est présent, une tentative de création de fichier JSON est effectuée.
 * @returns {Promise<void>}
 */
async function first_load() {

    let is_dir_empty = await check_dir();
    if (is_dir_empty < 0) {
        console.log('Dossier vide');
        $("#refresh-return").html("<b>Aucun fichier trouvé. Génération du fichier en cours...</b>");
        await load_tree().then(first_load);
    }
    else {
        console.log("Fichier trouvé");
        await load_counter();
        await load_file_showing();
        await load_data_tree();
        set_header_button_status(true);

        console.log("Données chargées !");
    }
}

/**
 * Met à jour toutes les données de la page.
 * @returns {Promise<void>}
 */

async function update_all() {
    console.log("Mise à jour des données");
    await set_header_button_status(false);
    await reset_all();
    await load_tree();
    await load_counter()
    await load_file_showing();
    await load_data_tree();
    await set_header_button_status(true);
    console.log("Loaded all data");
}

function get_cookie(cname){
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/**
 * Réinitialise les <div> et <span> contenant les données.
 * @return {Promise<void>}
 */

async function reset_all()  {
    $("#counter").html(" <i>Chargement...</i>");
    $("#file-showing").html(" <i>Chargement...</i>");
    $("#show-data-tree").html(" <i>Chargement...</i>");
}

/**
 * Commande de génération d'un fichier JSON.
 * @return {Promise<unknown>}
 */
async function load_tree() {
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: "src/library/exec_tree_script.php",
            success: function (result) {
                $("#refresh-return").html(result);
                return resolve(true);
            }
        })
            .catch(function (err) {
            return reject(err);
            })
    });
}

/**
 * Charge le chiffre affichant le nombre de sites présent dans le fichier JSON.
 * @return {Promise<void>}
 */
async function load_counter() {
    $("#counter").ready(function () {
        $.ajax({
            url: "src/script/ajax_requests/index/get_counter.php",
            success: function (result) {
                $("#counter").html("<b>" + result + "</b>");
            }
        })
    })
}

/**
 * Vérifie que le dossier contenant les fichiers JSON n'est pas vide.
 * @return {Promise<int>}
 */
async function check_dir() {
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

/**
 * Affiche le nom du fichier JSON récupéré.
 * @return {Promise<void>}
 */
async function load_file_showing() {
    $("#file-showing").ready(function () {
        $.ajax({
            url: "src/script/ajax_requests/index/get_file_showing.php",
            success: function (result) {
                $("#file-showing").html("<b>" + result + "</b>");
            }
        })
    })
}

/**
 * Affiche les données du fichier JSON.
 * @return {Promise<void>}
 */
async function load_data_tree() {
    return new Promise( (resolve, reject) =>
    {
    //$("#show-data-tree").ready(function () {
        $.ajax({
            url: "src/script/ajax_requests/index/get_load_data_tree.php",
            success: function (result) {
                $("#show-data-tree").html(result);
                return resolve(true);
            }
        })
            .catch(function (err) {
                return reject(err);
            })
    });
}