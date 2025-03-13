<?php // target : posts-read

    // en este caso el token es un password hash
    $password_hash = str_replace('Bearer ', '', $bearer_auth);
        
    if ( ! password_verify(SECRET_KEY_BEARER, $password_hash) ) {        

        on_exception_server_response(403,[],$target);
        die();        
    }
    
    $filename = "./public/uploads/posts_files/posts-example.json";
    
    // si no es un archivo
    if (!is_file($filename)){
        on_exception_server_response(404,[],$target,"not data");
        die();
    }

    $resource = fopen($filename, "r");
    
    $file = ""; 
    while (($line = fgets($resource)) !== false) { 
        $file .= $line; 
    }
    
    fclose($resource); 
    
    $message = "data recuperada";

    $response = [
        'status'        => '200',
        'message'       => $message,        
        'data'          => $file,
        'error_code'    => '0',
    ];

    // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
    $response = ( count($response) > 0 ) ? json_encode($response,JSON_UNESCAPED_UNICODE) : "";

    http_response_code(200);
    echo $response;