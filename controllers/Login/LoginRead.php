<?php // target : login-read

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
        
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        on_exception_server_response(403,[],$target);
        die();        
    }
    
    # Capturamos los datos enviados por POST
    
    $username           = isset($jsonObject->username)           ? clean_data(trim_double($jsonObject->username)) : "";
    $password           = isset($jsonObject->password)           ? clean_data(trim_double($jsonObject->password)) : "";
    $save_login_session = isset($jsonObject->save_login_session) ? clean_data(trim_double($jsonObject->save_login_session)) : "off";
    
    /* Procedemos a verificar que el usuario exista en la base de datos */

    # instanciamos la clase Conexion    
    # $Conexion = new Library\Classes\Conexion; -> ya fue instancia en el archivo api.php
    # $conn = $Conexion->get(DB_CONFIG); -> ya fue asignada en el archivo api.php

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

    }

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['users_email']) )
    {
        $error_code = '403';

        $msg = 'El usuario o contraseña es incorrecto.';

        $message = arr_multi_lang($msg); 
       
        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }
   
    $id_user        = $result['fetched'][0]['id'];
    $users_name     = $result['fetched'][0]['users_name'];
    $users_email    = $result['fetched'][0]['users_email'];

    $password_hash  = $result['fetched'][0]['users_password'];


    /** Procedemos a verificar la contraseña recibida del usuario */

    if ( ! password_verify($password, $password_hash) ) {  
        
        $error_code = '403';

        $msg = 'El usuario o contraseña es incorrecto.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(403,$message,$target,$error_code);
        die();        
    }
    
    /** obtenemos los datos de la account */

    // table : smtapi_users_account

    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_users_account',
        $filter = "id_user",
        $keyword = $id_user,            
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
    if ( ! isset($result['fetched'][0]['id_user']) )
    {
        $error_code = '500-03';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }
    // si el id de usuario esta seteado
    if( isset($result['fetched'][0]['id_user']) )
    {
        // si el id enviado no es igual al obtenido
        if ( $result['fetched'][0]['id_user'] != $id_user )
        {
            $error_code = '500-04';

            $msg = 'Contacte al administrador de sistemas.';

            $message = arr_multi_lang($msg); 

            on_exception_server_response(409,$message,$target,$error_code);
            die();
        }
    }
    
    $first_name = $result['fetched'][0]['first_name'];
    $last_name  = $result['fetched'][0]['last_name'];
    
    /** obtenemos los datos de seguridad  */
        
        // table : smtapi_users_security_data

    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_users_security_data',
        $filter = "id_user",
        $keyword = $id_user,            
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
    if ( ! isset($result['fetched'][0]['id_user']) )
    {
        $error_code = '500-05';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);       

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }
    // si el id de usuario esta seteado
    if( isset($result['fetched'][0]['id_user']) )
    {
        // si el id enviado no es igual al obtenido
        if ( $result['fetched'][0]['id_user'] != $id_user )
        {
            $error_code = '500-06';

            $msg = 'Contacte al administrador de sistemas.';

            $message = arr_multi_lang($msg);

            on_exception_server_response(409,$message,$target,$error_code);
            die();
        }
    }
    
    $id_sysrol          = $result['fetched'][0]['id_sysrol'];
    $id_userrol         = $result['fetched'][0]['id_userrol'];
    $email_confirmed  = $result['fetched'][0]['email_confirmed'];
    $id_account_state   = $result['fetched'][0]['id_account_state'];

    $session_token = session_id();

    ## Obtenemos la imagen de perfil

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_profile", 
        $where_conditions = ["id_user" => $id_user]
    );

    if (!isset($result['fetched'][0]['thumbnail_path']) && (int)$id_user != (int)$result['fetched'][0]['id_user'] ) {

        $error_code = '409-01';

        $msg = 'Error 409. Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    // obtenemos la url de la foto de perfil y la agregamos a la respuesta
    $array_thumbnail_path = (array)json_decode($result['fetched'][0]['thumbnail_path']);
    $profile_public_photo_url = $array_thumbnail_path['public_url'] . $array_thumbnail_path['filename'];


    /** Generamos el token Bearer con los datos del usuario identificado */    

    // Si recuerda la session el token vence dentro de 6 meses de lo contrario, vence en 24 horas. -> ver setting.php
    $time_to_expire     = ( $save_login_session == 'on') ? TOKEN_BEARER_LARGE_EXPIRATION_TIME : TOKEN_BEARER_SINGLE_EXPIRATION_TIME;
    $token_expire_time  = $time_to_expire;

    $payload = array(
        "id_user"           => $id_user,
        "users_name"        => $users_name, // table: smtapi_users_login_info
        "users_email"       => $users_email,

        "first_name"        => $first_name,
        "last_name"         => $last_name,

        "id_sysrol"         => $id_sysrol,
        "id_userrol"        => $id_userrol, // table: smtapi_users_security_data
        "email_confirmed"   => $email_confirmed, 
        "id_account_state"  => $id_account_state,

        "session_token"     => $session_token, //  table: smtapi_users_security_session | token de la sesion de php activa session_id()
        "expere_time"       => $token_expire_time,        
    );

    use \Firebase\JWT\JWT;

    // random llave secreta para validar el token bearer
    $secret_key = base64_encode(random_bytes(32));
    
    try {        
        $token_bearer_for_login_session = JWT::encode($payload, $secret_key, 'HS256');
    } catch (\Throwable $th) {

        $error_code = '500-07';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    /** Guardamos la llave secreta y el token bearer en los datos de seguridad del usuario */

    $table_name = 'smtapi_users_security_data';

    $array_security_data = [
        'id_user'           => (int)$id_user,
        'token_bearer'      => $token_bearer_for_login_session,
        'secret_key_bearer' => $secret_key        
    ];
    
    $result = $ManageDB->update( $table_name , $array_security_data );
    
    // si no se actualizo el registro
    if ( $result == false )
    {
        $error_code = '500-08';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    /** Si no hubo error devolvemos una respuesta satisfactoria */
   
    $msg = 'Sesión Iniciada Correctamente.';

    $message = arr_multi_lang($msg);

    $avatar_image_url = $profile_public_photo_url;
    
    $response = [
        'status'    => '200',
        'message'   => $message,        
        'data'      => [ 
            "users_name"        => $users_name, // table: smtapi_users_login_info
            "users_email"       => $users_email,
            "first_name"        => $first_name,
            "last_name"         => $last_name,
            "avatar_image"      => $avatar_image_url,
            "id_sysrol"         => $id_sysrol,
            "id_userrol"        => $id_userrol, // table: smtapi_users_security_data
            "email_confirmed"   => $email_confirmed, 
            "id_account_state"  => $id_account_state,
            'token_bearer'      => $token_bearer_for_login_session,
        ],
        'error_code'            => '0',
    ];

    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;