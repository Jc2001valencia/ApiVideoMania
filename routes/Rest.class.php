<?php
class Rest {
public $tipo = "application/json";
public $datosPeticion = array();
private $_codEstado = 200;
public function __construct() {
$this->tratarEntrada();
}
public function mostrarRespuesta($data, $estado) {
    $this->_codEstado = ($estado) ? $estado : 200;//si no se envía $estado por defecto será 200
$this->setCabecera();
echo $data;
exit;
}
private function setCabecera() {
header("HTTP/1.1 " . $this->_codEstado . " " . $this->getCodEstado());
header("Content-Type:" . $this->tipo . ';charset=utf-8');
}
private function limpiarEntrada($data) {
$entrada = array();
if (is_array($data)) {
foreach ($data as $key => $value) {
$entrada[$key] = $this->limpiarEntrada($value);
}
} else {
if (true) {
$data = trim(stripslashes($data));
}
//eliminamos etiquetas html y php
$data = strip_tags($data);
//Conviertimos todos los caracteres aplicables a entidades HTML
$data = htmlentities($data);
$entrada = trim($data);
}
return $entrada;
}
private function tratarEntrada()
{
$content_type = null;
$metodo = $_SERVER['REQUEST_METHOD'];
if (isset($_SERVER["CONTENT_TYPE"]))
$content_type = $_SERVER["CONTENT_TYPE"];
switch ($metodo) {
case "GET":
$this->datosPeticion = $this->limpiarEntrada($_GET);
break;
case "POST":
if (count($_POST) > 0) //Valid oque vengan parametros por post tipo Forma
$this->datosPeticion = $this->limpiarEntrada($_POST);
else { //parametros vienen en otro formato
    $body = file_get_contents("php://input");
    if ($content_type != "application/json") {
    parse_str($body, $this->datosPeticion);
    $this->datosPeticion = $this->limpiarEntrada($body);
    } else {
    json_decode($body, true);
    if (json_last_error() == JSON_ERROR_NONE)
    $this->datosPeticion = json_decode($body, true);
    }
    }
    //print_r($this->datosPeticion);
    break;
    case "DELETE": //"falling though". Se ejecutará el case siguiente
    case "PUT":
    //php no tiene un método propiamente dicho para leer una petición PUT o DELETE por lo que se
   // usa un "truco":
    //leer el stream de entrada file_get_contents("php://input") que transfiere un fichero a una
    //cadena.
    //Con ello obtenemos una cadena de pares clave valor de variables
    //(variable1=dato1&variable2=data2...)
    //que evidentemente tendremos que transformarla a un array asociativo.
    //Con parse_str meteremos la cadena en un array donde cada par de elementos es un componente
    //del array.
    parse_str(file_get_contents("php://input"), $this->datosPeticion);
    $this->datosPeticion = $this->limpiarEntrada($this->datosPeticion);
    break;
    default:
    $this->mostrarRespuesta('', 404);
    break;
    }
    //print_r($this->datosPeticion);
    }
    private function getCodEstado() {
    $estado = array(
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    204 => 'No Content',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    500 => 'Internal Server Error');
    $respuesta = ($estado[$this->_codEstado]) ? $estado[$this->_codEstado] : $estado[500];
    return $respuesta;
    }
    }
    ?>