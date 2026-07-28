<?php

/*
|--------------------------------------------------------------------------
| Routes API racine
|--------------------------------------------------------------------------
|
| Vide tant que le module Api (flag `public_api`) n'est pas activé.
| Ses routes vivront dans Modules/Api/routes/api.php, chargées uniquement
| si modules_statuses.json["Api"] = true ET Feature::active('public_api').
|
*/
