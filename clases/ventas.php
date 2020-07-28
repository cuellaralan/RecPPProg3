<?php
class venta 
{
    public $id;
    public $idProd;
    public $cantidad;
    public $usuario;
    public $montoVenta;

    public function __construct($claveProd, $cant, $user)
    {
        $this->id = time();
        $this->idProd = $claveProd;
        $this->cantidad = $cant;
        $this->usuario = $user;
        $this->montoVenta = 0;
    }

    public function guardarVenta($archivo, $producto)
    {
        // echo "estoy en usuario";
        $listaVentas = funciones::Leer($archivo);
        $this->montoVenta = $this->cantidad * $producto->precio;
        array_push($listaVentas, $this);      
        // escribo archivo
        $retorno = funciones::Guardar($listaVentas,$archivo,'w');
        $retorno2 = $this->restarStock($producto);
        $response = new Lresponse();
        $response->data = $this;
        $response->status = $retorno;
        return $response;

        // return funciones::Guardar($this,$archivo,'a+');
    }

    private function restarStock($producto)
    {
        // echo "estoy en restar Stock";
        $archivo = './files/producto.json';
        // $nuevaLista = array();
        $listaProductos = funciones::Leer($archivo);
        foreach ($listaProductos as $key => $value) {
            // echo("producto lista: $value->id" + " producto informado:  + $producto->id" );
            if ($producto->id == $value->id) {
                // echo("stock es: " + $value->stock);
                $value->stock = $value->stock - $this->cantidad;
                // $listaProductos[$key] = $producto;
                // echo("stock nuevo es: " + $value->stock);
                break;
            }
            // array_push($nuevaLista, $value);
        }
        $retorno = funciones::Guardar($listaProductos,$archivo,'w');
        $response = new Lresponse();
        $response->data = $this;
        $response->status = $retorno;
        return $response;
    }

    public static function traerVentas($tipo, $nombre)
    {
        // echo $nombre;
        // echo $tipo;
        $archivo = './files/ventas.json';
        // $nuevaLista = array();
        $listaProductos = funciones::Leer($archivo);
        $respuesta = '';
        if ($tipo == 'admin') {
            $respuesta = $listaProductos;
        }
        else {
            $respuesta = array();
            foreach ($listaProductos as $key => $value) {
                if ($nombre == $value->usuario) {
                    array_push($respuesta, $value);
                }
            }
        }
        if (empty($respuesta)) {
            $respuesta = 'no existen ventas para el usuario';
        }
        return $respuesta;
    }

}


?>