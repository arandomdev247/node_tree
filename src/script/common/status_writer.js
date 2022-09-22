/**
 * Permettant la modification du statut du programme.
 */

/**
 * Affichage du status de chargement et de modification du masque
 * @param status_message Message à afficher
 * @param status_id ID HTML du statut
 * @param type Type de message à afficher (success, error, primary, secondary)
 */
export function set_status(status_id, status_message, type="default")
{
    if (type === "error")
        $(status_id).html("Statut : <span style='color: red'><b>" + status_message + "</b></span>");
    else if (type === "success")
        $(status_id).html("Statut : <span style='color: green'><b>" + status_message + "</b></span>");
    else if (type === "primary")
        $(status_id).html("Statut : <span><b>" + status_message + "</b></span>");
    else if (type === "secondary")
        $(status_id).html("Statut : <span><i>" + status_message + "</i></span>");
    else
        $(status_id).html("Statut : <span>" + status_message + "</span>");
}