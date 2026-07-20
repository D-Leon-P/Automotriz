-- ========================================================
-- 1. BASE DE DATOS: prospects_db
-- ========================================================
CREATE DATABASE IF NOT EXISTS prospects_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prospects_db;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    KEY idx_empleados_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prospectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    vehiculo_id INT NOT NULL,
    etapa ENUM('prospeccion', 'calificacion', 'negociacion', 'cierre') NOT NULL DEFAULT 'prospeccion',
    empleado_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE RESTRICT,
    KEY idx_prospectos_etapa (etapa),
    KEY idx_prospectos_empleado (empleado_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY idx_jobs_queue (queue)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ========================================================
-- 2. BASE DE DATOS: sales_db
-- ========================================================
CREATE DATABASE IF NOT EXISTS sales_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sales_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prospectos (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    vehiculo_id INT NOT NULL,
    etapa VARCHAR(50) NOT NULL DEFAULT 'prospeccion',
    empleado_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento ENUM('DNI', 'RUC', 'CEX') NOT NULL DEFAULT 'DNI',
    nombre VARCHAR(50) NULL,
    apellido VARCHAR(50) NULL,
    razon_social VARCHAR(150) NULL,
    fecha_nacimiento DATE NULL,
    email VARCHAR(100) NULL UNIQUE,
    telefono VARCHAR(20) NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    direccion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_clientes_documento (documento)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prospecto_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    empleado_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado ENUM('efectiva', 'fallida') NOT NULL,
    motivo_perdida VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (prospecto_id) REFERENCES prospectos(id) ON DELETE RESTRICT,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE RESTRICT,
    KEY idx_ventas_estado (estado),
    KEY idx_ventas_empleado (empleado_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY idx_jobs_queue (queue)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ========================================================
-- 3. BASE DE DATOS: insurance_db
-- ========================================================
CREATE DATABASE IF NOT EXISTS insurance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE insurance_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
    id INT PRIMARY KEY,
    prospecto_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    empleado_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seguros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    tipo_seguro VARCHAR(100) NOT NULL,
    prima_esperada DECIMAL(10, 2) NOT NULL,
    prima_real DECIMAL(10, 2) NULL,
    estado ENUM('prospectado', 'vendido') NOT NULL DEFAULT 'prospectado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
    KEY idx_seguros_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY idx_jobs_queue (queue)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ========================================================
-- 4. BASE DE DATOS: dashboard_db
-- ========================================================
CREATE DATABASE IF NOT EXISTS dashboard_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dashboard_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prospectos (
    id INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    vehiculo_id INT NOT NULL,
    etapa VARCHAR(50) NOT NULL,
    empleado_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
    id INT PRIMARY KEY,
    prospecto_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    empleado_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    motivo_perdida VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seguros (
    id INT PRIMARY KEY,
    venta_id INT NOT NULL,
    tipo_seguro VARCHAR(100) NOT NULL,
    prima_esperada DECIMAL(10, 2) NOT NULL,
    prima_real DECIMAL(10, 2) NULL,
    estado VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY idx_jobs_queue (queue)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ========================================================
-- DATOS DE PRUEBA E INICIALIZACIÓN (DML)
-- ========================================================

-- Insertar datos en prospects_db
USE prospects_db;

INSERT INTO roles (id, nombre) VALUES (1, 'administrador'), (2, 'vendedor') ON DUPLICATE KEY UPDATE id=id;

INSERT INTO permisos (id, nombre) VALUES
(1, 'ver_prospectos_todos'), (2, 'ver_prospectos_propios'), (3, 'gestionar_prospectos_todos'), (4, 'gestionar_prospectos_propios'),
(5, 'ver_ventas_todas'), (6, 'ver_ventas_propias'), (7, 'gestionar_ventas_todas'), (8, 'gestionar_ventas_propias'),
(9, 'ver_seguros_todos'), (10, 'ver_seguros_propios'), (11, 'gestionar_seguros_todos'), (12, 'gestionar_seguros_propios'),
(13, 'ver_dashboard_todos'), (14, 'ver_dashboard_propio'), (15, 'ver_roles'), (16, 'gestionar_roles'),
(17, 'ver_empleados'), (18, 'gestionar_empleados'), (19, 'ver_clientes'), (20, 'gestionar_clientes')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 3), (1, 5), (1, 7), (1, 9), (1, 11), (1, 13), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
(2, 2), (2, 4), (2, 6), (2, 8), (2, 10), (2, 12), (2, 14), (2, 19), (2, 20)
ON DUPLICATE KEY UPDATE rol_id=rol_id;

INSERT INTO empleados (id, nombre, email, password, rol_id) VALUES
(1, 'Juan Pérez', 'juan.perez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(2, 'María Gómez', 'maria.gomez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO vehiculos (id, marca, modelo, anio, precio, stock) VALUES
(1, 'Toyota', 'Corolla Hybrid', 2026, 26500.00, 140),
(2, 'Hyundai', 'Tucson', 2026, 31000.00, 140),
(3, 'Kia', 'Sportage', 2025, 29000.00, 140),
(4, 'Mazda', 'CX-30', 2026, 28000.00, 140),
(5, 'Ford', 'Mustang GT', 2025, 55000.00, 140)
ON DUPLICATE KEY UPDATE marca=VALUES(marca), modelo=VALUES(modelo), anio=VALUES(anio), precio=VALUES(precio), stock=VALUES(stock);

INSERT INTO prospectos (id, nombre, email, telefono, vehiculo_id, etapa, empleado_id) VALUES
(1, 'Alejandro Sanz', 'alejandro@example.com', '+51987654321', 1, 'prospeccion', 1),
(2, 'Laura Pausini', 'laura.p@example.com', '+51912345678', 2, 'calificacion', 1),
(3, 'Ricardo Arjona', 'ricardo@example.com', '+51955555555', 3, 'negociacion', 2),
(4, 'Shakira Mebarak', 'shakira@example.com', '+51944444444', 4, 'cierre', 2),
(5, 'Luis Miguel', 'luismi@example.com', '+51933333333', 5, 'cierre', 3),
(6, 'Rosalía Vila', 'rosalia@example.com', '+51922222222', 1, 'negociacion', 3),
(7, 'Juanes Aristizábal', 'juanes@example.com', '+51911111111', 2, 'calificacion', 3)
ON DUPLICATE KEY UPDATE id=id;


-- Insertar datos iniciales réplicas en sales_db
USE sales_db;

INSERT INTO roles (id, nombre) VALUES (1, 'administrador'), (2, 'vendedor') ON DUPLICATE KEY UPDATE id=id;

INSERT INTO permisos (id, nombre) VALUES
(1, 'ver_prospectos_todos'), (2, 'ver_prospectos_propios'), (3, 'gestionar_prospectos_todos'), (4, 'gestionar_prospectos_propios'),
(5, 'ver_ventas_todas'), (6, 'ver_ventas_propias'), (7, 'gestionar_ventas_todas'), (8, 'gestionar_ventas_propias'),
(9, 'ver_seguros_todos'), (10, 'ver_seguros_propios'), (11, 'gestionar_seguros_todos'), (12, 'gestionar_seguros_propios'),
(13, 'ver_dashboard_todos'), (14, 'ver_dashboard_propio'), (15, 'ver_roles'), (16, 'gestionar_roles'),
(17, 'ver_empleados'), (18, 'gestionar_empleados'), (19, 'ver_clientes'), (20, 'gestionar_clientes')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 3), (1, 5), (1, 7), (1, 9), (1, 11), (1, 13), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
(2, 2), (2, 4), (2, 6), (2, 8), (2, 10), (2, 12), (2, 14), (2, 19), (2, 20)
ON DUPLICATE KEY UPDATE rol_id=rol_id;

INSERT INTO empleados (id, nombre, email, password, rol_id) VALUES
(1, 'Juan Pérez', 'juan.perez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(2, 'María Gómez', 'maria.gomez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO vehiculos (id, marca, modelo, anio, precio, stock) VALUES
(1, 'Toyota', 'Corolla Hybrid', 2026, 26500.00, 140),
(2, 'Hyundai', 'Tucson', 2026, 31000.00, 140),
(3, 'Kia', 'Sportage', 2025, 29000.00, 140),
(4, 'Mazda', 'CX-30', 2026, 28000.00, 140),
(5, 'Ford', 'Mustang GT', 2025, 55000.00, 140)
ON DUPLICATE KEY UPDATE marca=VALUES(marca), modelo=VALUES(modelo), anio=VALUES(anio), precio=VALUES(precio), stock=VALUES(stock);

INSERT INTO prospectos (id, nombre, email, telefono, vehiculo_id, etapa, empleado_id) VALUES
(1, 'Alejandro Sanz', 'alejandro@example.com', '+51987654321', 1, 'prospeccion', 1),
(2, 'Laura Pausini', 'laura.p@example.com', '+51912345678', 2, 'calificacion', 1),
(3, 'Ricardo Arjona', 'ricardo@example.com', '+51955555555', 3, 'negociacion', 2),
(4, 'Shakira Mebarak', 'shakira@example.com', '+51944444444', 4, 'cierre', 2),
(5, 'Luis Miguel', 'luismi@example.com', '+51933333333', 5, 'cierre', 3),
(6, 'Rosalía Vila', 'rosalia@example.com', '+51922222222', 1, 'negociacion', 3),
(7, 'Juanes Aristizábal', 'juanes@example.com', '+51911111111', 2, 'calificacion', 3)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO ventas (id, prospecto_id, vehiculo_id, empleado_id, monto, estado, motivo_perdida) VALUES
(1, 4, 4, 2, 28000.00, 'efectiva', NULL),
(2, 5, 5, 3, 55000.00, 'fallida', 'Presupuesto fuera de alcance y falta de financiamiento inmediato')
ON DUPLICATE KEY UPDATE id=id;


-- Insertar datos iniciales réplicas en insurance_db
USE insurance_db;

INSERT INTO roles (id, nombre) VALUES (1, 'administrador'), (2, 'vendedor') ON DUPLICATE KEY UPDATE id=id;

INSERT INTO permisos (id, nombre) VALUES
(1, 'ver_prospectos_todos'), (2, 'ver_prospectos_propios'), (3, 'gestionar_prospectos_todos'), (4, 'gestionar_prospectos_propios'),
(5, 'ver_ventas_todas'), (6, 'ver_ventas_propias'), (7, 'gestionar_ventas_todas'), (8, 'gestionar_ventas_propias'),
(9, 'ver_seguros_todos'), (10, 'ver_seguros_propios'), (11, 'gestionar_seguros_todos'), (12, 'gestionar_seguros_propios'),
(13, 'ver_dashboard_todos'), (14, 'ver_dashboard_propio'), (15, 'ver_roles'), (16, 'gestionar_roles'),
(17, 'ver_empleados'), (18, 'gestionar_empleados'), (19, 'ver_clientes'), (20, 'gestionar_clientes')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 3), (1, 5), (1, 7), (1, 9), (1, 11), (1, 13), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
(2, 2), (2, 4), (2, 6), (2, 8), (2, 10), (2, 12), (2, 14), (2, 19), (2, 20)
ON DUPLICATE KEY UPDATE rol_id=rol_id;

INSERT INTO empleados (id, nombre, email, password, rol_id) VALUES
(1, 'Juan Pérez', 'juan.perez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(2, 'María Gómez', 'maria.gomez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO ventas (id, prospecto_id, vehiculo_id, empleado_id, monto, estado) VALUES
(1, 4, 4, 2, 28000.00, 'efectiva'),
(2, 5, 5, 3, 55000.00, 'fallida')
ON DUPLICATE KEY UPDATE id=id;

-- Insertar seguros en insurance_db
INSERT INTO seguros (id, venta_id, tipo_seguro, prima_esperada, prima_real, estado) VALUES
(1, 1, 'Todo Riesgo Premium Plus', 1200.00, 1150.00, 'vendido')
ON DUPLICATE KEY UPDATE id=id;


-- Insertar datos iniciales réplicas en dashboard_db
USE dashboard_db;

INSERT INTO roles (id, nombre) VALUES (1, 'administrador'), (2, 'vendedor') ON DUPLICATE KEY UPDATE id=id;

INSERT INTO permisos (id, nombre) VALUES
(1, 'ver_prospectos_todos'), (2, 'ver_prospectos_propios'), (3, 'gestionar_prospectos_todos'), (4, 'gestionar_prospectos_propios'),
(5, 'ver_ventas_todas'), (6, 'ver_ventas_propias'), (7, 'gestionar_ventas_todas'), (8, 'gestionar_ventas_propias'),
(9, 'ver_seguros_todos'), (10, 'ver_seguros_propios'), (11, 'gestionar_seguros_todos'), (12, 'gestionar_seguros_propios'),
(13, 'ver_dashboard_todos'), (14, 'ver_dashboard_propio'), (15, 'ver_roles'), (16, 'gestionar_roles'),
(17, 'ver_empleados'), (18, 'gestionar_empleados'), (19, 'ver_clientes'), (20, 'gestionar_clientes')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 3), (1, 5), (1, 7), (1, 9), (1, 11), (1, 13), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
(2, 2), (2, 4), (2, 6), (2, 8), (2, 10), (2, 12), (2, 14), (2, 19), (2, 20)
ON DUPLICATE KEY UPDATE rol_id=rol_id;

INSERT INTO empleados (id, nombre, email, password, rol_id) VALUES
(1, 'Juan Pérez', 'juan.perez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(2, 'María Gómez', 'maria.gomez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 2),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO prospectos (id, nombre, email, telefono, vehiculo_id, etapa, empleado_id) VALUES
(1, 'Alejandro Sanz', 'alejandro@example.com', '+51987654321', 1, 'prospeccion', 1),
(2, 'Laura Pausini', 'laura.p@example.com', '+51912345678', 2, 'calificacion', 1),
(3, 'Ricardo Arjona', 'ricardo@example.com', '+51955555555', 3, 'negociacion', 2),
(4, 'Shakira Mebarak', 'shakira@example.com', '+51944444444', 4, 'cierre', 2),
(5, 'Luis Miguel', 'luismi@example.com', '+51933333333', 5, 'cierre', 3),
(6, 'Rosalía Vila', 'rosalia@example.com', '+51922222222', 1, 'negociacion', 3),
(7, 'Juanes Aristizábal', 'juanes@example.com', '+51911111111', 2, 'calificacion', 3)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO ventas (id, prospecto_id, vehiculo_id, empleado_id, monto, estado, motivo_perdida) VALUES
(1, 4, 4, 2, 28000.00, 'efectiva', NULL),
(2, 5, 5, 3, 55000.00, 'fallida', 'Presupuesto fuera de alcance y falta de financiamiento inmediato')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO seguros (id, venta_id, tipo_seguro, prima_esperada, prima_real, estado) VALUES
(1, 1, 'Todo Riesgo Premium Plus', 1200.00, 1150.00, 'vendido')
ON DUPLICATE KEY UPDATE id=id;

-- Grant privileges to the user automotriz_user for all microservice databases
-- ========================================================
-- OTORGAR PRIVILEGIOS AL USUARIO automotriz_user
-- ========================================================
GRANT ALL PRIVILEGES ON prospects_db.* TO 'automotriz_user'@'%';
GRANT ALL PRIVILEGES ON sales_db.* TO 'automotriz_user'@'%';
GRANT ALL PRIVILEGES ON insurance_db.* TO 'automotriz_user'@'%';
GRANT ALL PRIVILEGES ON dashboard_db.* TO 'automotriz_user'@'%';
FLUSH PRIVILEGES;
