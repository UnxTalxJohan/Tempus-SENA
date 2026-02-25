drop database if exists app_cide;
CREATE database app_cide;
use app_cide;

CREATE TABLE proyecto (
    id_proy INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cod_proy INT not NULL UNIQUE,
    descrip_proy TEXT,
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    INDEX(id_proy),
    INDEX(cod_proy)
) ENGINE=InnoDB;

CREATE TABLE programa (
    id_prog INT NOT NULL,
    cod_prog INT NOT NULL,
    nombre VARCHAR(255),
    version INT(255),
    nivel VARCHAR(255),
    cant_trim VARCHAR(255),
    fch_sub_prg DATETIME,
    fhc_utl_act_prg DATETIME,
    cod_proy_fk INT NOT NULL,
    PRIMARY KEY (id_prog),
    INDEX(id_prog),
    INDEX(cod_proy_fk),
    CONSTRAINT uniq_prog_proy UNIQUE (cod_prog, cod_proy_fk),
    FOREIGN KEY (cod_proy_fk) REFERENCES proyecto(id_proy) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE ficha (
    id_fich INT NOT NULL,
    caracterizacion_fich VARCHAR(255),
    fecha_inic_lec DATE,
    fecha_fin_lec DATE,
    proy_formativo_enruto TEXT,
    trimestre VARCHAR(255),
    abierta TINYINT NOT NULL DEFAULT 1, -- 1: abierta, 2: cerrada
    CDF TEXT,
    cerr_convenio VARCHAR(255),
    jornada VARCHAR(255),
    cod_proy_fk INT NOT NULL,
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    PRIMARY KEY (id_fich),
    INDEX(id_fich),
    INDEX(cod_proy_fk),
    FOREIGN KEY (cod_proy_fk) REFERENCES proyecto(id_proy) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE horario (
    id_horario INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id_horario),INDEX(id_horario),
    dia VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE hora(
    id_hora INT NOT NULL AUTO_INCREMENT,
    hora_inicio VARCHAR(255),
    hora_fin VARCHAR(255),
    id_fich_fk INT NOT NULL,
    id_horario_fk INT NOT NULL,
    PRIMARY KEY (id_hora),
    INDEX(id_hora),
    INDEX(id_fich_fk),
    INDEX(id_horario_fk),
    FOREIGN KEY (id_fich_fk) REFERENCES ficha(id_fich) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_horario_fk) REFERENCES horario(id_horario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE competencia (
    cod_comp INT NOT NULL,
    nombre VARCHAR(255),
    duracion_hora INT,
    id_prog_fk INT NOT NULL,
    PRIMARY KEY (cod_comp),
    INDEX(cod_comp),
    INDEX(id_prog_fk),
    FOREIGN KEY (id_prog_fk) REFERENCES programa(id_prog) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE resultado (
    id_resu INT NOT NULL AUTO_INCREMENT,
    cod_resu INT NOT NULL,
    nombre VARCHAR(255),
    cod_comp_fk INT NOT NULL,
    id_prog_fk INT NOT NULL,
    PRIMARY KEY (id_resu),
    INDEX(id_resu),
    INDEX(cod_resu),
    INDEX(cod_comp_fk),
    INDEX(id_prog_fk),
    FOREIGN KEY (cod_comp_fk) REFERENCES competencia(cod_comp) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_prog_fk) REFERENCES programa(id_prog) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

create Table duracion (
    id_dura int not NULL AUTO_INCREMENT PRIMARY KEY,
    duracion_hora_max INT,
    duracion_hora_min INT,
    trim_prog INT,
    hora_sema_programar INT,
    hora_trim_programar INT,
    cod_resu_fk INT NOT NULL, INDEX(cod_resu_fk),
    id_prog_fk INT NOT NULL, INDEX(id_prog_fk),
    FOREIGN KEY (cod_resu_fk) REFERENCES resultado(id_resu) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_prog_fk) REFERENCES programa(id_prog) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE vinculacion (
    id_vinculacion INT NOT NULL AUTO_INCREMENT,
    tip_vincul VARCHAR(255),
    nmr_contrato VARCHAR(255),
    nvl_formacion VARCHAR(255),
    pregrado TEXT,
    postgrado TEXT,
    coord_pertenece TEXT,
    modalidad TEXT,
    especialidad TEXT,
    fch_inic_contrato DATE,
    fch_fin_contrato DATE,
    area TEXT,
    estudios TEXT,
    red TEXT,   
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    PRIMARY KEY (id_vinculacion),
    INDEX(id_vinculacion)
) ENGINE=InnoDB;

CREATE TABLE rol (
    id_rol INT NOT NULL AUTO_INCREMENT,
    nombre_rol VARCHAR(255),
    PRIMARY KEY (id_rol),
    INDEX(id_rol)
) ENGINE=InnoDB;

CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    cc INT NULL,
    id_rol_fk INT NOT NULL, INDEX (id_rol_fk), FOREIGN KEY (id_rol_fk) REFERENCES rol(id_rol) ON DELETE CASCADE ON UPDATE CASCADE,
    correo VARCHAR(255) UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL, 
    avatar_path VARCHAR(255) NULL,
    avatar_mime VARCHAR(100) NULL,
    avatar_size INT NULL,
    avatar_uploaded_at DATETIME NULL,
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    id_vinculacion_fk INT NULL,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uniq_cc (cc),
    INDEX(id_vinculacion_fk),
    FOREIGN KEY (id_vinculacion_fk) REFERENCES vinculacion(id_vinculacion) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE notificacion (
    id_noti INT NOT NULL AUTO_INCREMENT,
    cc_usuario_fk INT NULL,
    fch_noti DATE NOT NULL,
    hora_noti TIME NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    estado TINYINT NOT NULL DEFAULT 1, -- 1: no leído, 2: leído
    PRIMARY KEY (id_noti),
    INDEX(id_noti),
    INDEX(cc_usuario_fk),
    FOREIGN KEY (cc_usuario_fk) REFERENCES usuario(cc) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sede (
    cod_sede INT NOT NULL AUTO_INCREMENT,
    nom_sede VARCHAR(255),
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    PRIMARY KEY (cod_sede),
    INDEX(cod_sede)
) ENGINE=InnoDB;


CREATE TABLE ambiente (
    cod_amb INT NOT NULL AUTO_INCREMENT,
    denominacion VARCHAR(255),
    cod_sede_fk INT NOT NULL,
    PRIMARY KEY (cod_amb),
    INDEX(cod_amb),
    INDEX(cod_sede_fk),
    FOREIGN KEY (cod_sede_fk) REFERENCES sede(cod_sede) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE evento (
    id_evento INT NOT NULL AUTO_INCREMENT,
    id_fich_fk INT NOT NULL,
    id_usuario_fk INT NOT NULL,
    cod_sede_fk INT NOT NULL,
    fch_registro DATETIME,
    fch_ult_act DATETIME,
    INDEX(id_evento),
    PRIMARY KEY (id_evento),
    INDEX(id_fich_fk),
    INDEX(id_usuario_fk),
    INDEX(cod_sede_fk),
    FOREIGN KEY (id_fich_fk) REFERENCES ficha(id_fich) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_fk) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cod_sede_fk) REFERENCES sede(cod_sede) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS programa_competencia (
  id_prog_fk INT NOT NULL,
  cod_comp_fk INT NOT NULL,
  PRIMARY KEY (id_prog_fk, cod_comp_fk),
  INDEX (id_prog_fk),
  INDEX (cod_comp_fk)
);
INSERT IGNORE INTO programa_competencia (id_prog_fk, cod_comp_fk)
    SELECT id_prog_fk, cod_comp FROM competencia;

CREATE TRIGGER tr_programa_update_programa
BEFORE UPDATE ON programa
FOR EACH ROW
    -- Evitar UPDATE recursivo sobre la misma tabla.
    -- Simplemente asignamos el valor al registro que se está actualizando.
    SET NEW.fhc_utl_act_prg = NOW();

-- Cuando se actualiza una competencia de un programa
CREATE TRIGGER tr_competencia_update_programa
BEFORE UPDATE ON competencia
FOR EACH ROW
    UPDATE programa
    SET fhc_utl_act_prg = NOW()
    WHERE id_prog = NEW.id_prog_fk;

-- Cuando se actualiza un resultado asociado a un programa
CREATE TRIGGER tr_resultado_update_programa
BEFORE UPDATE ON resultado
FOR EACH ROW
    UPDATE programa
    SET fhc_utl_act_prg = NOW()
    WHERE id_prog = NEW.id_prog_fk
      AND NEW.id_prog_fk IS NOT NULL;

-- Cuando se insertan/actualizan horas (duracion) para un programa
CREATE TRIGGER tr_duracion_insert_programa
BEFORE INSERT ON duracion
FOR EACH ROW
    UPDATE programa
    SET fhc_utl_act_prg = NOW()
    WHERE id_prog = NEW.id_prog_fk;

CREATE TRIGGER tr_duracion_update_programa
BEFORE UPDATE ON duracion
FOR EACH ROW
    UPDATE programa
    SET fhc_utl_act_prg = NOW()
    WHERE id_prog = NEW.id_prog_fk;



INSERT INTO horario (dia) VALUES
('Lunes'),
('Martes'),
('Miércoles'),  
('Jueves'),
('Viernes'),
('Sábado');

INSERT INTO rol (id_rol, nombre_rol) VALUES
(1, 'admin'),
(2, 'contrato'),
(3, 'planta');


INSERT INTO usuario (cc, id_rol_fk, correo, contrasena, nombre)
VALUES (10000, 1, 'admin@gmail.com', 'admin', 'admin');