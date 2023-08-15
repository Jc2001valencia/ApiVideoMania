<?php
require_once "routes/Rest.class.php";
require_once "models/dbconfig.php";

class ApiMovies extends Rest
{

    private $_metodo;
    private $_argumentos;

    private $_conn = null;

    public function __construct()
    {
        parent::__construct();
        $db = new DBConnect('localhost', 'root', '', 'peliculas');
        $this->_conn = $db->getConexion();
    }

    private function devolverError($id)
    {
        $errores = array(
            array('estado' => "error", "msg" => "petición no encontrada"),
            array('estado' => "error", "msg" => "petición no aceptada"),
            array('estado' => "error", "msg" => "petición sin contenido"),
            array('estado' => "error", "msg" => "email o password incorrectos"),
            array('estado' => "error", "msg" => "error borrando usuario"),
            array('estado' => "error", "msg" => "error actualizando nombre de usuario"),
            array('estado' => "error", "msg" => "error buscando usuario por email"),
            array('estado' => "error", "msg" => "error creando usuario"),
            array('estado' => "error", "msg" => "usuario ya existe"),
            array('estado' => "error", "msg" => "Error actualizando el producto"),
        );
        return $errores[$id];
    }

    public function procesarLLamada()
    {
        if (isset($_REQUEST['url'])) {

            $url = explode('/', trim($_REQUEST['url']));
            $url = array_filter($url);
            $this->_metodo = strtolower(array_shift($url));
            $this->_argumentos = $url;
            $func = $this->_metodo;
            if ((int) method_exists($this, $func) > 0) {
                if (count($this->_argumentos) > 0) {
                    call_user_func_array(array($this, $this->_metodo), $this->_argumentos);
                } else { //si no lo llamamos sin argumentos, al metodo del controlador
                    call_user_func(array($this, $this->_metodo));
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);
            }
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);
    }

    private function convertirJson($data)
    {
        return json_encode($data);
    }

//            Metodos

//Get

//catlogo
    public function catalogo()
    {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $query = $this->_conn->query("SELECT * FROM pelicula");
        $filas = $query->fetchAll(PDO::FETCH_ASSOC);
        $num = count($filas);
        if ($num > 0) {

            $respuesta['estado'] = 'correcto';
            $respuesta['pelicula'] = $filas;
            $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
        }
        $this->mostrarRespuesta($this->devolverError(2), 204);
    }

// filtrar peliculas dependiendo membresia

//mostar membresias
    public function planes_suscripcion()
    {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $query = $this->_conn->query("SELECT * FROM suscripcion");
        $filas = $query->fetchAll(PDO::FETCH_ASSOC);
        $num = count($filas);
        if ($num > 0) {

            $respuesta['estado'] = 'correcto';
            $respuesta['plan'] = $filas;
            $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
        }
        $this->mostrarRespuesta($this->devolverError(2), 204);
    }

    public function usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $query = $this->_conn->query("SELECT * FROM usuarios");
        $filas = $query->fetchAll(PDO::FETCH_ASSOC);
        $num = count($filas);
        if ($num > 0) {

            $respuesta['estado'] = 'correcto';
            $respuesta['usuario'] = $filas;
            $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
        }
        $this->mostrarRespuesta($this->devolverError(2), 204);
    }

//Post

//perfin socio
// filtarar por usuario
    public function socio()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }

        // Verificar si se proporcionó el ID de usuario
        if (isset($this->datosPeticion['id'])) {
            $idUsuario = $this->datosPeticion['id'];

            $query = $this->_conn->prepare("SELECT id_usuario, u.nombre, u.apellido, u.email, s.id_suscripcion, s.titulo, s.descripcion, s.precio
            FROM socio AS so
            JOIN usuarios AS u ON u.id_usuarios = so.id_usuario
            JOIN suscripcion AS s ON s.id_suscripcion = so.id_suscripcion
            WHERE id_usuario = :id");
            $query->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);

            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['suscripcion'] = $filas;
                $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(2)), 204);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

// reproduccion

    public function reproduccion()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }

        // Verificar si se proporcionó el ID de usuario
        if (isset($this->datosPeticion['id'])) {
            $idUsuario = $this->datosPeticion['id'];

            $query = $this->_conn->prepare("SELECT id_reproduccion,p.portada, p.titulo, p.descripcion, r.fecha_reproduccion
            FROM reproduccion AS r
            JOIN usuarios AS u ON u.id_usuarios = r.id_usuario
            JOIN pelicula AS p ON p.id_pelicula = r.id_pelicula
            WHERE id_usuario = :id");
            $query->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);

            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['reproduccion'] = $filas;
                $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(2)), 204);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

//usuario

    public function existeUsuario($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $query = $this->_conn->prepare("SELECT email from usuarios WHERE email = :usuaemai");
            $query->bindValue(":usuaemai", $email);
            $query->execute();
            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        } else {
            return false;
        }
    }

// singup

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['nombre'], $this->datosPeticion['apellido'], $this->datosPeticion['email'], $this->datosPeticion['password'], $this->datosPeticion['telefono'], $this->datosPeticion['fecha_registro'])) {
            $nombre = $this->datosPeticion['nombre'];
            $apellido = $this->datosPeticion['apellido'];
            $email = $this->datosPeticion['email'];
            $password = $this->datosPeticion['password'];
            $telefono = $this->datosPeticion['telefono'];
            $fecha_registro = $this->datosPeticion['fecha_registro'];

            if (!$this->existeUsuario($email)) {
                $query = $this->_conn->prepare("INSERT INTO usuarios
                (nombre, apellido, email, password, telefono, fecha_registro) VALUES
                (:nombre, :apellido, :email, :password, :telefono, :fecha_registro)");

                $query->bindValue(":nombre", $nombre);
                $query->bindValue(":apellido", $apellido);
                $query->bindValue(":email", $email);
                $query->bindValue(":password", ($password));
                $query->bindValue(":telefono", $telefono);
                $query->bindValue(":fecha_registro", $fecha_registro);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();

                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'Usuario creado correctamente';
                    $respuesta['usuario']['id'] = $id;
                    $respuesta['usuario']['nombre'] = $nombre;
                    $respuesta['usuario']['apellido'] = $apellido;
                    $respuesta['usuario']['email'] = $email;
                    $respuesta['usuario']['telefono'] = $telefono;
                    $respuesta['usuario']['fecha_registro'] = $fecha_registro;

                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(8)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

    public function llenarform()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }

        // Verificar si se proporcionó el ID de usuario
        if (isset($this->datosPeticion['id'])) {

            $idUsuario = $this->datosPeticion['id'];

            $query = $this->_conn->prepare("SELECT * FROM `usuarios` WHERE id_usuarios = :id;");
            $query->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);

            if ($num > 0) {

                $respuesta['estado'] = 'correcto';
                $respuesta['user'] = $filas;
                $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(2)), 204);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

//Login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }

        if (isset($this->datosPeticion['email'], $this->datosPeticion['pwd'])) {
            $email = $this->datosPeticion['email'];
            $pwd = $this->datosPeticion['pwd'];

            if (!empty($email) && !empty($pwd) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $query = $this->_conn->prepare("SELECT id_usuarios, nombre, apellido, email, telefono, fecha_registro, verification_code
                FROM usuarios
                WHERE email = :usuaemai AND password = :usuapass ");
                $query->bindValue(":usuaemai", $email);
                $query->bindValue(":usuapass", $pwd);
                $query->execute();

                if ($fila = $query->fetch(PDO::FETCH_ASSOC)) {
                    if (empty($fila['verification_code'])) {
                        $verification = mt_rand(100000, 999999);
                        $query = $this->_conn->prepare("UPDATE usuarios SET verification_code = :codigo WHERE id_usuarios = :id");
                        $query->bindValue(":id", $fila['id_usuarios']);
                        $query->bindValue(":codigo", $verification);
                        $query->execute();

                        $fila['verification_code'] = $verification;
                    }

                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'datos pertenecen a usuario registrado';
                    $respuesta['Token'] = $fila['verification_code'];
                    $respuesta['id'] = $fila['id_usuarios'];
                    $respuesta['usuario']['nombre'] = $fila['nombre'];
                    $respuesta['usuario']['apellido'] = $fila['apellido'];
                    $respuesta['usuario']['email'] = $fila['email'];
                    $respuesta['usuario']['telefono'] = $fila['telefono'];
                    $respuesta['usuario']['fecha_registro'] = $fila['fecha_registro'];

                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                }
            }
        }

        $this->mostrarRespuesta($this->convertirJson($this->devolverError(3)), 400);
    }

//socio nuevo
    public function newsocio()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['id_usuario'], $this->datosPeticion['id_suscripcion'], $this->datosPeticion['fecha_ini'], $this->datosPeticion['fecha_fin'])) {

            $id_usuario = $this->datosPeticion['id_usuario'];
            $id_suscripcion = $this->datosPeticion['id_suscripcion'];
            $fecha_ini = $this->datosPeticion['fecha_ini'];
            $fecha_fin = $this->datosPeticion['fecha_fin'];

            if (!$this->existeUsuario($email)) {
                $query = $this->_conn->prepare("INSERT INTO socio
                (id_usuario, id_suscripcion, fecha_ini, fecha_fin) VALUES
                (:id_usuario, :id_suscripcion, :fecha_ini, :fecha_fin)");

                $query->bindValue(":id_usuario", $id_usuario);
                $query->bindValue(":id_suscripcion", $id_suscripcion);
                $query->bindValue(":fecha_ini", $fecha_ini);
                $query->bindValue(":fecha_fin", $fecha_fin);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();

                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'Usuario creado correctamente';
                    $respuesta['socio']['id'] = $id;
                    $respuesta['socio']['id_usuario'] = $id_usuario;
                    $respuesta['socio']['id_suscripcion'] = $id_suscripcion;
                    $respuesta['socio']['fecha_ini'] = $fecha_ini;
                    $respuesta['socio']['fecha_fin'] = $fecha_fin;

                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(8)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

//reproduccion

    public function newreproduccion()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['id_usuario'], $this->datosPeticion['id_pelicula'], $this->datosPeticion['fecha_reproduccion'])) {

            $id_usuario = $this->datosPeticion['id_usuario'];
            $id_pelicula = $this->datosPeticion['id_pelicula'];
            $fecha_reproduccion = $this->datosPeticion['fecha_reproduccion'];

            $query = $this->_conn->prepare("INSERT INTO reproduccion
            (id_usuario, id_pelicula, fecha_reproduccion) VALUES
            (:id_usuario, :id_pelicula, :fecha_reproduccion)");

            $query->bindValue(":id_usuario", $id_usuario);
            $query->bindValue(":id_pelicula", $id_pelicula);
            $query->bindValue(":fecha_reproduccion", $fecha_reproduccion);

            $query->execute();

            if ($query->rowCount() == 1) {
                $id = $this->_conn->lastInsertId();

                $respuesta['estado'] = 'correcto';
                $respuesta['msg'] = 'Reproducción creada correctamente';
                $respuesta['reproduccion']['id'] = $id;
                $respuesta['reproduccion']['id_usuario'] = $id_usuario;
                $respuesta['reproduccion']['id_pelicula'] = $id_pelicula;
                $respuesta['reproduccion']['fecha_reproduccion'] = $fecha_reproduccion;

                $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
        // Agrega el siguiente bloque de código al final del método
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
    }

    public function generarContrasenaAleatoria($longitud = 8)
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $contrasena = '';

        for ($i = 0; $i < $longitud; $i++) {
            $indice = mt_rand(0, strlen($caracteres) - 1);
            $contraseña .= $caracteres[$indice];
        }

        return $contrasena;
    }

//recuperar contraseña

    public function recuperarContrasena()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['email'])) {

            $email = $this->datosPeticion['email'];

            if ($this->existeUsuario($email)) {
                // Generar una nueva contraseña aleatoria
                $longitud = 8;
                $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $contrasena = '';

                for ($i = 0; $i < $longitud; $i++) {
                    $indice = mt_rand(0, strlen($caracteres) - 1);
                    $contrasena .= $caracteres[$indice];
                }

                //echo $contrasena;

                // Actualizar la contraseña del usuario en la base de datos
                $query = $this->_conn->prepare("UPDATE usuarios SET password = :nueva_contrasena WHERE email = :email");
                $query->bindValue(":nueva_contrasena", $contrasena);
                $query->bindValue(":email", $email);
                $query->execute();

                if ($query->rowCount() == 1) {
                    // Envía la nueva contraseña al correo electrónico del usuario
/*
$asunto = 'Recuperación de contraseña';
$mensaje = 'Tu nueva contraseña es: ' . $contrasena;

// Para enviar correos HTML, se debe establecer la cabecera Content-type
$cabeceras = 'MIME-Version: 1.0' . "\r\n";
$cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";

// Opcional: Establecer el remitente del correo
$cabeceras .= 'From: jcvm2001valencia@gmail.com' . "\r\n";

// Enviar el correo electrónico
mail($email, $asunto, $mensaje, $cabeceras);

 */
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'Se ha enviado una nueva contraseña al correo electrónico proporcionado.';
                    $respuesta['contraseña'] = $contrasena;
                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(9)), 500);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(10)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

    public function enviarCorreoRecuperacionContraseña($email, $contrasena)
    {
        // Código para enviar el correo electrónico con la nueva contraseña al usuario
        // Puedes utilizar una biblioteca o servicio de envío de correo electrónico como PHPMailer o SendGrid
        // Aquí hay un ejemplo básico usando la función mail de PHP:

        $asunto = 'Recuperación de contraseña';
        $mensaje = 'Tu nueva contraseña es: ' . $contrasena;

        // Para enviar correos HTML, se debe establecer la cabecera Content-type
        $cabeceras = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        // Opcional: Establecer el remitente del correo
        $cabeceras .= 'From: jcvm2001valencia@gmail.com' . "\r\n";

        // Enviar el correo electrónico
        mail($email, $asunto, $mensaje, $cabeceras);
    }

//put

//editar perfil tabla user

    public function actualizarUsuario($idUsuario)
    {
        if ($_SERVER['REQUEST_METHOD'] != "PUT") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['nombre'], $this->datosPeticion['apellido'], $this->datosPeticion['email'], $this ->datosPeticon['password'], $this->datosPeticion['telefono'])) {

            $nombre = $this->datosPeticion['nombre'];
            $apellido = $this->datosPeticion['apellido'];
            $email = $this->datosPeticion['email'];
            $password = $this->datosPeticion['password'];
            $telefono = $this->datosPeticion['telefono'];
            $id = (int) $idUsuario;

            if (!empty($nombre) && $id > 0) {
                $query = $this->_conn->prepare("UPDATE `usuarios` SET nombre=:nombre, apellido=:apellido, email=:email, telefono=:telefono WHERE id_usuarios=:usuaid;");
                $query->bindParam(":nombre", $nombre);
                $query->bindParam(":apellido", $apellido);
                $query->bindParam(":email", $email);
                $query->bindParam(":password", $password);
                $query->bindParam(":telefono", $telefono);
                $query->bindParam(":usuaid", $id);
                $query->execute();

                $filasActualizadas = $query->rowCount();
                if ($filasActualizadas == 1) {
                    $resp = array('estado' => "correcto", "msg" => "Datos de usuario actualizados correctamente.");
                    $this->mostrarRespuesta($this->convertirJson($resp), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);
        }
    }

// editar plan tabla socio
    public function updatesocio()
    {
        if ($_SERVER['REQUEST_METHOD'] != "PUT") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['id_usuario'], $this->datosPeticion['id_suscripcion'], $this->datosPeticion['fecha_ini'], $this->datosPeticion['fecha_fin'])) {

            $id_usuario = $this->datosPeticion['id_usuario'];
            $id_suscripcion = $this->datosPeticion['id_suscripcion'];
            $fecha_ini = $this->datosPeticion['fecha_ini'];
            $fecha_fin = $this->datosPeticion['fecha_fin'];

            if ($this->existeUsuario($email)) {
                $query = $this->_conn->prepare("UPDATE socio SET
                id_suscripcion = :id_suscripcion,
                fecha_ini = :fecha_ini,
                fecha_fin = :fecha_fin
                WHERE id_usuario = :id_usuario");

                $query->bindValue(":id_suscripcion", $id_suscripcion);
                $query->bindValue(":fecha_ini", $fecha_ini);
                $query->bindValue(":fecha_fin", $fecha_fin);
                $query->bindValue(":id_usuario", $id_usuario);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'Usuario actualizado correctamente';
                    $respuesta['socio']['id_usuario'] = $id_usuario;
                    $respuesta['socio']['id_suscripcion'] = $id_suscripcion;
                    $respuesta['socio']['fecha_ini'] = $fecha_ini;
                    $respuesta['socio']['fecha_fin'] = $fecha_fin;

                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(9)), 400);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(8)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

//delete

//eliminar cuenta
    public function borrarUsuario($idUsuario)
    {
        if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $id = (int) $idUsuario;
        if ($id >= 0) {

            $this->_conn->beginTransaction();

            try {
                $queryReproduccion = $this->_conn->prepare("DELETE FROM reproduccion WHERE id_usuario = :usuaid");
                $queryReproduccion->bindValue(":usuaid", $id);
                $queryReproduccion->execute();

                $querySocio = $this->_conn->prepare("DELETE FROM socio WHERE id_usuario = :usuaid");
                $querySocio->bindValue(":usuaid", $id);
                $querySocio->execute();

                $queryUsuario = $this->_conn->prepare("DELETE FROM usuarios WHERE id_usuarios = :usuaid");
                $queryUsuario->bindValue(":usuaid", $id);
                $queryUsuario->execute();

                $filasBorradasUsuario = $queryUsuario->rowCount();
                $filasBorradasSocio = $querySocio->rowCount();

                if ($filasBorradasUsuario == 1 && $filasBorradasSocio >= 0 && $filasBorradasReproduccion >= 0) {
                    $this->_conn->commit();

                    $resp = array('estado' => "correcto", "msg" => "Usuario y sus registros relacionados borrados correctamente.");
                    $this->mostrarRespuesta($this->convertirJson($resp), 200);
                } else {
                    $this->_conn->rollBack();
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
                }
            } catch (PDOException $e) {
                $this->_conn->rollBack();
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
            }

           /*$query = $this->_conn->prepare("DELETE FROM socio WHERE id_usuario =:usuaid");
            $query->bindValue(":usuaid", $id);
            $query->execute();
            //rowcount para insert, delete. update
            $filasBorradas = $query->rowCount();
            if ($filasBorradas == 1) {
                $resp = array('estado' => "correcto", "msg" => "usuario borrado correctamente.");
                $this->mostrarRespuesta($this->convertirJson($resp), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
            }*/
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
    }

// elinimar filtrado id reproduccion

    public function borrarreproduccionexpecifica($id_r)
    {
        if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $id = (int) $id_r;
        if ($id >= 0) {
            $query = $this->_conn->prepare("DELETE FROM reproduccion WHERE id_reproduccion =:id_r");
            $query->bindValue(":id_r", $id);
            $query->execute();
            //rowcount para insert, delete. update
            $filasBorradas = $query->rowCount();
            if ($filasBorradas == 1) {
                $resp = array('estado' => "correcto", "msg" => "o borrado correctamente.");
                $this->mostrarRespuesta($this->convertirJson($resp), 200);
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
            }
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
    }

}
