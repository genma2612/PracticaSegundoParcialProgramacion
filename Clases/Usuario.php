<?php

use Firebase\JWT\JWT; //Incluyo Firebase

class Usuario{

    public $correo;
    public $clave;
    public $nombre;
    public $apellido;
    public $perfil;
    public $foto;

    //Metodos API

    public static function AltaDeUsuarioDBAPI($request, $response, $args) {
        $obj = json_decode($request->getParsedBody()["usuario"]);
        Usuario::AltaDeUsuarioDB($obj);
        return $response->getBody()->write("Usuario agregado a BD");;
    }

    public static function TraerTodosLosUsuariosAPI($request, $response, $args) {
        $Usuarios=Usuario::TraerTodosLosUsuarios();
        $newResponse = $response->withJson($Usuarios, 200);  
        return $newResponse;
    }

    public static function GenerarJWTAPI($request, $response, $args)
    {
        $obj = new stdClass();
        $obj->correo = $request->getParsedBody()["correo"];
        $obj->clave = $request->getParsedBody()["clave"];
        try 
        {
            $token = Usuario::GenerarJWT($obj);
            if ($token == null) {
                throw new Exception("El usuario no se encuentra registrado");
            }
            $newResponse =  $response->withJson($token, 200);  
        } 
        catch (Exception $e) {
            $newResponse = $response->getBody()->write("Token no valido!!! --> " . $e->getMessage());
        }
        return $newResponse;
    }

    public static function VerificarJWTAPI($request, $response, $args)
    {
        $token = $_GET["token"];
        $obj = Usuario::VerificarJWT($token);
        return $response->withJson($obj->mensaje, $obj->status);  
    }


    //Metodos 
    public static function AltaDeUsuarioDB($obj)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta('INSERT INTO usuarios (correo, clave, nombre, apellido, perfil, foto) 
                                                        VALUES(:correo, :clave, :nombre, :apellido, :perfil, :foto)');
        $consulta->bindValue(':correo', $obj->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $obj->clave, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $obj->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $obj->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $obj->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $obj->foto, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function TraerTodosLosUsuarios()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from usuarios");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");
    }

    public static function TraerUnUsuario($obj)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from usuarios WHERE correo = :correo AND clave = :clave"); 
        $consulta->bindValue(':correo', $obj->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $obj->clave, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchObject('Usuario');
    }

    public static function GenerarJWT($obj)
    {
        $retorno = null;
        $resultado = Usuario::TraerUnUsuario($obj);
        if($resultado != null)
        {
            $ahora = time();
            $payload = array(
                  'iat'=>$ahora,
                  //'exp' => $ahora + 20,
                  'data' => $resultado
              );
            $retorno = JWT::encode($payload, "claveSecreta");
        }
        return $retorno;
    }

    public static function VerificarJWT($token)
    {
        $retorno = new stdClass();
        $retorno->mensaje = "Token validado correctamente";
        $retorno->payload = null;
        $retorno->status = 200;
        try{
            if(empty($token) || $token === "")
            {
                throw new Exception("Token vacio");
            }
            //Decodifico agregando el token, la clave y el tipo de codificado
            $decodificado = JWT::decode($token, 'claveSecreta', ['HS256']);
            $retorno->payload = $decodificado->data; //payload
            $retorno->mensaje .= " , usuario {$retorno->payload->correo}";
        }
        catch(Exception $e){
            $retorno->status = 409;
            $retorno->mensaje = "Token no valido!!! --> " . $e->getMessage();
        }
        //var_dump($decodificado);
        return $retorno;
    }    
}