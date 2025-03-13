<?php

namespace Library\Classes;

/** 
 * Clase ValidateForms
 * 
 */
class ValidateForms {
    
    /*
     * Verifica si es un objeto FormData
     * 
     * @param array $formData 
     * @return bool
     *
    /*private function isformDataInstance($formData){
        
        if ($formData instanceof FormData){
            return true;
        }

        return false;            
    }*/

    /**
     * Devuelve un array con los nombre de los campos dados
     * 
     * @param array $array
     * 
     * @return array Con nombre de los campos o array[]
     */
    private function getFieldNamesFromObjectInArray($array){

        
        return $array;
        /*if ( is_array($array) )
        {
            $fieldNames=[]; 
            for ($i=0; $i < count($array) ; $i++) { 
                $fieldNames[$i] = $array[$i];
                if ($i+1 == count($array) )
                {                    
                    return $fieldNames;
                }
            }
            die();    
        }
        return [];*/
    }

    private function getFieldNamesFromFormDataInArray($formData){
        
        $fieldNames = []; $counter = 0;
        for ($i=0; $i < count($formData) ; $i++) { 
            $fieldNames[$counter] = $formData[0];
            $counter++;
            
        }
        
        return $fieldNames;
               
    }
    
    private function getFieldValuesFromFormDataInArray($formData){
       
        $fieldValues = []; $counter = 0;
        for ($i=0; $i < count($formData) ; $i++) { 
            $fieldValues[$counter] = $formData[1];
            $counter++;
        }
        return $fieldValues;

    }

    /**
     * Recorre el objeto Form y devuelve un array con los pares de valores
     * 
     * @param array $formData 
     * 
     * @return array    Con pares de valores o array[]
     */
    private function getFieldNamesAndValuesFromFormDataInArray($formData){
        
        $fieldNamesAndValues=[]; $counter = 0;
        foreach ($formData as $assoc_key => $value) {
            $fieldNamesAndValues[$counter] = [
                $assoc_key    => $value
            ];
            $counter++;            
        }
        return $fieldNamesAndValues;        

    }

    // Data Type Methods

    private function filterDataType($data_type){
        
        switch ($data_type) {
            /* english validation type */                
            case 'str':         return 'string';
            case 'string':      return 'string';
            case 'char':        return 'string';
            case 'text':        return 'string';
            case 'varchar':     return 'string';
            case 'date':        return 'string';
            case 'timestamp':   return 'string';
            case 'blob':        return 'string';

            case 'int':         return 'integer';                
            case 'integer':     return 'integer';
            case 'double':      return 'double';
            case 'float':       return 'double';
            case 'long':        return 'double';

            case 'bint':        return 'bigint';
            case 'obj':         return 'object';
            case 'bool':        return 'boolean';
            case 'sym':         return 'symbol';
            case 'und':         return 'undefined';
            
            case 'email':       return 'string';


            //case 'number':    return 'number';
            //case 'bigint':    return 'bigint';
            //case 'object':    return 'object';
            //case 'boolean':   return 'boolean';
            //case 'symbol':    return 'symbol';
            //case 'undefined': return 'undefined';
          
            /* spanish validation type */
            case 'entero':          return 'integer';
            case 'numero':          return 'integer';
            case 'doble':           return 'double';
            case 'punto_decimal':   return 'double';
            case 'num':             return 'integer';
            case 'flotante':        return 'double';
            case 'num_gde':         return 'integer';
            case 'numero_grande':   return 'integer';
            case 'cadena':          return 'string';
            case 'caracter':        return 'string';
            case 'boleano':         return 'boolean';
            case 'indefinido':      return 'undefined';
            case 'correo':          return 'string';

            default:
                break;
        }

        return $data_type;
    }

    private function IsValidDataType($data_type,$value_from_field){        
        
        if ( gettype($value_from_field) == $data_type )
        {
            return true;
        }
        return false;
    }
    
    // String methods 
    
    private function ValidateLength($value_to_eval,$rule_value){

        $result = [
            'length' => false,
        ];

        if ( strlen(value_to_eval) == $rule_value )
        {
            $result['length'] = true; 
        }            
        
        return $result;
    }

    private function ValidateMaxLength($value_to_eval,$rule_value){

        $result = [
            'max_length' => false,
        ];

        if ( strlen($value_to_eval) <= $rule_value )
        {
            $result['max_length'] = true; 
        }            
        
        return $result;
    }

    private function ValidateMinLength($value_to_eval,$rule_value){

        $result = [
            'min_length' => false,
        ];

        if ( strlen($value_to_eval) >= $rule_value )
        {
            $result['min_length'] = true; 
        }            
        
        return $result;
    }

    private function ValidateRequired($value_from_field){
        
        $result = [
            'required' => false,
        ];        
        if ( gettype($value_from_field) !== NULL && strlen((string)$value_from_field) > 0 
             && !empty($value_from_field) )
        {
            $result['required'] = true;
        }
        
        return $result;
    }

    private function ValidateMinYearsOldDate($value_from_field,$rule_value){
        $result = [
            'min_years_old' => false,
        ];
        
        $currentDate = date('Y-m-d');

        $compare_date = strtotime($value_from_field);
        
        $current_year =  date('Y',strtotime($currentDate));
        $compare_year = date('Y',strtotime($compare_date));
        
        if ( ($current_year - $compare_year) >= $rule_value )
        {
            $result['min_years_old'] = true;
        }
        
        return $result;
    }

    private function ValidateRegex($value_from_field,$rule_value){
        
        /**
         * para validar correo electronico que solo acepte letras, numero guiones y guiones bajos
         * ex. mi-dominio_223@corre1-2_3.edu.do
         * $regex = '/[a-z\d\.\-\_]{3,}@{1}[a-z\d\-\_]{3,}[\.]{1}[a-z]{2,3}[\.]{0,1}[a-z]{0,2}/i';
         * */

        $result = [
            'regex' => false,
        ];
        
        if ( preg_match($rule_value, $value_from_field) > 0 )
        {
            $result['regex'] = true;
        }
        
        return $result;
    }
    
    private function stringRulesValidation($value_from_field,$array_with_string_rules){
        
        $array_with_rule_results=[
            'result' => []
        ]; 
        
        $rules=[];
        foreach ($array_with_string_rules as $key => $rules) {        
        
            $rules = explode(":",$rules);
                
            switch ($rules[0]) {
                case 'length':                        
                    $array_with_rule_results['result'][$key] = $this->ValidateLength($value_from_field,$rules[1]);
                    break;
                case 'max-length':
                    $array_with_rule_results['result'][$key] = $this->ValidateMaxLength($value_from_field,$rules[1]);
                    break;
                case 'min-length':                        
                    $array_with_rule_results['result'][$key] = $this->ValidateMinLength($value_from_field,$rules[1]);
                    break;
                case 'required':                        
                    $array_with_rule_results['result'][$key] = $this->ValidateRequired($value_from_field);
                    break;
                case 'regex':                        
                    $array_with_rule_results['result'][$key] = $this->ValidateRegex($value_from_field,$rules[1]);
                    break;
                case 'max-years-old':                        
                    //$array_with_rule_results['result'][$key] = $this->ValidateMaxDate($value_from_field,$rules[1]);
                    break;
                case 'min-years-old':                        
                    $array_with_rule_results['result'][$key] = $this->ValidateMinYearsOldDate($value_from_field,$rules[1]);
                    break;
                case 'years-old':                        
                    //$array_with_rule_results['result'][$key] = $this->ValidateLastYearDate($value_from_field,$rules[1]);
                    break;
                    
                default:
                    break;
            }
            
        };

        
        return $array_with_rule_results;


    }

    private function numberRulesValidation($value_from_field,$array_with_string_rules){
        
        $object_with_rule_results =[
            'result' => []
        ]; 
        $rules = [];
        foreach ($array_with_string_rules as $key => $rules) {        
            
            $rules = explode(":",$rules);           
            
            switch ($rules[0]) {
                case 'length':                        
                    $object_with_rule_results['result'][$key] = $this->ValidateLength($value_from_field,$rules[1]);
                    break;
                case 'max-length':
                    $object_with_rule_results['result'][$key] = $this->ValidateMaxLength($value_from_field,$rules[1]);
                    break;
                case 'min-length':                        
                    $object_with_rule_results['result'][$key] = $this->ValidateMinLength($value_from_field,$rules[1]);
                    break;
                case 'required':                        
                    $object_with_rule_results['result'][$key] = $this->ValidateRequired($value_from_field);                                        
                    break;
                default:
                    break;
            }
            
        };
        
        return $object_with_rule_results;


    }    

    /**
     * Comprueba que la regla enviada por cadena sea una regla valida
     * y valida segun el caso
     * @param {*} value_from_field 
     * @param {string} data_type 
     * @param {array} array_with_rules 
     * @returns retorna la validacion o false
     */
    private function validateDataRules($value_from_field,$data_type,$array_with_rules){
        
        /* array_with_validate_rules = {
            names                   : "string|min-length:3",
            last_name_one           : "string|min-length:3",
            last_name_two           : "string|min-length:3",
            gender_id               : "number|required",
            birth_date              : "date|length:10|min-years-old:18",
            arch_photo           : "blob|required",
            id_code                 : "number|required",
            id_type                 : "number|required",
            id_issue_entity         : "number|required",
            nationality_id          : "number|required",
            issue_date              : "string|length:10",
            expire_date             : "string|length:10",
            country_of_residency_id : "number|required",
            estate_id               : "number|required",
            city_id                 : "number|required",
            address_one             : "string|required",
            //address_two             : "",
            //zip_code                : "",
            //movil_phone             : "",
            //home_phone              : "",
        }; */

        $result = false;
        switch ($data_type) {            
          
            case 'string':
                $result = $this->stringRulesValidation($value_from_field,$array_with_rules);                    
                break;
            case 'integer':    
                $result = $this->numberRulesValidation($value_from_field,$array_with_rules);  
                break;
            case 'email':    
                $result = $this->emailRulesValidation($value_from_field,$array_with_rules);  
                break;
            case 'object':    
                return 'object';
                break;
            case 'boolean':   
                return 'boolean';
                break;
            case 'symbol':    
                return 'symbol';
                break;
            case 'undefined': 
                return 'undefined';                            
                break;
            default:
                $result = false;
                break;
      };
      
      if (is_array($result) ){
          $result['field_value'] = $value_from_field;
      }      
      return $result;
    }

    /**
     * Valida si el nombre del campo es valido
     * 
     * @param string $value_from_field
     * @param array $array_with_rules
     * 
     * @return array
     */
    private function isValidFieldData($value_from_field,$array_with_rules){
        
        if (strlen($value_from_field) > 0)
        {
            if ( count($array_with_rules) > 0)
            {            
                    $data_type = (string)$array_with_rules[0];
                    $data_type = $this->filterDataType($data_type);
                    
                if ( $this->IsValidDataType($data_type,$value_from_field) )
                {
                   
                    $array_with_string_rules = []; $counter = 0;

                    foreach ($array_with_rules as $key => $rule) {
                        if ($counter > 0)
                        {
                            $array_with_string_rules[$counter-1] =  $rule;
                        }
                        $counter++;
                    }                    
                    
                    if ( count($array_with_rules) > 0)
                    {
                        $result = $this->validateDataRules($value_from_field,$data_type,$array_with_string_rules);
                        
                        return $result;
                    }
                }

            }
        }
        else
        {
            $response = [
                'field_name'    => "",
                'field_value'   => $value_from_field,
                'status'        => "failed",
                'result'        => false
            ];
            return $response;
        }

    }

    /**
     * Valida los campos segun las reglas enviadas como cadena
     * 
     * @param array $array_form_pair 
     * @param string $string_with_validate_rules 
     * 
     * @return array Con los nombre de los campos
     */
    private function validateRuleStringFromField($array_form_pair,$string_with_validate_rules){

        if ( isset($array_form_pair) && (count($array_form_pair) > 0) && strlen($string_with_validate_rules) > 0){
            
            foreach ($array_form_pair as $assoc_key => $pair_value) {                
                $pair_name = $assoc_key;
                $pair_value = $pair_value;
            }

            $array_with_rules = explode("|", $string_with_validate_rules );
             
            $result = $this->isValidFieldData($pair_value,$array_with_rules);
            
            if ( is_array($result) ){
                $result['field_name'] = $pair_name;
            }
            
            return $result;
        }
        return false;            
    }

    /**
     * Matchea los campos a validar con los campos enviados
     * 
     * @param array $formData 
     * @param array $array_with_validate_rules 
     * 
     * @return array devuelve un array con los nombre de las reglas validadas y su estado true o false
     */
    private function formMatchWithRules($formData,$array_with_validate_rules) {
        // si es una instancia de new Form()
        if ( isset($formData) && is_array($formData) && (count($formData) > 0) )
        {

            $form_fields_value_pairs = $this->getFieldNamesAndValuesFromFormDataInArray($formData);
            $fields_name_to_validate = $this->getFieldNamesFromObjectInArray($array_with_validate_rules);

            
            $result = []; $i=0;
            
            // recorremos el array con los los pares de valores
            foreach ($form_fields_value_pairs as $key => $array_pair) {

                foreach ($array_pair as $assoc_key => $pair_value) {
                
                    // recorremos el array con los nombre de los campos
                    foreach ($fields_name_to_validate as $field_name => $value) {
                        
                        // si los nombres coinciden
                        //if ( array_search($field_name, $form_fields_value_pairs) > -1)
                        if ( $assoc_key == $field_name )
                        {
                                
                                $result[$i] = $this->validateRuleStringFromField($array_pair,$array_with_validate_rules[$field_name]);
                                $i++;                        
                        }
                    }
                }
            }
           
            /* result = [
                [{ length : false, }],
                [{ max_length : false, }],
                [{ min_length : false, }]
            ]; */
            //-----------------------
            return $result;

        }

        return false;

    }

    /**
     * 
     * @param array $form                           Array con los datos a validar 
     * @param array $array_with_validate_rules      Array con las entradas del formulario y las reglas de validacion.
     * @param array $array_with_custom_messages     Array con los mensajes a mostrar si encuentra una excepcion. Un mensaje por cada entrada del objeto Form [opcional],
     *                                              Si no se define un mensaje por cada entrada devolvera un mensaje por defecto.
     * 
     * @return array    Devuelve un array con la respuesta del campo evaluado.
     */
    public function validateFormFields($form, $array_with_validate_rules, $array_with_custom_messages = []){
        
        $response = [
           'result' => $this->formMatchWithRules($form,$array_with_validate_rules),
           'status' => "failed",
           'field_name' => ""
        ];
        
        $catched = false;

        foreach ($response['result'] as $index => $value) {
            
            // Esta comprobacion es debido al tiempo de ejecucion.
            // para evitar asignacion de la siguiente iteracion.
            if ( $catched == false )
            {
                $response['message'] = $array_with_custom_messages[$index];
                $response['field_name'] = $response['result'][$index]['field_name'];
            }
            // si hubo error (false)
            if ( $response['result'][$index]['result'] == false )
            {
                $catched = true;                    
            }
            // si hubo un error (true)
            else if ( is_array($response['result'][$index]) )
            { 
                foreach ($response['result'][$index]['result'] as $key => $array_pair) {

                    foreach ($array_pair as $assockey => $value) {

                        if ( $value == false && $catched == false )
                        {
                            $catched = true;
                        }
                        
                    }
                }
                
            }  
        }

        // si hubo un error catched=true devuelve el objeto con la respuesta
        if ( $catched != true ){
            $response['status'] = "success";
            $response['message'] = "Formulario Validado Correctamente";
        }
        
        return $response;

    }

}