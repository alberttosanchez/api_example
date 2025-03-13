<?php

    # Recibe un objeto json
    $jsonString = file_get_contents('php://input'); 

    // lo convertimos en una clase php 
    // $jsonObject = json_decode($jsonString);
    
    # Recibe un objeto json string o un formulario multipart/form-data, no envie ambos en una sola peticion.     
    $jsonObject = ( !empty($jsonString) )       ? json_decode($jsonString) : (object) $_POST;    
    $target     = isset($jsonObject->target)    ? clean_data(trim_double($jsonObject->target)) : "";
    //$form_id    = isset($jsonObject->form_id)   ? clean_data(trim_double($jsonObject->form_id)) : "";    
    
    $recover_password = isset($jsonObject->recover_password)   ? clean_data(trim_double($jsonObject->recover_password)) : "";  
    $account_confirm = isset($_GET['account_confirm']) ? clean_data(trim_double($_GET['account_confirm'])) : "";
    
    # algoritmo para verificar la cuenta   
    if ( isset($account_confirm) && strlen($account_confirm) > 0)
    {        
        $target = 'signup-confirm_account';
        include_once( BASE_PATH . '/controllers/Signup/SignupConfirmAccount.php');
        die();
    }
    # algoritmo para recuperar la contraseña
    if ( isset($recover_password) && strlen($recover_password) > 0)
    {        
        $target = 'recover-password-read';
        include_once( BASE_PATH . '/controllers/Recover/Password/RecoverPasswordRead.php');
        die();
    }

    $APACHE_HEADERS = apache_request_headers();    
    
    header("Access-Control-Allow-Origin: " . CORS_HTTP_ORIGIN);    
    
    /** Este comprobacion es para el preflight de nextjs CORS */
    if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
    {
        header("Access-Control-Allow-Headers: Access-Control-Request-Method, X-API-KEY, Authorization, Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type, Accept, X-Custom-Header");
        header("HTTP/1.1 200 OK");
        die();
    }
    
    # Si el content-length sobrepasa el permitido
    if ( isset($APACHE_HEADERS["Content-Length"]) && $APACHE_HEADERS["Content-Length"] >  POST_FILE_MAX_SIZE )        
    {
        $error_code = '403';
        
        $msg = "Los archivos exceden la capacidad permitida de carga.";

        // devuelve un array con varias traducciones -> ver functions.php
        $message = arr_multi_lang($msg);

        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }
    # Verificamos que se haya enviado el token Bearer
    # si las cabeceras apaches estan seteadas y la propiedad Authorizcion esta presente
    if ( isset( $APACHE_HEADERS["Authorization"] ) || isset( $APACHE_HEADERS["authorization"] ) )
    {        
        $bearer_auth = isset( $APACHE_HEADERS["Authorization"] ) ? $APACHE_HEADERS["Authorization"] : $APACHE_HEADERS["authorization"];
    }
    # de lo contrario si la propiedad HTTP_AUTHORIZATION esta presente en el objeto $_SERVER
    else if ( isset($_SERVER['HTTP_AUTHORIZATION']) )
    {        
        $bearer_auth = $headers['HTTP_AUTHORIZATION'];
    }
        
    # En este caso el token recibido es un hash generado con password hash como engaño
    if( !isset( $bearer_auth ) || ! password_verify(SECRET_KEY_BEARER, trim(str_replace("Bearer","",$bearer_auth)))) {

        $error_code = '403-01';

        $msg = "Faltan parametros.";

        $message = arr_multi_lang($msg);

        // si el token no coincide devuelve un error
        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }

    /*
     Verificamos el metodo de envio y si se enviaron ciertos parametros tales como
     metodo de envio, parametros: target, form_id y el token bearer
    */
    if ( 
        $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($target) || empty($target) || 
        //!isset($form_id) || empty($form_id) || $form_id !== session_id() || 
        !isset($bearer_auth) || empty($bearer_auth)
        )
    {

        $error_code = '403-02';

        $msg = "Faltan parametros.";

        $message = arr_multi_lang($msg);

        // Si el metodo no es post, el target no esta definido y la session no es la misma
        // entonces resultado 403.
        on_exception_server_response(403,$message,$target,$error_code);
        die();
    }    
    
    //instanciamos la conexion
    $Conexion = Library\Classes\Conexion::singleton();

    //obtenemos la conexion
    $conn = $Conexion->get(DB_CONFIG);

    // si no hay conexion
    if ( ! $conn )
    {   

        $error_code = '409-01';

        $msg = "Contacte al administrador.";

        $message = arr_multi_lang($msg);

        // Devuelve el codigo de respuesta del servidor especificado y un json con datos.        
        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }
   
    $array_target = explode("-",$target);
    
    $target_lv1 = isset($array_target[0]) ? $array_target[0] : "";
    $target_lv2 = isset($array_target[1]) ? $array_target[1] : "";
    $target_lv3 = isset($array_target[2]) ? $array_target[2] : "";
    
    switch ($target_lv1) {
        case 'categories':                
            switch ($target_lv2) {                
                case 'countries_states_and_cities': include_once( BASE_PATH . '/controllers/Categories/CategoriesCountriesStatesAndCities.php'); break;                
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;

        case 'login':                
            switch ($target_lv2) {                
                case 'read':          include_once( BASE_PATH . '/controllers/Login/LoginRead.php'); break;
                case 'with_facebook': include_once( BASE_PATH . '/controllers/Login/LoginWithFacebook.php'); break;
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;
        case 'posts':                
            switch ($target_lv2) {                
                case 'read':    include_once( BASE_PATH . '/controllers/Posts/PostsRead.php'); break;                
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;
        case 'profile':                
            switch ($target_lv2) {                
                case 'read':    include_once( BASE_PATH . '/controllers/Profile/ProfileRead.php'); break;
                case 'update':  include_once( BASE_PATH . '/controllers/Profile/ProfileUpdate.php'); break;
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;    
        case 'security': 
            switch ($target_lv2) {
                case 'confirm_email':  include_once( BASE_PATH . '/controllers/Security/SecurityConfirmEmail.php'); break;                
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;
        case 'signup': 
            switch ($target_lv2) {
                case 'create':  include_once( BASE_PATH . '/controllers/Signup/SignupCreate.php'); break;
                case 'with_facebook': include_once( BASE_PATH . '/controllers/Signup/SignupWithFacebook.php'); break;
                #case 'read':    include_once( BASE_PATH . '/controllers/Signup/SignupRead.php'); break;
                #case 'update':  include_once( BASE_PATH . '/controllers/Signup/SignupUpdate.php'); break;
                #case 'delete':  include_once( BASE_PATH . '/controllers/Signup/SignupDelete.php'); break;
                default: on_exception_server_response(403,[],'403'); break;
            }
            break;
        case 'recovery':                
            switch ($target_lv2) {
                case 'change_password': include_once( BASE_PATH . '/controllers/Recovery/RecoveryChangePassword.php'); break;
                case 'request_new_password': include_once( BASE_PATH . '/controllers/Recovery/RecoveryRequestNewPassword.php'); break;                
                case 'verify_password_token': include_once( BASE_PATH . '/controllers/Recovery/RecoveryVerifyPasswordToken.php'); break;
                default: on_exception_server_response(403,[],'403'); break;
            }        
            break;             
        default: on_exception_server_response(403,[],'403'); break;

    }
    die();