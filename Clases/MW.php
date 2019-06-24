<?php

class MW{

    public function VerificarSeteo($request, $response, $next)
    {
        $arrayDeDatos = $request->getParsedBody();
        $correo = isset($arrayDeDatos['correo']);
        $clave = isset($arrayDeDatos['clave']);
        if($correo == null || $clave == null)
        {
            $retorno = new StdClass();
            $retorno->mensaje = "Falta setear campos en el formulario";
            $newResponse = $response->withJson($retorno, 409);
        }
        else
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public static function VerificarVacios($request, $response, $next)
    {
        $arrayDeDatos = $request->getParsedBody();
        if($arrayDeDatos["correo"] == "" || $arrayDeDatos["clave"] == "")
        {
            $retorno = new StdClass();
            $retorno->mensaje = "Campos vacios en el formulario";
            $newResponse = $response->withJson($retorno, 409);
        }
        else
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public function VerificarExistencia($request, $response, $next)
    {
        $Existe = true;
        $obj = new stdClass();
        $obj->correo = $request->getParsedBody()["correo"];
        $obj->clave = $request->getParsedBody()["clave"];
        $usuario = Usuario::TraerUnUsuario($obj);
        if($usuario == null)
        {
            $Existe = false;
            $retorno = new StdClass();
            $retorno->mensaje = "ERROR: El usuario no existe";
            $newResponse = $response->withJson($retorno, 409);
        }
        if($Existe)
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public function VerificarToken($request, $response, $next)
    {
        $token = null;
        if($request->IsGet())
        {
            $token = $_GET["token"];
        }
        else
        {
            $token = $request->getParsedBody()["token"];
        }
        $obj = Usuario::VerificarJWT($token);
        if($obj->status == 409)
        {
            $retorno = new StdClass();
            $retorno->mensaje = "ERROR: Token invalido";
            $newResponse = $response->withJson($retorno, 409);
        }
        else
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public static function VerificarPropietario($request, $response, $next)
    {
        $token;
        if($request->IsGet())
        {
            $token = $_GET["token"];
        }
        else
        {
            $token = $request->getParsedBody()["token"];
        }
        $obj = Usuario::VerificarJWT($token);
        $user = $obj->payload;
        if($user->perfil != "propietario")
        {
            $retorno = new StdClass();
            $retorno->mensaje = "ERROR: El usuario no tiene los permisos necesarios";
            $newResponse = $response->withJson($retorno, 409);
        }
        else
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public function VerificarEncargado($request, $response, $next)
    {
        $token;
        if($request->IsGet())
        {
            $token = $_GET["token"];
        }
        else
        {
            $token = $request->getParsedBody()["token"];
        }
        $obj = Usuario::VerificarJWT($token);
        $user = $obj->payload;
        if($user->perfil != "encargado" && $user->perfil != "propietario")
        {
            $retorno = new StdClass();
            $retorno->mensaje = "ERROR: El usuario no tiene los permisos necesarios";
            $newResponse = $response->withJson($retorno, 409);
        }
        else
        {
            $newResponse = $next($request, $response);
        }
        return $newResponse;
    }

    public function MostrarDatosLimitados($request, $response, $next)
    {
        $medias = Media::TraerTodasLasMedias("SELECT color,marca,precio,talle from medias");
        $newResponse = $response->withJson($medias, 200);  
        return $newResponse;
    }

    public function MostrarColoresDistintos($request, $response, $next)
    {
        $coloresTemp = array();
        $objRetorno = new stdClass();
        $medias = Media::TraerTodasLasMedias();
        foreach ($medias as $media) {
            array_push($coloresTemp, $media->color);
        }
        $objRetorno->colores = array_unique($coloresTemp);
        $objRetorno->cantidad = sizeof($objRetorno->colores);
        $newResponse = $response->withJson($objRetorno, 200);  
        return $newResponse;
    }

    public static function MostrarPorID($request, $response, $args)
    {
        $id = null;
        if(isset($args["id"]))
        {
            $id = $args["id"];
        }
        $noSeEncontro = true;
        $medias = Media::TraerTodasLasMedias();  
        if($id != null)
        {
            foreach ($medias as $media) {
                if($media->id == $id)
                {
                    $newResponse = $response->withJson($media, 200);
                    $noSeEncontro = false;
                    break;
                }
            }
            if($noSeEncontro)
            {
                $obj = new stdClass();
                $obj->mensaje = "No se encontro el ID";
                $newResponse = $response->withJson($obj, 409); 
            }
        }
        else
        {
            $newResponse = $response->withJson($medias, 200);
        }
        return $newResponse;
    }



}