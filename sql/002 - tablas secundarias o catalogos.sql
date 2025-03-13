
# ROLES DEL SISTEMA  (agregada al log)
CREATE TABLE IF NOT EXISTS smtapi_cat_sysrols(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    role_name varchar(50) not null,
    role_description varchar(200) default "",
    role_super int default 0,
    role_white int default 0,
    role_read int default 0,
    role_edit int default 0,
    role_delete int default 0,
    id_state int not null default 1,
    constraint smtapi_catsysroles_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)     
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_sysrols (id,role_name,role_description,role_super,role_white,role_read,role_edit,role_delete) VALUES 
(1,'ADMIN','ADMINISTRADOR - ACCESO TOTAL',1,1,1,1,1), 
(2,'SUPPORT','SOPORTE - ACCESO TOTAL POR DEBAJO DE ADMIN',0,1,1,1,1),
(3,'USER','USUARIO - ACCESO TOTAL TOTAL A SU CUENTA',0,1,1,1,0), 
(4,'VISITOR','VISITANTE - ACCESO DE LECTURA',0,0,1,0,0); 

# ROLES DE USUARIO (agregada al log)
CREATE TABLE IF NOT EXISTS smtapi_cat_userrols(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    role_name varchar(50) not null default "",
    role_iso varchar(50) not null default "",
    role_description varchar(200) default "",        
    id_state int not null default 1,
    constraint smtapi_catroles_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)     
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_userrols (id,role_name,role_iso,role_description) VALUES 
(1,'AGENCY','AGN','ACCESO PREFERIDO A DESCUENTOS'), 
(2,'FINAL_USER','FNU','ACCESO DE USUARIO FINAL'),
(3,'VISITOR','VST','ACCESO SOLO AREA PUBLICA'); 

# GENERO DE USUARIO (agregado al log)
CREATE TABLE IF NOT EXISTS smtapi_cat_users_gender(
	id int not null auto_increment primary key,
    gender varchar(20) default "",
    gender_description varchar(100) default "",
    gender_iso varchar(5) default "",
    id_state int not null default 1,
		constraint smtapi_catusergender_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)    
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_users_gender (id,gender,gender_description,gender_iso) VALUES
(1,'HOMBRE','HOMBRE, MACHO DE UNA ESPECIOE DE MAMIFEROS','MLE'),
(2,'MUJER','HEMBRA DE UNA ESPECIE DE MAMIFEROS','FEM'),
(3,'INDEFINIDO','NO DA DETALLES DE SU SEXO','UNF'),
(4,'OTRO','SU SEXO ES DISTINTO A HOMBRE O MUJER','OTR');

# ESTADO DE SESSION DE USUARIO
CREATE TABLE IF NOT EXISTS smtapi_cat_users_session_state(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    -- session_state bool default true not null,
    session_description varchar(100) default "",
    session_iso varchar(5) default "",
    id_state int not null default 1,
		constraint smtapi_catuexpsession_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)   
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_users_session_state (id,session_description,session_iso) VALUES
(1,'SIGNIFICA QUE LA SESION ESTA ACTIVA.','TRE'),
(2,'SIGNIFICA QUE LA SESION HA EXPIRADO.','FLS');


CREATE TABLE IF NOT EXISTS smtapi_cat_account_state(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    account_status varchar(20) not null,
    account_status_description varchar(150) default "",
    account_status_iso varchar(5) default "",
    id_state int not null default 1,
		constraint smtapi_cataccount_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)     
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_account_state (id,account_status,account_status_description,account_status_iso) VALUES
(1,'ACTIVE','SIGNIFICA QUE LA CUENTA FUNCIONA CON NORMALIDAD.','ACT'),
(2,'INACTIVE','SIGNIFICA QUE LA CUENTA TIENE UN TIEMPO DETERMINADO (DEFINIDO POR EL ADMINISTRADOR) SIN USAR.','INACT'),
(3,'SUSPENDE','SIGNIFICA QUE LA CUENTA VIOLO LAS NORMAS DE USO DE LA APP Y ESTA SUSPENDIDA POR TIEMPO INDETERMINADO.','SPD'),
(4,'BLOCKED','SIGNIFICA QUE LA CUENTA NO PUEDE VOLVER A USARSE.','BLK');

create table if not exists smtapi_cat_ident_type (
    id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    ident_type varchar(50) default "",
    ident_type_iso varchar(10) default "",
    id_state int not null default 1,
		constraint identtype_datastate
		foreign key (id_state)
		references smtapi_cat_data_state(id)    
) engine InnoDB, auto_increment = 1;

INSERT INTO smtapi_cat_ident_type (ident_type,ident_type_iso) VALUES
('CEDULA','CDL'),
('PASAPORTE','PPT'),
('RNC','RNC');

create table if not exists smtapi_cat_civil_state(
	id int not null auto_increment,
	civil_state varchar(50) not null unique,
    civil_state_iso varchar(10) default '' unique,
    civil_state_description varchar(100) default '',
    id_state int not null default 2,
		constraint smtapi_civilstatus_status
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade,  
    primary key (id)
) engine InnoDB auto_increment=1;

INSERT INTO smtapi_cat_civil_state (id,civil_state,civil_state_iso,civil_state_description) VALUES
(1,'SOLTERO(A)','STR',''),
(2,'CASADO(A)','CSD',''),
(3,'DIVORCIADO(A)','DVC',''),
(4,'UNION LIBRE', 'ULB',''),
(5,'VIUDO(A)', 'VUD','');

# PAISES DEL MUNDO
create table if not exists smtapi_cat_countries (
	id int NOT NULL AUTO_INCREMENT,
	country_iso varchar(2) DEFAULT NULL,
	country_name varchar(80) DEFAULT NULL,
    id_state int not null default 2, -- solo lectura
		constraint smtapi_countries_status
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade,
	PRIMARY KEY (id)
) engine InnoDB auto_increment=1;


# tabla zonas regionales (para provincias que esta agrupadas por esta categoria)
create table if not exists smtapi_cat_zones(
	id int not null auto_increment,
    id_country int not null default 65, -- Republica Dominicana
		constraint smtapi_zones_country
        foreign key (id_country)
        references smtapi_cat_countries(id),        
    zone_name varchar(50) not null unique,
    zone_iso varchar(10) default '' unique,
    zone_descripcion varchar(100) default '',
    id_state int not null default 2,
		constraint smtapi_zones_status
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade,
    primary key (id)
) engine InnoDB auto_increment=1;


# tabla provincias
create table if not exists smtapi_cat_provinces(
	id int not null auto_increment,
    id_country int not null,
		constraint smtapi_statecountry
        foreign key (id_country)
        references smtapi_cat_countries(id)
        on delete cascade,
	id_zone int not null,
		constraint smtapi_provincezones
        foreign key (id_zone)
        references smtapi_cat_zones(id)
        on delete cascade,
    province_name varchar(50) not null unique,
    province_iso varchar(10) default '' unique,
    province_descripcion varchar(100) default '',
    id_state int not null default 2,
		constraint smtapi_provinces_state
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade,
    primary key (id)
) engine InnoDB auto_increment=1;

# tabla ciudades
create table if not exists smtapi_cat_cities(
	id int not null auto_increment,
    id_province int not null,
		constraint smtapi_provincecities
        foreign key (id_province)
        references smtapi_cat_provinces(id),	
    city_name varchar(50) not null unique,
    city_iso varchar(10) default '' unique,
    city_descripcion varchar(100) default '',
    id_state int not null default 2,
		constraint smtapi_cities_state
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade,    
    primary key (id)
) engine InnoDB auto_increment=1;
