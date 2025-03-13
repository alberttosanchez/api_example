<?php // target : signup-create
    
    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
    
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {   
        
        $error_code = '403-01';
        // Faltan parametros
        on_exception_server_response(403,[],$target,$error_code);

        die();        
    }
      
    # Capturamos los datos enviados por POST
    
    $username   = isset($jsonObject->username)  ? clean_data(trim_double($jsonObject->username)) : "";
    $name       = isset($jsonObject->name)      ? clean_data(trim_double($jsonObject->name)) : "";
    $lastname   = isset($jsonObject->lastname)  ? clean_data(trim_double($jsonObject->lastname)) : "";
    $email      = isset($jsonObject->email)     ? clean_data(trim_double($jsonObject->email)) : "";    
    $password   = isset($jsonObject->password)  ? clean_data(trim_double($jsonObject->password)) : "";
    
    $form = [        
        'name'          => $name,
        'lastname'      => $lastname,
        'username'      => $username,
        'email'         => $email,
        'password'      => $password,        
    ];

    # instanciamos la clase ValidateForms
    $ValidateForms = new Library\Classes\ValidateForms;
    
    $result = $ValidateForms->validateFormFields($form,[
        'name'      => "string|min-length:3|max-length:100",
        'lastname'  => "string|min-length:3|max-length:100",
        'username'  => "string|min-length:3|max-length:100",            
        'email'     => "email|required|regex:/[a-z\d\.\-\_]{3,}@{1}[a-z\d\-\_]{3,}[\.]{1}[a-z]{2,3}[\.]{0,1}[a-z]{0,2}/i",
        'password'  => "string|min-length:8|max-length:100",        
    ],[
        "El nombre debe tener al menos 3 caracteres.",            
        "El apellido debe tener al menos 3 caracteres.",            
        "El nombre de usuario debe tener al menos 3 y un máximo de 100 caracteres.",            
        "Escriba su correo electrónico. Solo se permiten letras, numero puntos (.), guiones (-) y guiones bajos (_).",            
        "La contraseña debe tener entre 8 y 100 caracteres.",                    
    ]);
   
    if ( $result["status"] == "failed" ) {
        
        $error_code = '0';

        $msg = $result["message"];

        $message = arr_multi_lang($msg);

        on_exception_server_response(200,$message,$target,$error_code,$result);
        die();        
    }
    
    /* Procedemos a verificar que el usuario no exista en la base de datos */

    # instanciamos la clase Conexion    
    # $Conexion = new Library\Classes\Conexion; -> ya fue instancia en el archivo api.php

    # $conn = $Conexion->get(DB_CONFIG); -> ya fue asignada en el archivo api.php

    // instanciamos Firebase
    use \Firebase\JWT\JWT;  

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos los datos del usuario activo
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
    
    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched']) )
    {
        $error_code = '500-01';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }
   
    // si el nombre de usuario esta seteado
    if( isset($result['fetched'][0]['users_name']) )
    {
        // si el usuario enviado es igual al obtenido
        if ( $result['fetched'][0]['users_name'] == $username )
        {
            $error_code = '409-02';

            $msg = 'El nombre de usuario ya existe.';

            $message = arr_multi_lang($msg);

            on_exception_server_response(409,$message,$target,$error_code);
            die();
        }
    }

    /* Procedemos a verificar que el correo no exista en la base de datos */


    // obtenemos los datos del correo activo
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_users_login_info',
        $filter = "users_email",
        $keyword = $email,            
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
    if ( ! isset($result['fetched']) )
    {
        $error_code = '500-02';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);        

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }
   
    // si el nombre de usuario esta seteado
    if( isset($result['fetched'][0]['users_email']) )
    {
        // si el usuario enviado es igual al obtenido
        if ( $result['fetched'][0]['users_email'] == $email )
        {
            $error_code = '409-03';

            $msg = 'El correo electrónico ya existe.';

            $message = arr_multi_lang($msg);

            on_exception_server_response(409,$message,$target,$error_code);
            die();
        }
    }
  
    /** Procedemos a registrar al usuario */

    # tabla smtapi_users_login_info

    $table_name = 'smtapi_users_login_info';

    // Encryptamos la clave secreta del usuario
    $hash = password_hash($password,PASSWORD_DEFAULT);

    $array_new_register_data = [
        'users_name'    => $username,
        'users_email'   => $email,
        'users_password'=> $hash,        
    ];
    
    // insertamos los valores en la tabla
    // devulve true si lo inserta
    $result = $ManageDB->insert( $table_name , $array_new_register_data );
    
    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-03';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }

    /** Procedemos a completar otra tablas relacionadas al registro */

    // obtenemos los datos del usuario que se acaba de registrar de la base de datos
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
    
    // si el nombre de usuario no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['id']) )
    {
        $error_code = '500-04';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }
   
    # tabla smtapi_users_security_data

    // obtenemos el id de usuario, el nombre de usuario y el correo electronico
    $id_user     = $result['fetched'][0]['id'];
    $users_name  = $result['fetched'][0]['users_name'];     
    $users_email = $result['fetched'][0]['users_email'];     

    $id_userrol = "2"; // 2: FINAL_USER

    // random llave secreta para validar el correo electronico
    # $secret_key = base64_encode(random_bytes(32));

    // llave secreta constante para validar el correo electronico
    $secret_key = SECRET_KEY_BEARER;

    $table_name = 'smtapi_users_security_data';

    $array_new_security_data = [
        'id_user'           => (int)$id_user,
        'id_sysrol'         => 3, // 3:usuario
        'id_userrol'        => (int)$id_userrol,
        'id_account_state'  => 1,
        // 'secret_key_bearer' => $secret_key, -- no es necesario en este proceso ya que se usa una constante. ver setting.php
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {

        $error_code = '500-05';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }

    # tabla smtapi_users_verification_data

    $table_name = 'smtapi_users_verification_data';

    $array_new_security_data = [
        'id_user'           => (int)$id_user,
        'email_confirmed'   => 2, // 2:no confirmado
        'phone_confirmed'   => 2, // 2:no confirmado
        'dni_confirmed'     => 2, // 2:no confirmado
        'address_confirmed' => 2, // 2:no confirmado
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {

        $error_code = '500-06';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);       

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }

    # tabla smtapi_users_emails

    $table_name = 'smtapi_users_emails';
    $user_email = $users_email;  

    $array_new_security_data = [
        'id_user'       => (int)$id_user,
        'user_email'    => $user_email,
        'id_email_type' => 1, // 1:principal        
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-07';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);        

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }

    # tabla smtapi_users_security_session

    $table_name = 'smtapi_users_security_session';
    
    $array_new_security_data = [
        'id_user'           => (int)$id_user,
        'id_session_state'  => 2, // 2:sesion expirada        
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-08';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);       

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }
    
    # tabla smtapi_users_account

    $table_name = 'smtapi_users_account';    

    $array_new_security_data = [
        'id_user'       => (int)$id_user,
        'first_name'    => $name,
        'last_name'     => $lastname,
        'id_civil_state'=> 1, // 1:soltero(a)
        'id_ident_type' => 1, // 1:cedula
        'id_gender'     => 3, // 3:indefinido
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-09';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);          

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }

    # tabla smtapi_users_profile

    $table_name = 'smtapi_users_profile';    

    $array_new_security_data = [
        'id_user'       => (int)$id_user,
        'agency_name'   => $name ." ". $lastname,                
        'id_country'    => 65, // 65:Republica Dominicana
        'id_zone'       => 2,  // 2:Este
        'id_province'   => 5,  // 5:distrito nacional
        'id_city'       => 5,  // 5:Santo Domingo 
    ];
    
    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-09';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);          

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    }    

    # tabla smtapi_users_rating

    $table_name = 'smtapi_users_rating';
        
    $array_new_security_data = [
        'id_user'       => (int)$id_user,
    ];

    $result = $ManageDB->insert( $table_name , $array_new_security_data );

    // si no se inserto el registro
    if ( $result == false )
    {
        $error_code = '500-11';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(200,$message,$target,$error_code);
        die();
    } 

    ## Obtenemos los datos de la account 

    # table : smtapi_users_account

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
    
    ## Obtenemos los datos de seguridad
        
    # table : smtapi_users_security_data

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
    $email_confirmed    = $result['fetched'][0]['email_confirmed'];
    $id_account_state   = $result['fetched'][0]['id_account_state'];

    $session_token = session_id();

    ## Generamos el token Bearer con los datos del usuario identificado

    // El token vence dentro de 6 meses. -> ver setting.php
    //$time_to_expire     = ( $save_login_session == 'on') ? TOKEN_BEARER_LARGE_EXPIRATION_TIME : TOKEN_BEARER_SINGLE_EXPIRATION_TIME;
    $time_to_expire     = TOKEN_BEARER_LARGE_EXPIRATION_TIME;
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

    // random llave secreta para validar el token bearer
    $secret_key = base64_encode(random_bytes(32));
    
    try {        
        $token_bearer = JWT::encode($payload, $secret_key, 'HS256');
    } catch (\Throwable $th) {

        $error_code = '500-077';

        $msg = 'Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    ## Guardamos la llave secreta y el token bearer en los datos de seguridad del usuario

    $table_name = 'smtapi_users_security_data';

    $array_security_data = [
        'id_user'           => (int)$id_user,
        'token_bearer'      => $token_bearer,
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

    /** Procedemos a enviar un correo electronico al cliente de bienvenida a su cuenta de correo */

    // instanciamos la clase email
    $Email = new Library\Classes\Email;

    $arrayData = [
        'emailToUser'   => $user_email,                     # email
        'emailToStaff'  => EMAIL_TO_STAFF,                  # email
        'aliasName'     => $users_name,                     # string
        'msgToUser'     => EMAIL_TO_NEW_USER_FILE,          # url
        'msgToStaff'    => EMAIL_TO_STAFF_FOR_NEW_USER_FILE,# url
        'where'         => 'mail_to_new_user',
        'token'         => ''
    ];


    ## Obtenemos la imagen de perfil

    // en este caso se deja vacio, porque es un registro de nuevo usuario
    $avatar_image_url = "";


    /** Obtenemos los datos del nuevo usuario de la base de datos 
     * para crear un token bearer con dicha informacion
    */

    /** Si no hubo error devolvemos una respuesta satisfactoria */
   
    $msg = 'Usuario registrado correctamente.';

    $message = arr_multi_lang($msg); 

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
            'token_bearer'      => $token_bearer,
        ],
        'error_code'            => '0',
    ];

    // procedemos a enviar el correo electronico de bienvenida al nuevo usuario
    // y el correo de notificacion de nuevo usuario al correo de soporte interno
    $result = $Email->send($arrayData);

    if ( $result == false )    
    {
        $error_code = '500-12';        

        $msg = 'Usuario Registrado. Email no enviado.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    /** Procedemos a enviar un correo electronico al cliente con el token de confirmación de su cuenta de correo */

    $arrayData = [
        'emailToUser'   => $user_email,  # email
        'aliasName'     => $users_name,  # string
        'msgToUser'     => EMAIL_TO_NEW_USER_TO_EMAIL_CONFIRMATION_FILE,	# url archivo
        'where'         => 'mail_to_active_account',	# int       Ver -> metodo send();
        'token'         => $token_bearer 	# string    Ver -> functions.php
    ];

    // procedemos a enviar el correo electronico de activacion
    // y el correo de notificacion de nuevo usuario al correo de soporte interno
    $result = $Email->send($arrayData);

    if ( $result == false )    
    {
        $error_code = '500-13';        

        $msg = 'Usuario Registrado. Email no enviado.';

        $message = arr_multi_lang($msg);

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }
    
    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;