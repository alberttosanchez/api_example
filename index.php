<?php 

    /**     
     * Unica entrada a la app
     */

    # Iniciamos la sesion de php.
    session_start();

    # Cargamos la depedencias de composer
    require_once './vendor/autoload.php';
        
    # carga la configuracion
    require_once('./admin/setting.php');

    # carga las funciones
    require_once( BASE_PATH .'/functions.php');
    
    # carga las clases
    foreach ( glob( BASE_PATH . '/src/library/class/*.php') as $filename){ include_once $filename; }

    # carga la api
    require_once( BASE_PATH . '/controllers/index.php');  