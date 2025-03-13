-- create schema if not exists smt_api_db;
-- use smt_api_db;

# ESTADO DE LAS ENTIDADES (FILAS DE LAS TABLAS)
CREATE TABLE IF NOT EXISTS smtapi_cat_data_state(
	id int not null auto_increment primary key,
    data_state varchar(50) not null,
    data_description varchar(200) default "",    
    data_iso varchar(10) default ""
) engine InnoDB, auto_increment = 1;

# no se debe crear un CRUD (Create, Read, Update, Delete) para esta tabla
INSERT INTO smtapi_cat_data_state (data_state,data_description,data_iso) VALUES
('active','La data esta disponible para cualquier uso.','ACT'),
('inactive','La data esta disponible pero no puede ser modificada.','ICT'),
('deleted','La data fue borrada y solo esta disponible para reportes.','DEL'),
('bloqued','La data existe en la base de datos pero no esta disponible.','BLO'),
('unavailable','La data no existe, este estado es para los log del sistema exclusivamente.','UVL');

# INFORMACION DE INICIO DE SESION DE USUARIO (agregada al log)
CREATE TABLE IF NOT EXISTS smtapi_users_login_info (
	id int not null auto_increment primary key,
    id_facebook varchar(255) default "",
    id_google varchar(255) default "",
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    users_name varchar(50) not null unique,
    users_email varchar(100) not null unique,
    -- users_goverment_id varchar(11) not null unique, -- cedula de identidad    
    -- users_phone varchar(12) default "", -- teléfono movil
    users_password varchar(255) not null,
    id_state int not null default 1,    
		constraint smtapi_logininfo_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)    
) engine InnoDB, auto_increment = 1;

# REGISTRO DE CAMBIOS EN TABLAS
CREATE TABLE IF NOT EXISTS smtapi_log_for_users_tables (
	id int not null auto_increment primary key,    
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    table_name varchar(100) not null,
    user_id int not null,    
    action_done varchar(30) not null, -- update or delete
    data_before_action varchar(250) default null,
    log_description varchar(200) default null,
    id_state int not null default 1,    
		constraint smtapi_logforusers_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)   
) engine InnoDB, auto_increment = 1;