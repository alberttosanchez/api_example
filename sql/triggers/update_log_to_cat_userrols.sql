#-- eliminar trigger
-- DROP TRIGGER update_log_to_users_profile;
#-- crear un trigger que lleve un log de insercion de la tabla smtapi_users_login_info.
DELIMITER //
CREATE TRIGGER update_log_to_cat_userrols 
AFTER UPDATE ON smtapi_cat_userrols 
# se indica el momento (after=despues)de la ejecucion y en que evento (insert) ocurrira  
FOR EACH ROW # se ejecutara por cada fila afectada
	BEGIN
		INSERT INTO smtapi_log_for_users_tables (
			table_name,
            user_id,
			action_done,
            data_before_action,
            log_description
            )
			VALUES (
            'smtapi_cat_userrols',
            0, -- usuario no registrado en las tablas de categoria
            'update',            
            concat(
				(select current_user()), -- devuelve el nombre de usuario de base de datos
				old.role_name,'|',
				old.role_iso,'|',
				old.role_description,'|',				
                old.id_state),
			'accion de actualizacion'
		);    
	END //
DELIMITER ;