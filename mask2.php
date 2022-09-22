<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config View : Masque 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="src/script/button_action.js"></script>
    <script src="src/html/nav/nav.js"></script>
    <script type="module" src="src/script/mask/load_mask.js"></script>
</head>

<body>
<div id="nav-placeholder"></div>
<section>
    <h1>Masque N°2 : Paramètres</h1>

    <hr>

    <div class="buttons" id="buttons-mask-parameter">
        <button type="button" class="btn btn-success disabled" id="btn-mask-save">Sauvegarder</button>
        <button type="button" class="btn btn-danger disabled" id="btn-mask-reset">Réinitialiser</button>
    </div>

    <div id="mask-status">...</div>

    <hr>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="mask-ignore-blanks">
        <label class="form-check-label" for="mask-ignore-blanks">
            Ignorer les valeurs vides
        </label>
    </div>

    <div class="mask-header">
        <h2>Hôte</h2>
        <div class="buttons" id="buttons-host">
            <button type="button" class="btn btn-primary btn-host disabled" id="btn-host-select-all">Tout sélectionner</button>
            <button type="button" class="btn btn-primary btn-host disabled" id="btn-host-select-none">Tout désélectionner</button>
        </div>
    </div>
    <div class="mask-list" id="host">...</div>
    <hr>
    <div class="mask-header">
        <h2>Virtual Server Pool</h2>
        <div class="buttons" id="buttons-vsp">
            <button type="button" class="btn btn-primary btn-vsp disabled" id="btn-vsp-select-all">Tout sélectionner</button>
            <button type="button" class="btn btn-primary btn-vsp disabled" id="btn-vsp-select-none">Tout désélectionner</button>
        </div>
    </div>
    <div class="mask-list" id="vsp">...</div>
    <hr>
    <div class="mask-header">
        <h2>Virtual Server</h2>
        <div class="buttons" id="buttons-vs">
            <button type="button" class="btn btn-primary btn-vs disabled" id="btn-vs-select-all">Tout sélectionner</button>
            <button type="button" class="btn btn-primary btn-vs disabled" id="btn-vs-select-none">Tout désélectionner</button>
        </div>
    </div>
    <div class="mask-list" id="vs">...</div>
    <hr>
    <div class="mask-header">
        <h2>Real Server Pool</h2>
        <div class="buttons" id="buttons-rsp">
            <button type="button" class="btn btn-primary btn-rsp disabled" id="btn-rsp-select-all">Tout sélectionner</button>
            <button type="button" class="btn btn-primary btn-rsp disabled" id="btn-rsp-select-none">Tout désélectionner</button>
        </div>
    </div>
    <div class="mask-list" id="rsp">...</div>
    <hr>
    <div class="mask-header">
        <h2>Real Server</h2>
        <div class="buttons" id="buttons-rs">
            <button type="button" class="btn btn-primary btn-rs disabled" id="btn-rs-select-all">Tout sélectionner</button>
            <button type="button" class="btn btn-primary btn-rs disabled" id="btn-rs-select-none">Tout désélectionner</button>
        </div>
    </div>
    <div class="mask-list" id="rs">...</div>


</section>

</body>
</html>