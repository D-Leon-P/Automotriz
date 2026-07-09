-- ==========================================
-- ESTRUCTURA DE LA BASE DE DATOS (DDL)
-- ==========================================

CREATE DATABASE IF NOT EXISTS automotriz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE automotriz_db;
SET NAMES utf8mb4;

-- 1. Tabla de Vendedores
CREATE TABLE IF NOT EXISTS vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_vendedores_email (email)
) ENGINE=InnoDB;

-- 2. Tabla de Vehículos
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

-- 3. Tabla de Prospectos
CREATE TABLE IF NOT EXISTS prospectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    vehiculo_id INT NOT NULL,
    etapa ENUM('prospeccion', 'calificacion', 'negociacion', 'cierre') NOT NULL DEFAULT 'prospeccion',
    vendedor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE RESTRICT,
    KEY idx_prospectos_etapa (etapa),
    KEY idx_prospectos_vendedor (vendedor_id)
) ENGINE=InnoDB;

-- 4. Tabla de Ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prospecto_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado ENUM('efectiva', 'fallida') NOT NULL,
    motivo_perdida VARCHAR(255) NULL, -- Requerido si el estado es 'fallida'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prospecto_id) REFERENCES prospectos(id) ON DELETE RESTRICT,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE RESTRICT,
    KEY idx_ventas_estado (estado),
    KEY idx_ventas_vendedor (vendedor_id)
) ENGINE=InnoDB;

-- 5. Tabla de Seguros Vehiculares
CREATE TABLE IF NOT EXISTS seguros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    tipo_seguro VARCHAR(100) NOT NULL, -- e.g., 'Todo Riesgo Premium', 'Responsabilidad Civil', etc.
    prima_esperada DECIMAL(10, 2) NOT NULL,
    prima_real DECIMAL(10, 2) NULL,
    estado ENUM('prospectado', 'vendido') NOT NULL DEFAULT 'prospectado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
    KEY idx_seguros_estado (estado)
) ENGINE=InnoDB;

-- ==========================================
-- DATOS DE PRUEBA (DML)
-- ==========================================

-- Insertar Vendedores (Passwords encriptados con bcrypt para '$2y$10$...' correspondientes a 'password123')
INSERT INTO vendedores (id, nombre, email, password) VALUES
(1, 'Juan Pérez', 'juan.perez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja'),
(2, 'María Gómez', 'maria.gomez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja'),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@automotriz.com', '$2y$10$Jin9DjsA2VJG8Xtcve2y2evddnoEiIl96KVtAz6FJ9IF4cck5mcja')
ON DUPLICATE KEY UPDATE id=id;

-- Insertar Vehículos
INSERT INTO vehiculos (id, marca, modelo, anio, precio, stock) VALUES
(1, 'Toyota', 'Corolla Hybrid', 2026, 26500.00, 10),
(2, 'Hyundai', 'Tucson', 2026, 31000.00, 5),
(3, 'Kia', 'Sportage', 2025, 29000.00, 8),
(4, 'Mazda', 'CX-30', 2026, 28000.00, 4),
(5, 'Ford', 'Mustang GT', 2025, 55000.00, 2)
ON DUPLICATE KEY UPDATE id=id;

-- Insertar Prospectos en distintas etapas
INSERT INTO prospectos (id, nombre, email, telefono, vehiculo_id, etapa, vendedor_id) VALUES
(1, 'Alejandro Sanz', 'alejandro@example.com', '+51987654321', 1, 'prospeccion', 1),
(2, 'Laura Pausini', 'laura.p@example.com', '+51912345678', 2, 'calificacion', 1),
(3, 'Ricardo Arjona', 'ricardo@example.com', '+51955555555', 3, 'negociacion', 2),
(4, 'Shakira Mebarak', 'shakira@example.com', '+51944444444', 4, 'cierre', 2),
(5, 'Luis Miguel', 'luismi@example.com', '+51933333333', 5, 'cierre', 3),
(6, 'Rosalía Vila', 'rosalia@example.com', '+51922222222', 1, 'negociacion', 3),
(7, 'Juanes Aristizábal', 'juanes@example.com', '+51911111111', 2, 'calificacion', 3)
ON DUPLICATE KEY UPDATE id=id;

-- Insertar Ventas (Efectivas y Fallidas)
-- Prospecto 4 finaliza en Cierre -> Venta Efectiva
INSERT INTO ventas (id, prospecto_id, vehiculo_id, vendedor_id, monto, estado, motivo_perdida) VALUES
(1, 4, 4, 2, 28000.00, 'efectiva', NULL),
-- Prospecto 5 finaliza en Cierre -> Venta Fallida
(2, 5, 5, 3, 55000.00, 'fallida', 'Presupuesto fuera de alcance y falta de financiamiento inmediato')
ON DUPLICATE KEY UPDATE id=id;

-- Insertar Seguros
INSERT INTO seguros (id, venta_id, tipo_seguro, prima_esperada, prima_real, estado) VALUES
(1, 1, 'Todo Riesgo Premium Plus', 1200.00, 1150.00, 'vendido')
ON DUPLICATE KEY UPDATE id=id;
