<?php
    
    /**
     * Remueves el doble espaciado innesario, ex. "  " -> " "
     * 
     * @param string $tr        Cadena aplicar el doble trim
     * 
     * @return string Cadena depurada
     */
    function trim_double($str)
    {
        return preg_replace('/( ){2,}/u',' ',$str);
    }

    /**     
     *      
     * Recibe una cadena la cual es depurada contra inserción de código y caracteres no permitidos, 
     *           
     * @param string    $str                Cadena a depurar     
     * 
     * @return string   Cadena depurada
     */
    function clean_data($str = "")
    {
        // remueve espacios vacios al principio y al final de una cadena.
        $str = trim($str);
        // neutraliza caracteres especiales.
        $str = htmlspecialchars($str);
        // reemplaza caracteres especiales no permitidos
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $str = strtr( $str, $unwanted_array );

        return $str;
    }

    /**
     * Obtiene la url actual y verifica si esta en el path correcto.
     * Toma en cuenta la posicion omitiendo el dominio
     * 
     * @param string    $url                            Cadena con la url publica, ex. http://midominio.com
     * @param int       $index_position <opcional>      Indica la posicion del index a comparar del array obtenido de la url
     * @param bool      $exact <opcional>               False busca coincidencia en la cadena del path.
     * 
     * @return bool     Retorna true o false
     * 
     */
    function is_path($url, $index_position = 1, $exact = true )
    {   
        // obtiene url actual 
        $uri = $_SERVER['REQUEST_URI'];

        if ($_SERVER['SERVER_NAME'] === "localhost")
        {        
            //echo var_dump(STRING_TO_CUT);
            
            $string_to_cut_lenght = strlen(STRING_TO_CUT);

            $uri = substr($uri,$string_to_cut_lenght);
            
            //echo var_dump($uri);
        }
        
        // explode crea array a partir de una cadena, tomando en cuenta un delimitador
        $array = explode('/',$uri);
        //echo var_dump($array);
        $found = $array[$index_position];
        
        //echo var_dump($found);

        if ( $exact === true && $found === $url )
        {
            return true;
        }
        elseif ($exact === false && (strpos($found,$url) > -1) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Devuelve una cadena con el path sin el dominio.
     * Esta funciona elimina el directorio localhost de la cadena.
     * Si envia un parametro cadena y lo encuentra lo devuelve.
     * 
     * @param string    $string_to_search           Cadena a buscar
     * @param int       $index_position <opcional>  Posicion a devolver
     * @param string    $delimiter                  Por defecto - (es un guion)
     * 
     * @return string   Devuelve el path actual
     */
    function get_path($string_to_search = "", $index_position = null, $delimiter = "-")
    {
        // obtenemos el path sin el dominio
        $current_full_path = str_replace(STRING_TO_CUT,"",$_SERVER['REQUEST_URI']);
        
        // si no hay cadena a buscar
        if ($string_to_search !== "" && strlen($string_to_search) > 0)
        {
            // creamos una array con el delimitador
            $array_from_string_to_search = explode($delimiter,$current_full_path);
            
            // si index_position esta definido devolvemos esa posicion del array
            if ( isset($index_position) && (count($array_from_string_to_search)-1) >= $index_position )
            {
                $found = isset($index_position) ? $array_from_string_to_search[$index_position] : null;

                return $found;
            }
            else if ( count($array_from_string_to_search) > 0 )
            {

                for ($i=0; $i < count($array_from_string_to_search) ; $i++) {
                
                    $delimiter = ( $delimiter !== "-" ) ? $delimiter : "";

                    if ( ($delimiter . $array_from_string_to_search[$i]) === $string_to_search )
                    {                        
                        return $delimiter . $array_from_string_to_search[$i];
                    }

                }

            }
        }

        return $current_full_path;
    }

    /**     
     * Retorna un valor booleano para indicar si es el directorio raiz (home)
     * 
     * @return bool      
     */
    function is_home()
    {
        
        if( get_path() === "/index.php" || get_path() === "/" )
        {
            return true;
        }

        return false;

    }

    /**
     * Generar nu token md5 al azar de 32 caracteres
     * 
     * @return string     Devuelve un token md5
     */    
    function get_security_token()
    {
        $token = mt_rand(10000000,99999999);
        $new_token = md5($token);
        return $new_token;
    }
        
    /**
     * Devuelve el codigo de respuesta del servidor especificado y un json con datos.
     * 
     * @param int    $http_response_code        Codigo que el servidor devolvera en la cabecera de respuesta
     * @param string $message <opcional>        Mensaje que acompaña la respuesta
     * @param bool   $send_result <opcional>    Si es false no devuelve el resultado solo el codigo en la cabecera
     * 
     * @return json  Devuelve un objeto json con las respuestas establecidas.   
     */
    function on_exception_server_response( $http_response_code , $message = [] , $target = "", $error_code = 0, $data = [], $send_result = true)
    {
        if ( is_array($message) && count($message) == 0)
        {
            $msg = "Ejecucion incorrecta.";

            $message = arr_multi_lang($msg);            

        }
        else
        {
            $message['lang_es'] = isset($message['lang_es']) ? $message['lang_es'] : 'Ejecucion incorrecta.';
            $message['lang_en'] = isset($message['lang_en']) ? $message['lang_en'] : 'Incorrect execution.';
            $message['lang_fr'] = isset($message['lang_fr']) ? $message['lang_fr'] : 'Exécution incorrecte.';
        }

        // server codigos : 200, 403 , 409 , etc
        http_response_code($http_response_code);
    
        header('Content-Type: application/json');

        if ( $send_result )
        {
            $result = [
                'data'          => $data,
                'error_code'    => $error_code,
                'message'       => $message,
                'status'        => strval($http_response_code),                
                'target'        => $target,
            ];
            // creamos el objeto json con json_encode (JSON_UNESCAPED_UNICODE evitar que cambie las vocales asentuadas)
            $result = ( count($result) > 0 ) ? json_encode($result,JSON_UNESCAPED_UNICODE) : "";            
            echo $result;

        }

    }
    
    /**
     * Devuelve la extension de un nombre de archivo dado.
     * 
     * @param string $file          Recibe una cadena con el nombre completo de una archivo y su extension.
     * 
     * @return string ex. archivo.txt -> return txt
     */
    function get_file_extension_by_name($file = "")
    {

        $array = explode(".",$file);

        for ($i = 0; $i < count($array); $i++) {
            
            if ( $i === count($array)-1 )
            {
                return $array[$i];
            }
            
        }
        return false;
        
    }

    /**
     * Copia un directorio completo
     * @param string $src directorio origen
     * @param string $dst director a crear
     * @param string $child_folder directorio hijo de dst
     */    
    function recurseCopy($src,$dst, $child_folder='') { 

        $dir = opendir($src); 
        @mkdir($dst);
        if ($child_folder!='') {
            @mkdir($dst.'/'.$child_folder);
    
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . '/' . $file) ) { 
                        recurseCopy($src . '/' . $file,$dst.'/'.$child_folder . '/' . $file); 
                    } 
                    else { 
                        copy($src . '/' . $file, $dst.'/'.$child_folder . '/' . $file); 
                    }  
                } 
            }
        }else{
                // return $cc; 
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . '/' . $file) ) { 
                        recurseCopy($src . '/' . $file,$dst . '/' . $file); 
                    } 
                    else { 
                        copy($src . '/' . $file, $dst . '/' . $file); 
                    }  
                } 
            } 
        }
        
        closedir($dir); 
    }
    
    /**
     * Eliminar un directorio y sus archivos
     * 
     * @param array $path          Array con las ruta del directorio a eliminar.
     * 
     * @return bool Devuelve true si elimina de lo contrario false.
     */
    function remove_dir_and_files($path)
    {

        foreach (glob($path."/*") as $file_or_dir) {

            if ( is_dir($file_or_dir) )
            {
                remove_dir_and_files($file_or_dir);
            }
            else
            {
                if ( unlink($file_or_dir) )
                {
                    //file was deleted!!!                                                                         
                }
                else
                {
                    return false;
                }
            }
            
        }

        //elimina directorio vacio
        @rmdir($path);

        return true;

    }

        // funciones

    /**
     * Esta función recibe una cadena y la traduce al idioma seleccionado
     */
    function _lang($string = "", $lang = LANG ){
        

        if ( isset($_SESSION['LANG_TO_TRASLATE']) || isset($lang) )
        {
            $lang = isset($_SESSION['LANG_TO_TRASLATE']) ? $_SESSION['LANG_TO_TRASLATE'] : $lang;
            //var_dump($_SESSION['LANG_TO_TRASLATE']);
            
            $lang_file = get_lang_file($lang);
            
            if ( $lang_file !== false ){                    
                $string = get_traduction($string, $lang_file );                                
            }
        }        
                
        return $string;
    }

    /**
     * Esta funcion retorna el archivo con las traducciones solicitadas
     */
    function get_lang_file($lang, $default = LANG){

        $lang = isset($lang) ? $lang : $default;
        
        // recorre el directorio de idiomas buscando el idioma solicitado        
        foreach ( glob(  BASE_PATH . API_LANG_DIRECTORY . '/*.lang') as $filename){             
            if ( strpos($filename , $lang . ".lang") > -1){
                return $filename;
            }            
        }

        return false;            

    }

    /**
     * Devuelve la traduccion solicitada si la encuentra, de los contrario la cadena original
     */
    function get_traduction($string, $lang_file )
    {

        // si es un archivo
        if ( is_file($lang_file) )
        {

            $lang_file_opened = fopen( $lang_file , 'r' );
            $lang_traduction = "";

            while( !feof($lang_file_opened) ) {
                
                $getLine = fgets($lang_file_opened);
                
                $array_with_traduccion = explode( "|" , $getLine );
                
                // el array tiene 2 posiciones
                if (count($array_with_traduccion) == 2){

                    foreach ($array_with_traduccion as $key => $value) {  
                        // si las cadenas coinciden                      
                        if ( trim($value) == trim($string) )
                        {
                            fclose($lang_file_opened);
                            // devuelve la traduccion
                            return trim($array_with_traduccion[1]);                            
                        }    
                    }
                }

            }

            fclose($lang_file_opened);
        }

        return $string;
    }

    /**
     * Devuelve un array con varias traducciones, recibe un string
     */
    function arr_multi_lang($msg)
    {
        $message = [
            'lang_es' => _lang($msg,'es_ES'),
            'lang_en' => _lang($msg,'es_EN'),
            'lang_fr' => _lang($msg,'es_FR'),
        ]; 

        return $message;

    }