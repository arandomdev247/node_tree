<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="src/html/nav/nav.js"></script>
    <script type="module" src="src/script/parameter/load_param.js"></script>
</head>

<body>
<div id="nav-placeholder"></div>

<section>

    <h1>Paramètres</h1>

    <hr>

    <div class="buttons">
        <button type="button" class="btn btn-success disabled" id="btn-save">Enregistrer</button>
        <button type="button" class="btn btn-danger disabled" id="btn-reset">Réinitialiser</button>
        <button type="button" class="btn btn-primary disabled" data-bs-toggle="modal" data-bs-target="#reloadParam">Rafraichir</button>
        <button type="button" class="btn btn-secondary disabled" id="btn-select-all">Tout sélectionner</button>
        <button type="button" class="btn btn-secondary disabled" id="btn-select-none">Tout désélectionner</button>
    </div>

    <div id="param-status">...</div>

    <hr>

    <!-- Modal -->
    <div class="modal fade" id="reloadParam" tabindex="-1" aria-labelledby="reloadParamLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reloadParamLabel">Rafraichir les paramètres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes vous sûr de vouloir rafraichir les paramètres ? <br>
                    Cette opération peut prendre plusieurs minutes.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="btn-refresh" data-bs-dismiss="modal">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <div id="param-list">...</div>


</section>
</body>
</html>