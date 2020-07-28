<?php

class mensaje
{
    public $id;
    public $destinoId;
    public $mensaje;
    public $emisorId;
    public $fecha;

    public function __construct($mensaje,$destino, $emisor)
    {
        $this->id = time();
        $this->mensaje = $mensaje;
        $this->destinoId = $destino;
        $this->emisorId = $emisor;
        setlocale(LC_TIME,"es_RA");
        // $fecha = date("Y-m-d");
        $fecha = date(DATE_ATOM);
        // $hora = date("H-i-s");
        $this->fecha = $fecha; 
    }

    public function guardarMensaje($archivo)
    {
        $responde = new Lresponse();
        $listaMensajes = funciones::Leer($archivo);
        // echo "formato: <br>";
        // print_r($listaPersonas);
        // Insertamos persona
        array_push($listaMensajes, $this);
        // print_r($listaPersonas);
        //existencia de receptor
        $valida = $this->validaReceptor();       
        // escribo archivo
        if($valida){
            $retorno = funciones::Guardar($listaMensajes,$archivo,'w');
                $responde->data = 'Guardado exitoso';
                $responde->status = $retorno;
        }
        else{
            $responde->data = 'id de destino inexistente';
        }
        return $responde;
    }

    private function validaReceptor(){
        $rta = false;
        $archivo = './files/users.json';
        $listaUsuarios = funciones::Leer($archivo);
        if (! empty($listaUsuarios)) {
            foreach ($listaUsuarios as $key => $value) {
                if($value->id == $this->destinoId){
                    $rta = true;
                }
            }
        }
        return $rta;
    }

    public static function buscarMensajes($archivo, $tipo, $emisor)
    {
        // echo "estoy en usuario";
        $mensajes = funciones::Leer($archivo);
        /*array_search ( mixed $needle , array $haystack [, bool $strict = false ] ) : mixed
        Busca en el haystack (pajar) por la needle (aguja).*/
        $fechaultima = 0;
        $seleccion = array();
        if(! empty($mensajes)){
            /*
            Si es user, se muestran los datos de todas las personas a las que se les envió un
            mensaje (email, fecha y texto del último mensaje). 
            Si es admin se muestran los pares de usuarios que tuvieron
            mensajes (ejemplo: Maria - jose - fecha). 
            */
            foreach ($mensajes as $key => $value) {
                // var_dump($value); echo "$key";
                $userE = usuario::dameDatos($value->emisorId); 
                $userR = usuario::dameDatos($value->destinoId);
                if($tipo == 'admin')
                {
                    if($userE == 'datos no encontrados'){
                        array_push($seleccion, 'no hay mensajes para mostrar');
                    }
                    else{
                        array_push($seleccion, $userE->email . '=>' . $userR->email . '=>' . $value->fecha);
                        // var_dump($userE);
                        // var_dump($userR);
                    }
                }
                else {
                    if($emisor == $value->emisorId)
                    {
                        // array_push($seleccion, $value);
                        array_push($seleccion, $userR->email . '=>' . $value->fecha . '=>' . $value->mensaje);
                    }
                }
            }
        }
        return $seleccion;
    }
    public static function buscarMEnsajesId($archivo, $tipo, $id)
    {
        // echo "estoy en usuario";
        $mensajes = funciones::Leer($archivo);
        /*array_search ( mixed $needle , array $haystack [, bool $strict = false ] ) : mixed
        Busca en el haystack (pajar) por la needle (aguja).*/
        /*
        Si es user, se muestran todos los mensajes enviados y recibidos con ese usuario
        ordenado por fecha. 
        Si es admin se muestran todas las personas a las que el id envio mensajes.*/
        $responde = new Lresponse();
        // $fechaultima = 0;
        $seleccion = array();
        $entro = false;
        foreach ($mensajes as $key => $value) {
            // var_dump($value); echo "$key";
            $userE = usuario::dameDatos($value->emisorId); 
            $userR = usuario::dameDatos($value->destinoId);
            if($tipo == 'admin')
            {
                if($id == $value->emisorId)
                {
                    array_push($seleccion, $userR);
                }
                // $entro = $value;
            }
            else {
                if($id == $value->emisorId || $id == $value->destinoId)
                {
                    array_push($seleccion, $value);
                }
            }
        }
        return $seleccion;
    }

    public static function ultimoMensaje($id, $archivo){
        // $archivo = './files/mensajes.json';
        $mensajes = funciones::Leer($archivo);
        $respuesta = 'no existen mensajes para mostrar';
        if(! empty($mensajes)){
            $msgUser = funciones::traerPorId($mensajes,$id);
            if(! empty($msgUser)){
                $respuesta = funciones::traerUltimo($msgUser);
                // $respuesta = 'ultimo mensaje';
            }
        }
        return $respuesta;
    }

    public static function cuentaMensajes($archivo){
        $mensajes = funciones::Leer($archivo);
        $respuesta = 'no existen mensajes para mostrar';
        if(! empty($mensajes)){
            $respuesta = funciones::generaYcuenta($mensajes);
        }
        return $respuesta;
    }

    public static function borraMensaje($id, $archivo, $idMsg, $tipo){
        $mensajes = funciones::Leer($archivo);
        $newMensajes = array();
        $respuesta = 'mensaje no encontrado';
        if($tipo == 'user'){
            foreach ($mensajes as $key => $value) {
                if($value->id == $idMsg){
                    if($id == $value->emisorId){
                        $respuesta = 'mensaje eliminado';
                    }
                    else{
                        $respuesta = 'mensaje no enviado por el usuario';
                        array_push($newMensajes, $value);
                    }
                }else{
                    array_push($newMensajes, $value);
                }
            }
            $retorno = funciones::Guardar($newMensajes,$archivo,'w');
        }
        else{
            foreach ($mensajes as $key => $value) {
                if($value->id == $idMsg){
                        $respuesta = 'mensaje eliminado';
                }else{
                    array_push($newMensajes, $value);
                }
            }
            $retorno = funciones::Guardar($newMensajes,$archivo,'w');
        }
        return $respuesta;
    }



}

?>