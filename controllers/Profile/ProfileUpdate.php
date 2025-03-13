<?php // target : profile-update

    // incluimos el middleware para validar el username y el token encryptado
    
    require_once('./controllers/controllers.middleware.php');

    /** Obtenemos los datos que vamos a actualizar del payload */

    // imagen de perfil
    $base64_image       = isset($jsonObject->profile->base64data)       ? clean_data(trim_double($jsonObject->profile->base64data))         : "";

    $about_textarea     = isset($jsonObject->profile->about_textarea)   ? clean_data(trim_double($jsonObject->profile->about_textarea))     : "";
    $csn_fullname       = isset($jsonObject->profile->csn_fullname)     ? clean_data(trim_double($jsonObject->profile->csn_fullname))       : "";
    $csn_email          = isset($jsonObject->profile->csn_email)        ? clean_data(trim_double($jsonObject->profile->csn_email))          : "";
    $csn_phone          = isset($jsonObject->profile->csn_phone)        ? clean_data(trim_double($jsonObject->profile->csn_phone))          : "";

    $csn_facebook       = isset($jsonObject->profile->csn_facebook)     ? clean_data(trim_double($jsonObject->profile->csn_facebook))       : "";
    $csn_instagram      = isset($jsonObject->profile->csn_instagram)    ? clean_data(trim_double($jsonObject->profile->csn_instagram))      : "";
    $csn_tiktok         = isset($jsonObject->profile->csn_tiktok)       ? clean_data(trim_double($jsonObject->profile->csn_tiktok))         : "";
    $csn_twitter        = isset($jsonObject->profile->csn_twitter)      ? clean_data(trim_double($jsonObject->profile->csn_twitter))        : "";
    $csn_youtube        = isset($jsonObject->profile->csn_youtube)      ? clean_data(trim_double($jsonObject->profile->csn_youtube))        : "";

    $locadd_address1    = isset($jsonObject->profile->locadd_address1)  ? clean_data(trim_double($jsonObject->profile->locadd_address1))    : "";
    $locadd_address2    = isset($jsonObject->profile->locadd_address2)  ? clean_data(trim_double($jsonObject->profile->locadd_address2))    : "";
    $locadd_country     = isset($jsonObject->profile->locadd_country)   ? clean_data(trim_double($jsonObject->profile->locadd_country))     : "";
    $locadd_state       = isset($jsonObject->profile->locadd_state)     ? clean_data(trim_double($jsonObject->profile->locadd_state))       : "";
    $locadd_city        = isset($jsonObject->profile->locadd_city)      ? clean_data(trim_double($jsonObject->profile->locadd_city))        : "";
    $locadd_zipcode     = isset($jsonObject->profile->locadd_zipcode)   ? clean_data(trim_double($jsonObject->profile->locadd_zipcode))     : "";

    $locadd_maps        = isset($jsonObject->profile->locadd_maps)      ? clean_data(trim_double($jsonObject->profile->locadd_maps))        : "";

    $cbx_hidde_about    = isset($jsonObject->profile->cbx_hidde_about)  ? clean_data(trim_double($jsonObject->profile->cbx_hidde_about))    : "";
    $cbx_hidde_email    = isset($jsonObject->profile->cbx_hidde_email)  ? clean_data(trim_double($jsonObject->profile->cbx_hidde_email))    : "";
    $cbx_hidde_map      = isset($jsonObject->profile->cbx_hidde_map)    ? clean_data(trim_double($jsonObject->profile->cbx_hidde_map))      : "";

    $remove_profile_photo = isset($jsonObject->profile->remove_profile_photo)    ? clean_data(trim_double($jsonObject->profile->remove_profile_photo))      : false;

    /** Procemos a converir el base64data en una imagen y la guardamos en el servidor */
    $photo_path_was_changed = false;
    if ( isset($base64_image) && !empty($base64_image) ) {
        
        // formatos admitidos de imagen
        $type = [ 'jpg', 'jpeg', 'png' ];

        // Comprobar el formato de la imagen (PNG, JPG, etc.)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
            $type = strtolower($type[1]); // jpg, png, gif, bmp, etc.
    
            // Eliminar la parte de la cadena base64 para dejar solo los datos
            $base64_image = substr($base64_image, strpos($base64_image, ',') + 1);
    
            // Decodificar la imagen
            $base64_image = base64_decode($base64_image);
    
            // Comprobar si la decodificación fue exitosa                      
            if ( $base64_image === false )
            {
                $error_code = '409-01';

                $msg = 'Error b64. Contacte al administrador de sistemas.';

                $message = arr_multi_lang($msg); 

                on_exception_server_response(409,$message,$target,$error_code);
                die();                
            }
    
            // $user_id esta en controllers.middleware.php (en este caso)

            // Crear un nombre único para la imagen
            $new_empty_filename = $user_id .'-' . uniqid() . '.' . $type;
            
            // Obtener la fecha actual en el formato Y-m-d
            $current_date = date("Y-m-d");

            // Separar la fecha en año, mes y día
            list($anno, $month, $day) = explode("-", $current_date);

            // ruta donde se guarda la foto del perfil del colaborador (coworker)            
            $directory = "." . API_PUBLIC_DIRECTORY . '/uploads' . '/' . $anno .'/' . $month . '/' . $day . '/';

            // ruta publica 
            $public_url =  API_DOMAIN_URL . '/public/uploads/' . $anno .'/' . $month . '/' . $day . '/';

            // Verificar si el directorio existe
            if (!is_dir($directory)) {
                // Intentar crear el directorio
                if (!mkdir($directory, 0777, true)) {

                    $error_code = '409-02';

                    $msg = 'Error 409. Contacte al administrador de sistemas.';
                    //$msg = "Error al crear el directorio: " . error_get_last()['message'] . "dir:" . $directory;
                    $message = arr_multi_lang($msg); 
    
                    on_exception_server_response(409,$message,$target,$error_code);
                    die();
                }                
            }

            // instanciamos la clase ManageDB   
            $Files = new Library\Classes\Files;

            /**
             * mueve un array de archivos a la ubicacion indicada, los renombra de ser necesario
             * Devuelve un array con las rutas de los archivos movidos de los contrario false
             */
            //$result = $Files->recursive_move_uploaded_file($files_path,$assoc_name);
           
            
            // Escribir los datos de la imagen en un archivo
            $result = file_put_contents( $directory . $new_empty_filename, $base64_image );            

            if ($result)
            {
                $json_photo_path = '{"path" : "' . $directory . '", "filename" : "'. $new_empty_filename .'", "public_url" : "'. $public_url .'"}';
                $photo_path_was_changed = true;
            }

           
        } 

    }

    /** Procedemos a eliminar la foto de perfil antigua si existe */

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_profile", 
        $where_conditions = ["id_user" => $user_id]
    );

    if (!isset($result['fetched'][0]['thumbnail_path'])) {

        $error_code = '409-03';

        $msg = 'Error 409. Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    $thumbnail_path = $result['fetched'][0]['thumbnail_path'];

    // procedemos a eliminar la imagen de perfil del servidor    
    $array_photo_path = (array)json_decode($thumbnail_path);

    $photo_path = "";    
    if (isset($array_photo_path['filename']) && strlen($array_photo_path['filename']) > 0 )
    {
        $photo_path = $array_photo_path['path'] . $array_photo_path['filename'];

        // elimina la imagen guardada si hay una imagen blob o una cadena especifica
        if ( isset($base64_image) && !empty($base64_image) || ($remove_profile_photo == true) )
        {
            if( is_file($photo_path) )
            {
                $file_was_deleted = unlink($photo_path);                
                $photo_path_was_changed = true;
            }
        }    

    }        

    /** Procedemos a actulizar el perfil */

    $array_update_profile_data = [                
        //'thumbnail_path'        => "{}",
        'agency_name'           => $csn_fullname, 
        'profile_description'   => $about_textarea,        
        'public_email'          => $csn_email,
        'public_phone'          => $csn_phone,        
        //'id_country'            => $locadd_country,
        //'id_zone'               => $locadd_city,
        //'id_province'           => $locadd_state,
        //'id_city'               => $locadd_city,
        'address_one'           => $locadd_address1,
        'address_two'           => $locadd_address2,
        'zip_code'              => $locadd_zipcode,
        'map_location'          => $locadd_maps,   
        //'social_networks'       => "{}",
        //'profile_options'       => "{}",
    ];
    
    // agregamos el path de la foto de perfil
    if ( (isset($photo_path_was_changed) && $photo_path_was_changed) || $remove_profile_photo == true )
    {
        # Obtenemos el path donde esta guardad la foto
        $photo_path = isset($json_photo_path) ? $json_photo_path  : '{"path" : "", "filename" : "", "public_url":""}';            
        $array_update_profile_data['thumbnail_path'] = $photo_path;
    }

    if ( isset($locadd_country) && !empty($locadd_country) && !is_nan((int)$locadd_country) )
    {
        $array_update_profile_data['id_country'] = $locadd_country;
    }
    if ( isset($locadd_state) && !empty($locadd_state) && !is_nan((int)$locadd_state) )
    {
        $array_update_profile_data['id_province'] = $locadd_state;
    }
    if ( isset($locadd_city) && !empty($locadd_city) && !is_nan((int)$locadd_city) )
    {
        $array_update_profile_data['id_city'] = $locadd_city;
    }

    if ( isset($locadd_city) && empty($locadd_city) )
    {
        $array_update_profile_data['id_city']       = "";
    }
    if ( isset($locadd_state) && empty($locadd_state) )
    {        
        $array_update_profile_data['id_zone']       = "";
        $array_update_profile_data['id_province']   = "";
        $array_update_profile_data['id_city']       = "";
    }
    if ( isset($locadd_country) && empty($locadd_country) )
    {
        $array_update_profile_data['id_country']    = "";
        $array_update_profile_data['id_zone']       = "";
        $array_update_profile_data['id_province']   = "";
        $array_update_profile_data['id_city']       = "";
    }

    if ( isset($locadd_state) && !empty($locadd_state) && !is_nan((int)$locadd_state) )
    {
        // Obtenemos el id_zone de la base de datos
        $result = $ManageDB->get_table_single_row(
            $table_name = "smtapi_cat_provinces", 
            $where_conditions = ["id" => (int)$locadd_state],
        );    
    
        if (!isset($result['fetched'][0]['id_zone'])) {
    
            $error_code = '409-04';
    
            $msg = 'Error 409. Contacte al administrador de sistemas.';
    
            $message = arr_multi_lang($msg); 
    
            on_exception_server_response(409,$message,$target,$error_code);
            die();
        }
    
        $array_update_profile_data['id_zone'] = $result['fetched'][0]['id_zone'];
    }

    //creamos un json con las redes sociales
    $sn_json = [ "social_networks" => [] ];
    if (isset($csn_facebook) && !empty($csn_facebook))
    {
        $sn_json['social_networks']["facebook"] = $csn_facebook;
    }
    if (isset($csn_twitter) && !empty($csn_twitter))
    {
        $sn_json['social_networks']["twitter"] = $csn_twitter;
    }
    if (isset($csn_instagram) && !empty($csn_instagram))
    {
        $sn_json['social_networks']["instagram"] = $csn_instagram;
    }
    if (isset($csn_youtube) && !empty($csn_youtube))
    {
        $sn_json['social_networks']["youtube"] = $csn_youtube;
    }
    if (isset($csn_tiktok) && !empty($csn_tiktok))
    {
        $sn_json['social_networks']["tiktok"] = $csn_tiktok;
    }
        
    $array_update_profile_data['social_networks'] = isset($sn_json["social_networks"]) ? json_encode($sn_json["social_networks"],JSON_UNESCAPED_UNICODE) : "{}";

    // creamos un json con las opciones del perfil
    $profile_opt_json = [ "profile_options" => [] ];
    if ( isset($cbx_hidde_about) )
    {
        $profile_opt_json['profile_options']['show_description'] = (!empty($cbx_hidde_about) ? !(bool)$cbx_hidde_about : true);
    }
    if ( isset($cbx_hidde_email) )
    {
        $profile_opt_json['profile_options']['show_public_email'] = (!empty($cbx_hidde_email) ? !(bool)$cbx_hidde_email : true);
    }
    if ( isset($cbx_hidde_map) )
    {
        $profile_opt_json['profile_options']['show_map_location'] = (!empty($cbx_hidde_map) ? !(bool)$cbx_hidde_map : true);
    }
    
    $array_update_profile_data['profile_options'] = isset($profile_opt_json) ? json_encode($profile_opt_json,JSON_UNESCAPED_UNICODE) : "{}";

    //var_dump($array_update_profile_data); die();

    // procedemos a actualizar en la base de datos
    // si el id de facebook no estaba guardado procedemos a guardarlo
    if (    isset($array_update_profile_data) && 
            is_array($array_update_profile_data) && 
            (count($array_update_profile_data) > 0)
       ) {  
        
        $table_name = 'smtapi_users_profile';
        
        $array_assoc_where = [
            'id_user' => $user_id,            
        ];

        // insertamos los valores en la tabla
        // devulve true si lo inserta
        // updateAll($table_name = "", $fields_array = [] , $where_conditions = [] )
        $result = $ManageDB->updateAll( $table_name , $array_update_profile_data , $array_assoc_where );

        // si no se inserto el registro
        if ( $result == false )
        {
            $error_code = '500-01';

            $msg = 'Contacte al administrador de sistemas.';

            $message = arr_multi_lang($msg);

            on_exception_server_response(500,$message,$target,$error_code);
            die();
        }

    }
    

    /** Si no hubo error devolvemos una respuesta satisfactoria */

    $msg = 'Perfil actualizado.';

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