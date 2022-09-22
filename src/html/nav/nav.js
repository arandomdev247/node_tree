/**
 * Fichier d'import de la barre de navigation
 */

const htmlNavPath = "src/html/nav/nav.html";

document.addEventListener("DOMContentLoaded", () => {
    const navJs = document.getElementById('nav-placeholder');
    fetch(htmlNavPath)
        .then(response => response.text())
        .then(text=> navJs.innerHTML = text);
});
