/**
 * Fichier de gestion des cookies
 */


/**
 * Enregistre un cookie dans un format JSON avec les données de la page de paramètres.
 */
export function save_cookie_param(){

    let tbody_selector = $('#param-list>table>tbody');
    let max_depth = tbody_selector.find('tr:first td').length;
    let depth = 0;
    let id = 1;
    let cookie_list = {};

    while(depth < max_depth) {
        let param_select = $('#chk_' + String.fromCharCode(65 + depth) + id);
        if (param_select.length > 0) {
            try {
                let param_id = param_select.attr('id');
                let param_data = param_select.attr('data');
                let param_value = param_select.is(':checked');
                if (param_value)
                    cookie_list[param_id] = param_data;
            }
            catch (e) {
                console.log(e);
            }
            id++;
        }
        else {
            depth++;
            id = 1;
        }
    }
    let cookie_json = JSON.stringify(cookie_list);

    create_cookie('param-list', cookie_json, 100);

}

/**
 * Crée un cookie avec le nom, la valeur et la durée de vie en année.
 * @param cname
 * @param value
 * @param expiration_years
 */
export function create_cookie(cname, value, expiration_years) {
    let d = new Date();
    d.setTime(d.getTime() + expiration_years*3600*1000*24*365.25);
    let expires = "expires="+ d.toUTCString();

    document.cookie = cname + "=" + value + ";" + expires + ";";
}

/**
 * Récupère un cookie et renvoi le string.
 * @return {string}
 * @param cname
 */
export function get_cookie(cname){
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/**
 * Récupère le cookie et applique les données présentes à l'intérieur.
 */
export function load_cookie_param() {
    const cookie_data = get_cookie("param-list");

    if (cookie_data == null || cookie_data === "") {
        save_cookie_param();
        return false;
    } else {

        let cookie_parsed = JSON.parse(cookie_data);

        for (let value in cookie_parsed) {

            let param_id = value;
            let param_data = cookie_parsed[value];

            $('#' + param_id).prop('checked', true);
            $('#' + param_id).attr('data', param_data);
        }
        return true;
    }
}

/**
 * Réinitialise les paramètres à leurs valeurs par défaut.
 */
export function reset_cookie_param(){
    console.log("Resetting cookies...");
    document.cookie = 'param-list=; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
    console.log("Cookie reset");
    console.log(document.cookie);
}