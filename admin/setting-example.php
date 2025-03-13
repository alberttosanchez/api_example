<?php 

//------------- CONSTANTES DE CONFIGURACION


define('DEBUG', false ); // true: para usar en localhost en desarrollo

// Lista de orígenes permitidos
$allowed_origins = [
    'https://sumate.com.do',
    'https://www.sumate.com.do',
    'https://svmate.com',
    'https://www.svmate.com',
    'https://debugger.svmate.com'
];

if (DEBUG)
{
    // Agregar un nuevo elemento al final del array
    array_push($allowed_origins, 'https://localhost:3000');
}

// Obtener el origen de la solicitud
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Verificar si el origen de la solicitud está en la lista de permitidos
if (in_array($origin, $allowed_origins)) {
    // Establecer la cabecera CORS con el origen permitido
    define('CORS_HTTP_ORIGIN', $origin );
}
else
{
    define('CORS_HTTP_ORIGIN', 'https://sumate.com.do' );
}

date_default_timezone_set('America/Santo_Domingo');

define('SECRET_KEY_HASH', password_hash( '' , PASSWORD_DEFAULT) );
define('SECRET_KEY_BEARER', '' );

define('SEND_MAIL', true );

/** Define el idioma por defecto a motrar al cargar el portal o responder en la api los mensajes 
 *  es_EN : ingles ; es_FR : Frances ; es_ES : Español
*/
define('LANG', 'es_EN' );

/** Database settings - You can get this info from your web host */

/** Database name */
define( 'DB_NAME', '' );

/** Database username */
define( 'DB_USER', '' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

define( 'DB_CONFIG', [
    'host'      => DB_HOST,
    'db_name'   => DB_NAME,
    'user'      => DB_USER,
    'password'  => DB_PASSWORD    
]);

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Nombre del Sitio */
define( 'SITE_NAME', 'API SMT');

/** Url Base para includes y require */
define( 'BASE_PATH', '.');

//------------- CONSTANTES DE RUTAS INTERNAS

if ( DEBUG == true )
{
    define( "API_DOMAIN_URL" , "https://api.sumate.local" );
}
else
{
    define( "API_DOMAIN_URL" , "https://api.sumate.com.do" );
}

define( "API_URL_BASE" , "/" );

define( "API_CLASSES_DIRECTORY" , "/class");

define( "API_LANG_DIRECTORY" , API_URL_BASE . 'languajes');

define( "API_PUBLIC_DIRECTORY" , API_URL_BASE . 'public');

//------------- CORREO ELECTRONICOS CONSTANTES

/** Correo electronico enviado al soporte interno */
define('EMAIL_TO_STAFF','support@sumate.com.do'); 

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_STAFF','support@sumate.com.do');

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_SUBSCRIPTIONS','subscriptions@sumate.com.do');

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_NO_REPLY','no-reply@sumate.com.do');

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_SECURITY', 'security@sumate.com.do');

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_CONTACT', 'contact@sumate.com.do');

/** Correo electronico enviado desde el soporte interno */
define('EMAIL_FROM_RELATIONSHIP', 'relationship@sumate.com.do');

/** Rutas de los templates para los correo electrónicos */
define(
    'EMAIL_TO_USER_FOR_PASSWORD_RECOVER_FILE', 
    $_SERVER['DOCUMENT_ROOT']."/src/library/files/notificacion_to_recover_password_message.html"
);    
define(
    'EMAIL_TO_USER_FOR_PASSWORD_CHANGED_FILE', 
    $_SERVER['DOCUMENT_ROOT']."/src/library/files/notificacion_change_password_message.html"
);
define(
    'EMAIL_TO_NEW_USER_FILE',
    $_SERVER['DOCUMENT_ROOT']."/src/library/files/notificacion_to_new_user_message.html"
);
define(
    'EMAIL_TO_STAFF_FOR_NEW_USER_FILE', 
    $_SERVER['DOCUMENT_ROOT']."/src/library/files/notificacion_to_staff_for_new_user_message.html"
);
define(
    'EMAIL_TO_NEW_USER_TO_EMAIL_CONFIRMATION_FILE', 
    $_SERVER['DOCUMENT_ROOT']."/src/library/files/notificacion_to_new_user_for_email_confirmation_message.html"
);


//------------- CONSTANTE DE ARCHIVOS INTERNOS

/** Valor maximo permitido de carga de $_POST */
define( 'POST_FILE_MAX_SIZE', 41943040 );

/** Numero de filas a mostrar por pagina del index en detalle usuario -> userSetting.js */
define( 'ROWS_PER_PAGE',10); 

// tiempo de expiración de 24 horas
define( 'FIRST_LOGIN_EXPIRATION_TIME', time() + (3600 * 24) ); 

// tiempo de expiración de 24 horas
define( 'EMAIL_ACTIVATION_EXPIRATION_TIME', time() + (3600 * 24) ); 

// tiempo de expiración de 24 horas
define( 'EMAIL_RECOVER_PASSWORD_EXPIRATION_TIME', time() + (3600 * 24) ); 

// El token vence dentro de 24 horas (1 día)
define( 'TOKEN_BEARER_SINGLE_EXPIRATION_TIME', time() + (3600 * 24) ); 

// El token vence dentro de 180 dias (6 meses)
define( 'TOKEN_BEARER_LARGE_EXPIRATION_TIME', time() + (3600 * 24 * 180) ); 

//------------- CONSTANTES DE RUTAS PUBLICAS

define( 'DOMAIN_URL', $_SERVER['HTTP_HOST'] );

define(
    'EMAIL_CONFIRM_ACCOUNT_ROUTE', 
    "https://sumate.com.do/email-confirm/"
);

define(
    'EMAIL_TO_RESTORE_PASSWORD_ROUTE', 
    "https://sumate.com.do/change-password/"
);

//------------- CONSTANTE DE ARCHIVOS PUBLICOS


