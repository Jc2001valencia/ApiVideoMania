<?php

//require_once("controllers/Api.class_productos.php");
require_once("controllers/Api.class_peliculas.php");
//$apipro = new ApiProductos();
$apimovies = new ApiMovies();

$apimovies->procesarLLamada();
//$apipro->procesarLLamadapro();

?>




