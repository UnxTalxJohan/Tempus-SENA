drop database if exists app_cide;
CREATE database app_cide;
use app_cide;

CREATE TABLE programa (
    id_prog INT NOT NULL,
    nombre VARCHAR(255),
    version INT(255),
    nivel VARCHAR(255),
    cant_trim VARCHAR(255),
    PRIMARY KEY (id_prog),
    INDEX(id_prog)
) ENGINE=InnoDB;


CREATE TABLE ficha (
    id_fich INT NOT NULL,
    caracterizacion_fich VARCHAR(255),
    cod_prog_fk INT NOT NULL,
    fecha_inic_lec DATE,
    fecha_fin_lec DATE,
    PRIMARY KEY (id_fich),
    INDEX(id_fich),
    INDEX(cod_prog_fk),
    FOREIGN KEY (cod_prog_fk) REFERENCES programa(id_prog) ON DELETE CASCADE ON UPDATE CASCADE
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
    PRIMARY KEY (id_resu),
    INDEX(id_resu),
    INDEX(cod_resu),
    INDEX(cod_comp_fk),
    FOREIGN KEY (cod_comp_fk) REFERENCES competencia(cod_comp) ON DELETE CASCADE ON UPDATE CASCADE
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

CREATE TABLE matrs_ext (
    ID_matrx INT NOT NULL AUTO_INCREMENT,
    cod_prog_fk INT NOT NULL,
    cod_com_fk INT NOT NULL,
    id_resu_fk INT NOT NULL,
    PRIMARY KEY (ID_matrx),
    INDEX(ID_matrx),
    INDEX(cod_prog_fk),
    INDEX(cod_com_fk),
    INDEX(id_resu_fk),
    UNIQUE KEY uniq_matrs_ext (cod_prog_fk, cod_com_fk, id_resu_fk),
    FOREIGN KEY (cod_prog_fk) REFERENCES programa(id_prog) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cod_com_fk) REFERENCES competencia(cod_comp) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_resu_fk) REFERENCES resultado(id_resu) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE vinculacion (
    id_vinculacion INT NOT NULL AUTO_INCREMENT,
    tip_vincul VARCHAR(255),
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
    cc INT NOT NULL,
    id_rol_fk INT NOT NULL, INDEX (id_rol_fk), FOREIGN KEY (id_rol_fk) REFERENCES rol(id_rol) ON DELETE CASCADE ON UPDATE CASCADE,
    correo VARCHAR(255) UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    id_vinculacion_fk INT NULL,
    PRIMARY KEY (cc),
    INDEX(cc),
    INDEX(id_vinculacion_fk),
    FOREIGN KEY (id_vinculacion_fk) REFERENCES vinculacion(id_vinculacion) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sede (
    cod_sede INT NOT NULL AUTO_INCREMENT,
    nom_sede VARCHAR(255),
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
    id_fich_fk INT NOT NULL,
    cc_fk INT NOT NULL,
    cod_amb_fk INT NOT NULL,
    id_resu_fk INT NOT NULL,
    PRIMARY KEY (id_fich_fk, cc_fk, cod_amb_fk, id_resu_fk),
    INDEX(id_fich_fk),
    INDEX(cc_fk),
    INDEX(cod_amb_fk),
    INDEX(id_resu_fk),
    FOREIGN KEY (id_fich_fk) REFERENCES ficha(id_fich) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cc_fk) REFERENCES usuario(cc) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cod_amb_fk) REFERENCES ambiente(cod_amb) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_resu_fk) REFERENCES resultado(id_resu) ON DELETE CASCADE ON UPDATE CASCADE
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

