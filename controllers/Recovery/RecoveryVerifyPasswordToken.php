<?php // recovery-verify_password_token

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
        
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        $error_code = '403';

        $msg = 'Error. Faltan parametros.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();        
    }


    $password_token    = isset($jsonObject->password_token) ? clean_data(trim_double($jsonObject->password_token)) : "";
       
    ## Obtenemos el secret_key_bearer de la base de datos con el token

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_security_data", 
        $where_conditions = ["token_bearer" => $password_token]
    );    
    
    // si el array fetched esta seteado
    if ( ! isset($result['fetched'][0]['secret_key_bearer']) )
    {
    $error_code = '403-01';

    $msg = 'Token invalido.';

    $message = arr_multi_lang($msg); 

    on_exception_server_response(403,$message,$target,$error_code);
    die();
        
    }

    $secret_key_bearer = $result['fetched'][0]['secret_key_bearer'];

    /** Decodificamos el token token Bearer para confirmar el email del usuario */
    
    /** Procedemos a decodificar el token bearer recibido por GET */
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;   
            
    try {        
        $bearer_decoded = JWT::decode($password_token, new Key($secret_key_bearer, 'HS256'));
    } catch (\Throwable $th) {
        $error_code = '403-02';

        $msg = 'Token invalido.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        exit;
    }
    
    //var_dump($bearer_decoded); die();

    // si el id_user no esta seteado significa que el token no se desencripto
    if ( ! isset($bearer_decoded->id) )
    {
        $error_code = '403-03';

        $msg = 'Token invalido.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }
    
    ## Procedemos a verificar si el token aun es valido

    // obtenemos el tiempo en UTC donde va a expirar el token
    $timestamp = $bearer_decoded->exp;

    // obtenemos la fecha actual
    $current_time = time();

    // si el tiempo actual es mayor el token es invalido.
    if ( $current_time > $timestamp )
    {
        ## Procedemos a eliminar la llave secreta que decodifica el token
    
        $table_name = 'smtapi_users_security_data';
    
        $array_update_security_data = [                    
            'secret_key_bearer' => '',
        ];
        
        $array_assoc_where = [
            'id_user'            => $bearer_decoded->id,            
        ];
    
        // insertamos los valores en la tabla
        // devulve true si lo inserta
        // updateAll($table_name = "", $fields_array = [] , $where_conditions = [] )
        $result = $ManageDB->updateAll( $table_name , $array_update_security_data , $array_assoc_where );
    
        // si no se inserto el registro
        if ( $result == true )
        {
            $error_code = '0';
    
            $msg = 'token invalido.';
    
            $message = arr_multi_lang($msg);
    
            on_exception_server_response(403,$message,$target,$error_code);
            die();
        }
    }

    /** Si no hubo error devolvemos una respuesta satisfactoria */

    $msg = 'Token Validado. Proceder al cambio de clave de acceso.';

    $message = arr_multi_lang($msg); 

    $response = [
        'data'      => [],
        'error_code'=> '0',
        'message'   => $message,
        'status'    => '200',
        'target'    => $target,
    ];

    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;