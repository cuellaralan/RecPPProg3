<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\StreamInterface;
use Slim\Factory\AppFactory;

use \Firebase\JWT\JWT;
require_once __DIR__ .'./vendor/autoload.php';
// require './clases/paises.php';
require_once './clases/funciones.php';
require_once './clases/usuarios.php';
require_once './clases/mensajes.php';
require_once './clases/response.php';
require_once './clases/ventas.php';

$metodo = $_SERVER["REQUEST_METHOD"];

$app = AppFactory::create();
$app->setBasePath("/mensajes");
$app->addErrorMiddleware(false, true, true);
$key = "example_key";

// $response = new Lresponse();
// $response->status = 'success';

//obtengo headers
$headers = getallheaders();
//verifico token
$token = $headers['token'] ?? '';

//logup
$app->post('/users', function (Request $request, Response $response, array $args) {
    //variables
    $archivo = './files/users.json';
    $key = "example_key";
    $responde = new Lresponse();
    //parametros
    $email = $request->getParsedBody()['email'] ?? '';
    $clave = $request->getParsedBody()['clave'] ?? '';
    $tipo = $request->getParsedBody()['tipo'] ?? '';
    //files
    $fotos = $_FILES['foto'] ?? '';
    
    if($email != '' && $clave != '' && $tipo != '' && $fotos != '' && ($tipo == 'admin' || 'user'))
    {
        // validar existencia de usuario
        $cliente = new usuario($email, $clave, $tipo, '',  '');
        $exist = $cliente->verifica();
        //parametros para guardar foto
        if(!$exist)
        {
            $destino = './images/users/';
            $fotoName = $fotos['name'][0];
            $path = $fotos['tmp_name'][0];
            //
            $destiny1 = funciones::GuardaTemp($path, $destino, $fotoName, $email); 
            //
            $fotoName = $fotos['name'][1];
            $path = $fotos['tmp_name'][1];
            //
            $destiny2 = funciones::GuardaTemp($path, $destino, $fotoName, $email); 
            
            if($destino != $destiny1 && $destiny2)
            {
                $cliente->foto1 = $destiny1;
                $cliente->foto2 = $destiny2;
                $respuesta = $cliente->guardarUsuario($archivo);
                $responde->status = 'success';
                $responde = $respuesta;
            }
            else
            {
                $responde->data = 'error al subir imagen de producto';
            }
        }
        else{
            $responde->data = 'El usuario ya existe en el sistema';
        }
            
    }
    else
    {
        $responde->data = "Error - Datos vacíos o incorrectos";
    }
    $response->getBody()->write(json_encode($responde));
    return $response
    ->withHeader('Content-Type' , 'aplication/json')
    ->withStatus(200);
});  
  
    
$app->post('/login', function (Request $request, Response $response, array $args) {
    $archivo = './files/users.json';
    $key = "example_key";
    $responde = new Lresponse();
    $email = $request->getParsedBody()['email'] ?? '';
    $clave = $request->getParsedBody()['clave'] ?? '';
    
    if($email != '' && $clave != '')
    {
        $responde = usuario::verificarLogin($archivo,$email,$clave);
        $datos= $responde->data;
        // print_r(json_encode($response));
        if($responde->status == 'unsucces')
        {
            $responde->data = "Datos erroneos, verifique.";
        }
        else 
        {
            $payload = array(
                "iss" => "http://example.org",
                    "aud" => "http://example.com",
                    "iat" => 1356999524,
                    "nbf" => 1357000000,
                    "name" => $datos->email,
                    "id" => $datos->id,
                    "tipo" => $datos->tipo
                );
                $jwt = JWT::encode($payload, $key);
                $responde->status = 'success';
                $responde->data = $jwt;           
                // echo json_encode($response);
        }
    }
    else
    {
        $responde->data = "Error - Datos vacíos ";
    }
    $response->getBody()->write(json_encode($responde));
    return $response
        ->withHeader('Content-Type' , 'aplication/json')
        ->withStatus(200);
});

$app->group('/mensajes', function($group){
    $group->post('[/]', function (Request $request, Response $response, array $args) {
        $key = "example_key";
        //parametros para guardar foto
        $archivo = './files/mensajes.json';
        $responde = new Lresponse();
        //se modifico parametros, ya que no funciona getParsedBody por PUT
        // se envían por QRYPARMS
        $mensaje = $request->getParsedBody()['mensaje'] ?? '';
        $destino = $request->getParsedBody()['id_usuario'] ?? ''; 
        // obtengo token
        $headers = getallheaders();
        //verifico token
        $token = $headers['token'] ?? '';
        if ($token == '') {
            // $response = new Lresponse();
            $responde->status = 'unsucces';
            $responde->data = 'error , token incorrecto';
            $verifica = false;
        }
        else{
            try {
                //code...
                $decoded = JWT::decode($token, $key, array('HS256'));
                $verifica = true;
                // print_r($decoded);
            } catch (\Throwable $th) {
                //throw $th;
                $verifica = false;
                $responde->data = $th;
            }
        }
        if($verifica){
            if($mensaje != '' && $destino != '')
            {
                $nuevoMensaje = new mensaje($mensaje, $destino, $decoded->id);
                $responde = $nuevoMensaje->guardarMensaje($archivo);
                if($responde->status == 'unsucces'){
                    $responde->data = 'Error al guardar mensaje';
                }
            }
            else{
                $responde->data = 'Datos con errores'; 
                // echo('mensaje: ' . $mensaje);
                // echo('destino: '. $destino);
                // echo(json_encode($args));
            }
        }
        else
        {
            $responde->data = 'Token incorrecto'; 
        }
        $response->getBody()->write(json_encode($responde));
            return $response
                ->withHeader('Content-Type' , 'aplication/json')
                ->withStatus(200);
    });

    $group->get('[/]', function (Request $request, Response $response, array $args) {
        $key = "example_key";
        //parametros para guardar foto
        $archivo = './files/mensajes.json';
        $responde = new Lresponse();
        // $mensaje = $request->getParsedBody()['mensaje'] ?? '';
        // $destino = $request->getParsedBody()['id_usuario'] ?? '';
        //obtengo token
        $headers = getallheaders();
        //verifico token
        $token = $headers['token'] ?? '';
        if ($token == '') {
            // $response = new Lresponse();
            $responde->status = 'unsucces';
            $responde->data = 'error , token incorrecto';
            $verifica = false;
        }
        else{
            try {
                //code...
                $decoded = JWT::decode($token, $key, array('HS256'));
                $verifica = true;
                // print_r($decoded);
            } catch (\Throwable $th) {
                //throw $th;
                $verifica = false;
                $responde->data = $th;
            }
        }
        if($verifica == true)
        {
            $tipo = $decoded->tipo;
            $emisor = $decoded->id;
            $respuesta = mensaje::buscarMensajes($archivo, $tipo, $emisor);
            if(! empty($respuesta)){
                $responde->data = $respuesta;
                $responde->status = 'succes';
            }
            else{
                $responde->data = 'No hay mensajes para mostrar';
            }
        }
        else{
            $responde->data = 'Datos con errores - '; 
        }
        $response->getBody()->write(json_encode($responde));
            return $response
                ->withHeader('Content-Type' , 'aplication/json')
                ->withStatus(200);
    });

    $group->get('/{id}', function (Request $request, Response $response, array $args) {
        $key = "example_key";
        //parametros para guardar foto
        $archivo = './files/mensajes.json';
        $responde = new Lresponse();
        //obtengo token
        $headers = getallheaders();
        //verifico token
        $token = $headers['token'] ?? '';
        if ($token == '') {
            // $response = new Lresponse();
            $responde->status = 'unsucces';
            $responde->data = 'error , token incorrecto';
            $verifica = false;
        }
        else{
            try {
                //code...
                $decoded = JWT::decode($token, $key, array('HS256'));
                $verifica = true;
                // print_r($decoded);
            } catch (\Throwable $th) {
                //throw $th;
                $verifica = false;
                $responde->data = $th;
            }
        }
        if($verifica == true)
        {
            $tipo = $decoded->tipo;
            $id = $decoded->id;
            $respuesta = mensaje::buscarMEnsajesId($archivo, $tipo, $id);
            $responde->data = $respuesta;
            $responde->status = 'succes';
        }
        else{
            $responde->data = 'Datos con errores'; 
        }
        // $responde = new Lresponse();
        // $responde->data = 'Hola get por id';
        // $responde->status = 'succes';
        $response->getBody()->write(json_encode($responde));
            return $response
                ->withHeader('Content-Type' , 'aplication/json')
                ->withStatus(200);
    });

    $group->delete('/{id}', function (Request $request, Response $response, array $args) {
        $key = "example_key";
        $idMsg = $request->getQueryParams()['id_mensaje'] ?? '';
        // parametros para guardar foto
        $archivo = './files/mensajes.json';
        $responde = new Lresponse();
        // obtengo token
        $headers = getallheaders();
        // verifico token
        $token = $headers['token'] ?? '';
        if ($token == '') {
            $response = new Lresponse();
            $responde->status = 'unsucces';
            $responde->data = 'error , token incorrecto';
            $verifica = false;
        }
        else{
            try {
                $decoded = JWT::decode($token, $key, array('HS256'));
                $verifica = true;
                // print_r($decoded);
            } catch (\Throwable $th) {
                throw $th;
                $verifica = false;
                $responde->data = $th;
            }
        }
        if($verifica == true)
        {
            $tipo = $decoded->tipo;
            $id = $decoded->id;
            $respuesta = mensaje::borraMensaje($id, $archivo, $idMsg, $tipo);
            $responde->data = $respuesta;
            if($respuesta == 'mensaje eliminado'){
                $responde->satus = 'succes';
            }
        }

        $response->getBody()->write(json_encode($responde));
        return $response
            ->withHeader('Content-Type' , 'aplication/json')
            ->withStatus(200);
    });
 
});

$app->run();
?>

