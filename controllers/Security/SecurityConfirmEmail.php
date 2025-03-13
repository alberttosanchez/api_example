<?php // security-confirm_email

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
        
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        $error_code = '403';

        $msg = 'Error. Faltan parametros.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();        
    }


    $email_token    = isset($jsonObject->email_token) ? clean_data(trim_double($jsonObject->email_token)) : "";
       
    ## Obtenemos el secret_key_bearer de la base de datos con el token

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_security_data", 
        $where_conditions = ["token_bearer" => $email_token]
    );    

    // si el array fetched esta seteado
    if ( ! isset($result['fetched'][0]['secret_key_bearer']) || $result['fetched'][0]['email_confirmed'] > 0 )
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

    $secret_key = SECRET_KEY_BEARER;
        
    
    try {        
        $bearer_decoded = JWT::decode($email_token, new Key($secret_key_bearer, 'HS256'));
    } catch (\Throwable $th) {
        $error_code = '403-02';

        $msg = 'Token invalido.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        exit;
    }

    // si el id_user no esta seteado significa que el token no se desencripto
    if ( ! isset($bearer_decoded->id_user) )
    {
        $error_code = '403-03';

        $msg = 'Token invalido.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }
    
    ## Procedemos a confirma el correo electronico

    $table_name = 'smtapi_users_security_data';

    $array_update_security_data = [            
        'email_confirmed'   => 1,
        //'secret_key_bearer' => '',                   
    ];
    
    $array_assoc_where = [
        'id_user'            => $bearer_decoded->id_user,            
    ];

    // insertamos los valores en la tabla
    // devulve true si lo inserta
    // updateAll($table_name = "", $fields_array = [] , $where_conditions = [] )
    $result = $ManageDB->updateAll( $table_name , $array_update_security_data , $array_assoc_where );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '403-04';

        $msg = 'token invalido.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }

    /** Si no hubo error devolvemos una respuesta satisfactoria */

    $msg = 'Correo Electrónico confirmado';

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