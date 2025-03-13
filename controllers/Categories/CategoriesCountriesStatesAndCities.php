<?php // categories-countries_states_and_cities

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
        
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        on_exception_server_response(403,[],$target);
        die();        
    }
           
    /* Procedemos a obtener los paises */

    # instanciamos la clase Conexion    
    # $Conexion = new Library\Classes\Conexion; -> ya fue instancia en el archivo api.php
    # $conn = $Conexion->get(DB_CONFIG); -> ya fue asignada en el archivo api.php

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos todos los paises
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_cat_countries',
        $filter = "",
        $keyword = "",            
        $limit = 1000,
        $selected_page = '1',
        $array_fields = false,
        $order_by = 'id',
        $order_dir = 'ASC',
        $filter_between = "",
        $array_between = false,
        $strict_mode = true
    );    

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['country_name']) )
    {
        $error_code = '500-01';

        $msg = 'No se pudieron obtener los paises';

        $message = arr_multi_lang($msg); 
       
        on_exception_server_response(500,$message,$target,$error_code);
        die();
    }
       
    $array_with_all_countries = $result['fetched'];
    
    /* Procedemos a obtener las regiones */

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos todos los paises
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_cat_zones',
        $filter = "",
        $keyword = "",            
        $limit = 1000,
        $selected_page = '1',
        $array_fields = false,
        $order_by = 'id',
        $order_dir = 'ASC',
        $filter_between = "",
        $array_between = false,
        $strict_mode = true
    );    

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['zone_name']) )
    {
        $error_code = '500-02';

        $msg = 'No se pudieron obtener las regiones';

        $message = arr_multi_lang($msg); 
       
        on_exception_server_response(500,$message,$target,$error_code);
        die();
    }
       
    $array_with_all_zones = $result['fetched'];
   
    
    /* Procedemos a obtener los estados / provincias */

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos todos los paises
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_cat_provinces',
        $filter = "",
        $keyword = "",            
        $limit = 100000,
        $selected_page = '1',
        $array_fields = false,
        $order_by = 'id',
        $order_dir = 'ASC',
        $filter_between = "",
        $array_between = false,
        $strict_mode = true
    );    

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['province_name']) )
    {
        $error_code = '500-03';

        $msg = 'No se pudieron obtener las provincias';

        $message = arr_multi_lang($msg); 
       
        on_exception_server_response(500,$message,$target,$error_code);
        die();
    }
       
    $array_with_all_provinces = $result['fetched'];


    /* Procedemos a obtener las ciudades */

    // instanciamos la clase ManageDB
    $ManageDB = new Library\Classes\ManageDB;

    // obtenemos todos los paises
    $result = $ManageDB->get_table_rows(
        $table_name = 'smtapi_cat_cities',
        $filter = "",
        $keyword = "",            
        $limit = 100000,
        $selected_page = '1',
        $array_fields = false,
        $order_by = 'id',
        $order_dir = 'ASC',
        $filter_between = "",
        $array_between = false,
        $strict_mode = true
    );    

    // si el array fetched no esta seteado y el array esta vacio
    if ( ! isset($result['fetched'][0]['city_name']) )
    {
        $error_code = '500-04';

        $msg = 'No se pudieron obtener las ciudades';

        $message = arr_multi_lang($msg); 
       
        on_exception_server_response(500,$message,$target,$error_code);
        die();
    }
       
    $array_with_all_cities = $result['fetched'];


    /** Si no hubo error devolvemos una respuesta satisfactoria */
   
    $msg = 'Datos obtenidos.';

    $message = arr_multi_lang($msg);

    $response = [
        'status'    => '200',
        'message'   => $message,        
        'data'      => [ 
            "countries"   => $array_with_all_countries, // table: smtapi_users_login_info
            "zones"       => $array_with_all_zones,
            "provinces"   => $array_with_all_provinces,
            "cities"      => $array_with_all_cities,            
        ],
        'error_code' => '0',
    ];

    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;