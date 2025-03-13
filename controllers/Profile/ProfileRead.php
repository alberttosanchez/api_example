<?php // target : profile-read

    // incluimos el middleware para validar el username y el token encryptado
    
    require_once('./controllers/controllers.middleware.php');

    /** Procedemos a obtener los datos del perfil */

    $result = $ManageDB->get_table_single_row(
        $table_name = "smtapi_users_profile", 
        $where_conditions = ["id_user" => $user_id]
    );

    if (!isset($result['fetched'][0]['id_user']) && (int)$user_id != (int)$result['fetched'][0]['id_user'] ) {

        $error_code = '409-01';

        $msg = 'Error 409. Contacte al administrador de sistemas.';

        $message = arr_multi_lang($msg); 

        on_exception_server_response(409,$message,$target,$error_code);
        die();
    }

    $response = $result['fetched'][0];

    //sanitizamos las cadenas de respuesta
    foreach ($response as $key => $value) {
        
        if (is_string($value)){
            $response[$key] = html_entity_decode($value);
        }

    }

    // obtenemos la url de la foto de perfil y la agregamos a la respuesta
    $array_thumbnail_path = (array)json_decode($response['thumbnail_path']);
    $profile_public_photo_url = $array_thumbnail_path['public_url'] . $array_thumbnail_path['filename'];

    // obtenemos las redes sociales
    $array_social_networks = (array)json_decode($response['social_networks']);
    $sn_facebook    = isset($array_social_networks['facebook'])     ? $array_social_networks['facebook']    : "";
    $sn_twitter     = isset($array_social_networks['twitter'])      ? $array_social_networks['twitter']     : "";
    $sn_instagram   = isset($array_social_networks['instagram'])    ? $array_social_networks['instagram']   : "";
    $sn_youtube     = isset($array_social_networks['youtube'])      ? $array_social_networks['youtube']     : "";
    $sn_tiktok      = isset($array_social_networks['tiktok'])       ? $array_social_networks['tiktok']      : "";

    // obtenemos el json del campo profile_options y lo convertimos en array
    $array_profile_options = json_decode($response['profile_options'],true);

    // obtenemos las propiedades del objeto profile_options convertido en array    
    $profile_options    = isset($array_profile_options['profile_options']) ? (array)$array_profile_options['profile_options'] : "";
    $hidde_description  = isset($profile_options['show_description'])  ? !$profile_options['show_description']  : "";
    $hidde_public_email = isset($profile_options['show_public_email']) ? !$profile_options['show_public_email'] : "";
    $hidde_map_location = isset($profile_options['show_map_location']) ? !$profile_options['show_map_location'] : "";

    // eliminamos del array las entradas no deseadas
    // copiamos el array para utilizarlo como contador
    $arr = $response;

    // removemos los datos indexados no asociativos
    for ($u=0; $u < (count($arr)/2) ; $u++) {       
        unset($response[$u]);
    }

    // removemos del resultado los campos que no requemos mostrar en la respuesta
    for ($i=0; $i < count($arr); $i++) {
        
        //unset($result['fetched'][$i][1]);
        unset($response['created_at']);        
        unset($response['updated_at']);        
        unset($response['id_state']);
        unset($response['id']);
        unset($response['thumbnail_path']);
        unset($response['social_networks']);
        unset($response['profile_options']);
    }    

    $response['profile_public_photo_url']   = $profile_public_photo_url;
    $response['facebook']                   = $sn_facebook;
    $response['twitter']                    = $sn_twitter;
    $response['instagram']                  = $sn_instagram;
    $response['youtube']                    = $sn_youtube;
    $response['tiktok']                     = $sn_tiktok;

    $response['hidde_description']          = $hidde_description;
    $response['hidde_public_email']         = $hidde_public_email;
    $response['hidde_map_location']         = $hidde_map_location;
        


    /** Si no hubo error devolvemos una respuesta satisfactoria */

    $msg = 'Perfil recuperado.';

    $message = arr_multi_lang($msg); 

    $response = [
        'data'      => $response,
        'error_code'=> '0',
        'message'   => $message,
        'status'    => '200',
        'target'    => $target,
    ];

    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;