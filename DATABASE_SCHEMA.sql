-- SCRIPT COMPLETO – SISTEMA SAAS INVENTARIOS Y VENTAS
SET FOREIGN_KEY_CHECKS = 0;

-- TABLA: PLAN
CREATE TABLE plan (
  id_plan INT AUTO_INCREMENT PRIMARY KEY,
  nombre_plan VARCHAR(50),
  precio DECIMAL(10,2),
  duracion_meses INT,
  limite_usuarios INT,
  limite_productos INT,
  acceso_reportes BOOLEAN,
  estado_plan ENUM('activo','inactivo')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: EMPRESA
CREATE TABLE empresa (
  id_empresa INT AUTO_INCREMENT PRIMARY KEY,
  id_plan INT,
  nombre_comercial VARCHAR(100),
  razon_social VARCHAR(150),
  direccion VARCHAR(200),
  telefono VARCHAR(20),
  correo_contacto VARCHAR(100),
  logo VARCHAR(255),
  moneda VARCHAR(10),
  impuesto DECIMAL(5,2),
  stock_minimo_default INT,
  estado_empresa ENUM('activa','suspendida'),
  fecha_registro DATETIME,
  fecha_expiracion_suscripcion DATETIME,
  FOREIGN KEY (id_plan) REFERENCES plan(id_plan) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: ROL
CREATE TABLE rol (
  id_rol INT AUTO_INCREMENT PRIMARY KEY,
  nombre_rol VARCHAR(50),
  descripcion VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PERMISO
CREATE TABLE permiso (
  id_permiso INT AUTO_INCREMENT PRIMARY KEY,
  nombre_permiso VARCHAR(100),
  modulo VARCHAR(50),
  descripcion VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: ROL_PERMISO
CREATE TABLE rol_permiso (
  id_rol INT,
  id_permiso INT,
  PRIMARY KEY (id_rol, id_permiso),
  FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE,
  FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: USUARIO
CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  id_rol INT,
  nombre VARCHAR(50),
  apellido VARCHAR(50),
  correo VARCHAR(100),
  password_hash VARCHAR(255),
  estado_usuario ENUM('activo','inactivo'),
  ultimo_acceso DATETIME,
  fecha_creacion DATETIME,
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
  FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: CLIENTE
CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  nombre VARCHAR(50),
  apellido VARCHAR(50),
  telefono VARCHAR(20),
  correo VARCHAR(100),
  direccion VARCHAR(200),
  tipo_cliente VARCHAR(30),
  estado_cliente ENUM('activo','inactivo'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PROVEEDOR
CREATE TABLE proveedor (
  id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  nombre VARCHAR(100),
  telefono VARCHAR(20),
  correo VARCHAR(100),
  direccion VARCHAR(200),
  estado_proveedor ENUM('activo','inactivo'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: CATEGORIA
CREATE TABLE categoria (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  nombre VARCHAR(100),
  descripcion VARCHAR(150),
  estado_categoria ENUM('activo','inactivo'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: UNIDAD_MEDIDA
CREATE TABLE unidad_medida (
  id_unidad INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50),
  abreviatura VARCHAR(10),
  estado_unidad ENUM('activo','inactivo')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PRODUCTO
CREATE TABLE producto (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  id_categoria INT,
  id_unidad INT,
  nombre VARCHAR(100),
  descripcion VARCHAR(150),
  codigo VARCHAR(50),
  precio_venta DECIMAL(10,2),
  stock_actual INT,
  imagen VARCHAR(255),
  estado_producto ENUM('activo','inactivo'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
  FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE SET NULL,
  FOREIGN KEY (id_unidad) REFERENCES unidad_medida(id_unidad) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PRODUCTO_PROVEEDOR
CREATE TABLE producto_proveedor (
  id_producto INT,
  id_proveedor INT,
  PRIMARY KEY (id_producto, id_proveedor),
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE,
  FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: VENTA
CREATE TABLE venta (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  id_usuario INT,
  id_cliente INT,
  fecha DATETIME,
  total DECIMAL(10,2),
  impuesto DECIMAL(10,2),
  descuento DECIMAL(10,2),
  estado_venta ENUM('activa','anulada'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT,
  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: DETALLE_VENTA
CREATE TABLE detalle_venta (
  id_venta INT,
  id_producto INT,
  cantidad INT,
  precio_unitario DECIMAL(10,2),
  subtotal DECIMAL(10,2),
  PRIMARY KEY (id_venta, id_producto),
  FOREIGN KEY (id_venta) REFERENCES venta(id_venta) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: COMPRA
CREATE TABLE compra (
  id_compra INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  id_proveedor INT,
  id_usuario INT,
  fecha DATETIME,
  total DECIMAL(10,2),
  estado_compra ENUM('activa','anulada'),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
  FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor) ON DELETE RESTRICT,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: DETALLE_COMPRA
CREATE TABLE detalle_compra (
  id_compra INT,
  id_producto INT,
  cantidad INT,
  precio_unitario DECIMAL(10,2),
  subtotal DECIMAL(10,2),
  PRIMARY KEY (id_compra, id_producto),
  FOREIGN KEY (id_compra) REFERENCES compra(id_compra) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: MOVIMIENTO_INVENTARIO
CREATE TABLE movimiento_inventario (
  id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT,
  id_usuario INT,
  tipo_movimiento ENUM('entrada','salida','ajuste'),
  cantidad INT,
  fecha DATETIME,
  observacion VARCHAR(200),
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: NOTIFICACION
CREATE TABLE notificacion (
  id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT,
  id_usuario INT,
  tipo VARCHAR(50),
  mensaje VARCHAR(200),
  fecha DATETIME,
  leido BOOLEAN,
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: RECUPERACION_PASSWORD
CREATE TABLE recuperacion_password (
  id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion DATETIME NOT NULL,
  estado_token ENUM('vigente','usado','expirado') DEFAULT 'vigente',
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_usuario (id_usuario),
  INDEX idx_estado (estado_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
