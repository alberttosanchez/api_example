# agregado al log
CREATE TABLE IF NOT EXISTS smtapi_users_security_data (
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_security_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,
	id_sysrol int not null default 3, -- fk -> 3 rol de usuario por defecto
		constraint smtapi_security_cat_sysrol
        foreign key (id_sysrol)
        references smtapi_cat_sysrols(id),
	id_userrol int not null default 2, -- fk -> 2 usuario final
		constraint smtapi_security_cat_roles
        foreign key (id_userrol)
        references smtapi_cat_userrols(id),
	email_confirmed int not null default 0,
    phone_confirmed int not null default 0,
    two_factory_actived int not null default 0,        
    id_account_state int not null default 1, -- fk --> 1 : active
		constraint smtapi_security_cat_account_state
        foreign key (id_account_state)
        references smtapi_cat_account_state(id),
    token varchar(32) default "", -- recibe un token md5 de 32 caracteres
    token_bearer text, -- default "", -- recibe un token md5 de 32 caracteres
    secret_key_bearer varchar(2056), -- text default "",
    id_state int not null default 1,
		constraint smtapi_usdata_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)     
) engine InnoDB, auto_increment = 1;

# CUENTA DE USUARIO # agregado al log
CREATE TABLE IF NOT EXISTS smtapi_users_account(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_account_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,    
	first_name varchar(50) default "",
    last_name varchar(50) default "",
    birth_date varchar(10) default "",    
    ident_goverment_dni varchar(11) not null default "", -- cedula o pasaporte
    id_civil_state int not null default 1, -- soltero
		constraint smtapi_civilstateaccount
        foreign key (id_civil_state)
        references smtapi_cat_civil_state(id),
    id_ident_type int default 1, -- 1: cedula 2: pasaporte
		constraint smtapi_accnt_idtype_id
        foreign key (id_ident_type)
        references smtapi_cat_ident_type(id),
    id_gender int default 3, -- 3:indefinido
		constraint smtapi_account_gender
        foreign key (id_gender)
        references smtapi_cat_users_gender(id),
	documents_path varchar(2056) default "{}",
    id_state int not null default 1,
		constraint smtapi_accnt_datastate
        foreign key (id_state)
        references smtapi_cat_data_state(id)    
) engine InnoDB, auto_increment = 1;

# PERFIL DE USUARIO # agregado al log
CREATE TABLE IF NOT EXISTS smtapi_users_profile(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_profile_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,
    agency_name varchar(200) default "",
    profile_description varchar(600) default "",
    public_email varchar(200) default "",  -- se muestra en el perfil
    public_phone varchar(50) not null default "", -- telefono de contacto    
	thumbnail_path varchar(2056) default "{}",    
	social_networks varchar(2056) default "{}", -- string de objeto json contiene todas las redes sociales del usuario
	id_country int not null default 65, -- Republica Dominicana,
		constraint smtapi_csi_country
		foreign key (id_country)
		references smtapi_cat_countries(id),
    id_zone int not null default 2, -- Este
		constraint smtapi_csi_zone
		foreign key (id_zone)
		references smtapi_cat_zones(id),
    id_province int not null default 5, -- Distrito Nacional
		constraint smtapi_csi_province
		foreign key (id_province)
		references smtapi_cat_provinces(id),
    id_city int not null default 5, -- Santo Domingo    
		constraint smtapi_csi_city
		foreign key (id_city)
		references smtapi_cat_cities(id),
    address_one varchar(100) default "",
    address_two varchar(100) default  "",
    zip_code varchar(20) default "",
    map_location text,    
    profile_options varchar(2056) default '{"profile_options":{"show_description":true,"show_public_email":true,"show_map_location":true,}}',    
	id_state int not null default 1,
		constraint smtapi_usecsess_datastate
		foreign key (id_state)
		references smtapi_cat_data_state(id)   
) engine InnoDB, auto_increment = 1;

# TIPO DE EMAILS
create table if not exists smtapi_cat_email_state(
	id int not null auto_increment primary key,
    email_status_name varchar(50) not null unique,
    email_status_iso varchar(10) default '' unique,
    email_status_description varchar(100) default '',
    id_state int not null default 2,
		constraint smtapi_emailstatus_status
        foreign key (id_state)
        references smtapi_cat_data_state(id)
        on delete cascade    
) engine InnoDB auto_increment=1;

INSERT INTO smtapi_cat_email_state (id,email_status_name,email_status_iso,email_status_description) VALUES
(1,'PRINCIPAL','PNP',''),
(2,'SECUNDARIO','SCD','');

# EMAILS DE USUARIOS
CREATE TABLE IF NOT EXISTS smtapi_users_emails (
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int default 0, -- fk
    user_email varchar(100) not null default "",
    id_email_type int not null default 1, -- primary email
		constraint smtapi_usremails_state
        foreign key (id_email_type)
        references smtapi_cat_email_state(id),
	id_state int not null default 2,
		constraint smtapi_usremail_state
        foreign key (id_state)
        references smtapi_cat_data_state(id)
) engine InnoDB, auto_increment = 1;

# DATOS DE VERIFICACION DEL USUARIO
create table if not exists smtapi_users_verification_data(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_veridata_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,
	email_confirmed int not null default 2, -- no confirmada
    phone_confirmed int not null default 2, -- no confirmada
    dni_confirmed int not null default 2, -- no confirmada
    address_confirmed int not null default 2 -- no confirmada
) engine InnoDB, auto_increment = 1;

# CALIFICACION DEL USUARIO
create table if not exists smtapi_users_rating(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_rating_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,
	rating float default 100 -- calificacion de calcula para 5 estrellas
) engine InnoDB, auto_increment = 1;

# SEGURIDAD DE LA SESION DE USUARIO
CREATE TABLE IF NOT EXISTS smtapi_users_security_session(
	id int not null auto_increment primary key,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update now(),
    id_user int not null, -- fk
		constraint smtapi_secursess_users
        foreign key (id_user)
        references smtapi_users_login_info(id)
        on delete cascade,
	session_token varchar(128) default "",
    id_session_state int default 2, -- 1 para true y 2 para false
		constraint smtapi_securses_state
        foreign key (id_session_state)
        references smtapi_cat_users_session_state(id),
	id_state int not null default 1,
		constraint smtapi_secusess_datastate
		foreign key (id_state)
		references smtapi_cat_data_state(id)        
) engine InnoDB, auto_increment = 1;