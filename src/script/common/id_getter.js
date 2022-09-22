/**
 * Récupération de l'id à partir du nom du fichier
 * @return {string}
 */
export function get_id_from_filename(){
    let filename = document.location.pathname.match(/[^\/]+$/)[0];
    let id = filename.replace(/\D/g, '');
    console.log("filename = " + filename + " | id = " + id);
    return id;
}