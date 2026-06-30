

CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    numero_seleccionado VARCHAR(255) NOT NULL,
    comentario TEXT NULL,
    estado ENUM('reservado','vendido','bloqueado','disponible') NOT NULL DEFAULT 'reservado',
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_numero (numero_seleccionado),
    INDEX idx_estado (estado),
    INDEX idx_correo (correo),
    INDEX idx_telefono (telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS numeros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero CHAR(2) NOT NULL UNIQUE,
    estado ENUM('disponible','reservado','vendido','bloqueado') NOT NULL DEFAULT 'disponible',
    participante_id INT NULL,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_numeros_participante
        FOREIGN KEY (participante_id) REFERENCES participantes(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO numeros (numero, estado) VALUES
('00','disponible'),('01','disponible'),('02','disponible'),('03','disponible'),('04','disponible'),
('05','disponible'),('06','disponible'),('07','disponible'),('08','disponible'),('09','disponible'),
('10','disponible'),('11','disponible'),('12','disponible'),('13','disponible'),('14','disponible'),
('15','disponible'),('16','disponible'),('17','disponible'),('18','disponible'),('19','disponible'),
('20','disponible'),('21','disponible'),('22','disponible'),('23','disponible'),('24','disponible'),
('25','disponible'),('26','disponible'),('27','disponible'),('28','disponible'),('29','disponible'),
('30','disponible'),('31','disponible'),('32','disponible'),('33','disponible'),('34','disponible'),
('35','disponible'),('36','disponible'),('37','disponible'),('38','disponible'),('39','disponible'),
('40','disponible'),('41','disponible'),('42','disponible'),('43','disponible'),('44','disponible'),
('45','disponible'),('46','disponible'),('47','disponible'),('48','disponible'),('49','disponible'),
('50','disponible'),('51','disponible'),('52','disponible'),('53','disponible'),('54','disponible'),
('55','disponible'),('56','disponible'),('57','disponible'),('58','disponible'),('59','disponible'),
('60','disponible'),('61','disponible'),('62','disponible'),('63','disponible'),('64','disponible'),
('65','disponible'),('66','disponible'),('67','disponible'),('68','disponible'),('69','disponible'),
('70','disponible'),('71','disponible'),('72','disponible'),('73','disponible'),('74','disponible'),
('75','disponible'),('76','disponible'),('77','disponible'),('78','disponible'),('79','disponible'),
('80','disponible'),('81','disponible'),('82','disponible'),('83','disponible'),('84','disponible'),
('85','disponible'),('86','disponible'),('87','disponible'),('88','disponible'),('89','disponible'),
('90','disponible'),('91','disponible'),('92','disponible'),('93','disponible'),('94','disponible'),
('95','disponible'),('96','disponible'),('97','disponible'),('98','disponible'),('99','disponible');
