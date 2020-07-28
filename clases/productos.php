<?php

class producto
{
    public $id;
    public $tipo;
    public $precio;
    public $stock;
    public $sabor;
    public $foto;

    function __construct($tipo, $precio, $stock, $sabor, $photo)
    {
        $this->id = time();
        $this->tipo = $tipo;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->sabor = $sabor;
        $this->foto = $photo;
    }

    public function guardarProducto($archivo)
    {
        // echo "estoy en usuario";
        $listaProductos = funciones::Leer($archivo);
        array_push($listaProductos, $this);      
        // escribo archivo
        $retorno = funciones::Guardar($listaProductos,$archivo,'w');
        $response = new Lresponse();
        $response->data = $this;
        $response->status = $retorno;
        return $response;

        // return funciones::Guardar($this,$archivo,'a+');
    }
}

?>