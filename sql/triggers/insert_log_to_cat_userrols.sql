#-- eliminar trigger
-- DROP TRIGGER insert_log_to_users_profile;
#-- crear un trigger que lleve un log de insercion de la tabla smtapi_users_login_info.
DELIMITER //
CREATE TRIGGER insert_log_to_cat_userrols
AFTER INSERT ON smtapi_cat_userrols 
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
            'insert',            
            concat(
				(select current_user()), -- devuelve el nombre de usuario de base de datos
				new.role_name,'|',
				new.role_iso,'|',				
				new.role_description,'|',                
                new.id_state),
			'accion de inserccion'
		);    
	END //
DELIMITER ;