<?php
class Media{

    public $color;
    public $marca;
    public $precio;
    public $talle;

    //Metodos API

    public static function AltaDeMediaDBAPI($request, $response, $args) {
        $obj = json_decode($request->getParsedBody()["media"]);
        Media::AltaDeMediaDB($obj);
        return $response->getBody()->write("Media agregada a BD");
    }

    public static function TraerTodasLasMediasAPI($request, $response, $args) {
        $medias=Media::TraerTodasLasMedias();
        $newResponse = $response->withJson($medias, 200);  
        return $newResponse;
    }

    public static function BorrarMediaPorIDAPI($request, $response, $args) {
        $id = $request->getParsedBody()['id'];
        $cantidadDeBorrados = Media::BorrarMediaPorID($id);
        $objDelaRespuesta = new stdclass();
        if($cantidadDeBorrados > 0)
        {
            $objDelaRespuesta->resultado="Se borró la media";
        }
        else
        {
            $objDelaRespuesta->resultado="No se encontró el ID";
        }
        $newResponse = $response->withJson($objDelaRespuesta, 200);  
        return $newResponse;
    }

    public static function ModificarMediaPorIDAPI($request, $response, $args) {
        $obj = json_decode($request->getParsedBody()["media"]);
        Media::ModificarMediaPorID($obj); 
        $objDelaRespuesta = new stdclass();
        $objDelaRespuesta->resultado="Media modificada";
        $newResponse = $response->withJson($objDelaRespuesta, 200); 
        return $newResponse;
    }

    //Metodos 
    public static function AltaDeMediaDB($obj)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta('INSERT INTO medias (color, marca, precio, talle) 
                                                        VALUES(:color, :marca, :precio, :talle)');
        $consulta->bindValue(':color',  $obj->color, PDO::PARAM_STR);
        $consulta->bindValue(':marca',  $obj->marca, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $obj->precio, PDO::PARAM_INT);
        $consulta->bindValue(':talle',  $obj->talle, PDO::PARAM_STR);
        return $consulta->execute();
    }

    public static function TraerTodasLasMedias($cadena = "SELECT * from medias")
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta($cadena);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Media");
    }


    public static function BorrarMediaPorID($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta('DELETE from medias WHERE id =:id');	
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);		
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function ModificarMediaPorID($obj) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta('UPDATE medias SET color = :color, marca = :marca, precio = :precio, talle = :talle WHERE id = :id');
        $consulta->bindValue(':id', $obj->id, PDO::PARAM_INT);
        $consulta->bindValue(':color', $obj->color, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $obj->marca, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $obj->precio, PDO::PARAM_INT);
        $consulta->bindValue(':talle', $obj->talle, PDO::PARAM_STR);
        return $consulta->execute();
    }

    
}