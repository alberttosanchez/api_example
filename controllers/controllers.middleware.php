<?php // Controllers MiddleWare

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /** Validamos el token bearer */

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
            
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        $error_code = '403';

        $msg = 'Error. Faltan parametros.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();        
    }

    /** Obtenemos el nombre de usuario y el token para validar la solicitud */

    $username   = isset($jsonObject->username)  ? clean_data(trim_double($jsonObject->username))    : "";
    $token      = isset($jsonObject->token)     ? clean_data(trim_double($jsonObject->token))       : "";


    /** Obtenemos la llave secreta para descifrar el token */

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_login_info", 
        $where_conditions = ["users_name" => $username]
    );

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['id']) )
    {
        $error_code = '403-01';

        $msg = 'Solicitud no autorizada';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }

    $user_id = $result['fetched'][0]['id'];


    /** Obtenemos la llave para desencriptar el token */

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_security_data", 
        $where_conditions = ["id_user" => $user_id]
    );

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['secret_key_bearer']) )
    {
        $error_code = '403-02';

        $msg = 'Solicitud no autorizada';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }

    $secret_key_bearer = $result['fetched'][0]['secret_key_bearer'];

    /** Decodificamos el token token Bearer para confirmar el email del usuario */
    
    /** Procedemos a decodificar el token bearer recibido por GET */
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    $secret_key = SECRET_KEY_BEARER;
        
    
    try {        
        $bearer_decoded = JWT::decode($token, new Key($secret_key_bearer, 'HS256'));
    } catch (\Throwable $th) {
        $error_code = '403-03';

        $msg = 'Solicitud no autorizada';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        exit;
    }

    
    /** Procedemos a validar la informacion del token desencriptado */

    // verificamos que el nombre de usuario sea el mismo del token
    $has_error = ($bearer_decoded->users_name != $username ) ? true : false;
    
    // verificamos que el id de usuario recuperado sea el mismo del token
    $has_error = ($bearer_decoded->id_user != $user_id ) ? true : false;

    // verificamos que el token no este expirado
    $has_error = ( (time() - $bearer_decoded->expere_time) > 0 ) ? true : false;


    if ($has_error)
    {
        $error_code = '403-04';

        $msg = 'Solicitud no autorizada';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////