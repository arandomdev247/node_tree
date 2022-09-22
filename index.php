<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FortiView : Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="module" src="src/script/button_action.js"></script>
    <script src="src/html/nav/nav.js"></script>
    <script type="module" src="src/script/index/load_data.js"></script>
    <?php require_once 'src/library/data_tree.php'; ?>
</head>

<body>

<div id="nav-placeholder"></div>

<section>

    <h1>FortiView : Accueil</h1>

    <hr>

    <button type="button" class="btn btn-primary disabled" data-bs-toggle="modal" data-bs-target="#reloadData" id="refresh-data">
        Rafraichir
    </button>
    <button type="button" class="btn btn-primary disabled" id="expand-all-data">
        Tout développer
    </button>
    <button type="button" class="btn btn-primary disabled" id="shrink-all-data">
        Tout réduire
    </button>

    <p><br>
        Nombre de nom de domaine : <span id="counter">...</span>
        <br>
        Fichier affiché : <span id="file-showing">...</span>
    </p>

    <!-- Modal -->
    <div class="modal fade" id="reloadData" tabindex="-1" aria-labelledby="reloadDataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reloadDataLabel">Rafraichir les données</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes vous sûr de vouloir rafraichir les données ? <br>
                    Cette opération peut prendre plusieurs minutes.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            onclick="update_all()">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <div id="refresh-return"></div>

    <hr>

    <div id ="show-data-tree">...</div>

</section>
</body>
</html>