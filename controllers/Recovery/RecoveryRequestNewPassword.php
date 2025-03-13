<?php // target : recovery-request_new_password

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
    
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        $error_code = '403';

        $msg = 'Error. Faltan parametros.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();        
    }
    
    # Capturamos los datos enviados en el objeto json
    
    $username    = isset($jsonObject->username) ? clean_data(trim_double($jsonObject->username)) : "";
    

    /* Procedemos a verificar que el correo o usuario exista en la base de datos */

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos los datos del usuario que inicia sesion
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_users_login_info',
        $filter = "users_name",
        $keyword = $username,            
        $limit = 1,
        $selected_page = '1',
        $array_fields = false,
        $order_by = 'id',
        $order_dir = 'ASC',
        $filter_between = "",
        $array_between = false,
        $strict_mode = true
    );
    
    $fetched_users_name = (isset($result['fetched'][0]['users_name'])) ? $result['fetched'][0]['users_name'] : "";

    // si no iguales consulta por correo el usuario que inicia sesion
    if ( $fetched_users_name != $username )
    {
        // obtenemos los datos del usuario activo
        $result = $ManageDB->get_table_rows(
            $table_name = 'smtapi_users_login_info',
            $filter = "users_email",
            $keyword = $username,            
            $limit = 1,
            $selected_page = '1',
            $array_fields = false,
            $order_by = 'id',
            $order_dir = 'ASC',
            $filter_between = "",
            $array_between = false,
            $strict_mode = true
        );

        // si el array fetched no esta seteado y el array esta vacio
        if ( ! isset($result['fetched'][0]['users_email']) || $result['fetched'][0]['users_email'] != $username )
        {
            $error_code = '0';
    
            $msg = 'Si los datos son correctos recibirá un correo electrónico para recuperar su contraseña.';
    
            $message = arr_multi_lang($msg); 
           
            on_exception_server_response(200,$message,$target,$error_code);
            die();
        }

    }    
    
    $id_user    = $result['fetched'][0]['id'];
    $users_name = $result['fetched'][0]['users_name'];
    $users_email= $result['fetched'][0]['users_email'];
    
    
    /** Procedemos a crear el token token Bearer */

    // random llave secreta para recuperar la contraseña
    $secret_key = base64_encode(random_bytes(32));

    use \Firebase\JWT\JWT;

    $payload = array(
        "id"        => $id_user,
        "username"  => $users_name,
        "email"     => $users_email,        
        "exp"       => EMAIL_RECOVER_PASSWORD_EXPIRATION_TIME
    );

    $token_bearer_to_recover_password = JWT::encode($payload, $secret_key, 'HS256');

    /** Guardamos la llave secreta y el token bearer en los datos de seguridad del usuario */

    $table_name = 'smtapi_users_security_data';

    $array_security_data = [
        'id_user'           => (int)$id_user,
        'token_bearer'      => $token_bearer_to_recover_password,
        'secret_key_bearer' => $secret_key        
    ];
    
    $result = $ManageDB->update( $table_name , $array_security_data );
    
    // si no se actualizo el registro
    if ( $result == false )
    {

        $error_code = '500-1';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }


    /** Procedemos a enviar un correo electronico al cliente con el token de confirmación de su cuenta de correo */

    // instanciamos la clase email
    $Email = new Library\Classes\Email;

    $arrayData = [
        'emailToUser'   => $users_email,  # email
        'emailToStaff'  => EMAIL_TO_STAFF,  # email
        'aliasName'     => $users_name,  # string							
        'msgToUser'     => EMAIL_TO_USER_FOR_PASSWORD_RECOVER_FILE,	# url archivo							        
        'where'         => 'mail_to_recover_password',	# int       Ver -> metodo send();
        'token'         => $token_bearer_to_recover_password 	# string    Ver -> functions.php           
    ];

    // procedemos a enviar el correo electronico de activacion
    // y el correo de notificacion de nuevo usuario al correo de soporte interno
    $result = $Email->send($arrayData);

    if ( $result == false )    
    {
        $error_code = '500-02';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    /** Si no hubo error devolvemos una respuesta satisfactoria */
   
    $msg = 'Si los datos son correctos recibirá un correo electrónico para recuperar su contraseña.';

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