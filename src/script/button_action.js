/**
 * Fichier JS utilisé pour la gestion des actions liées aux boutons sur la page d'accueil
 */


/**
 * Change le nom du bouton lorsque l'on clique dessus et développe ou minimise tout son contenu en fonction de son état
 * @param a_button
 */
export function toggle(a_button) {


    if (a_button.getAttribute('data-name-changed') === '0') {
        a_button.setAttribute('data-name-changed', 'checked');
        a_button.innerHTML = 'Réduire tout';
        toggle_list(a_button, false);
    } else {
        a_button.setAttribute('data-name-changed', '0');
        a_button.innerHTML = 'Développer tout';
        toggle_list(a_button, true);
    }

}

export function force_toggle(a_button, is_expanded) {

    if(is_expanded === true)
    {
        a_button.setAttribute('data-name-changed', '0');
        a_button.innerHTML = 'Développer tout';
        toggle_list(a_button, false);
    }
    else
    {
        a_button.setAttribute('data-name-changed', 'checked');
        a_button.innerHTML = 'Réduire tout';
        toggle_list(a_button, true);
    }
}

/**
 * Fonction qui change le nom du bouton lorsque l'on clique dessus
 * is_expanded : true si on doit développer tout, false si on doit réduire tout
 * @param a_button
 * @param is_expanded
 */
export function toggle_list(a_button, is_expanded) {
    let myCollapse = document.getElementById(a_button.id.replace('col_', ''));

    if (is_expanded && myCollapse.getAttribute('class') === 'collapse show')
        new bootstrap.Collapse(myCollapse, {toggle: true});
     else if (!is_expanded && myCollapse.getAttribute('class') === 'collapse')
        new bootstrap.Collapse(myCollapse, {toggle: true});

    let recurseEle = myCollapse.querySelectorAll(".collapse");
    if (recurseEle.length < 1)
        return;

    if(is_expanded)
        for (let i = 0; i < recurseEle.length; i++) {
            if (recurseEle[i].getAttribute('class') === 'collapse show')
                new bootstrap.Collapse(recurseEle[i], {toggle: true});
        }
    else
        for (let i = 0; i < recurseEle.length; i++) {
            if (recurseEle[i].getAttribute('class') === 'collapse')
                new bootstrap.Collapse(recurseEle[i], {toggle: true});
        }
}