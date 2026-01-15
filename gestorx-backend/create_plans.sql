-- Verificar si ya existen planes
INSERT IGNORE INTO plan (id_plan, nombre_plan, precio, duracion_meses, limite_usuarios, limite_productos, acceso_reportes, estado_plan) VALUES
(1, 'Básico', 29.99, 1, 5, 100, 1, 'activo'),
(2, 'Premium', 59.99, 1, 20, 500, 1, 'activo'),
(3, 'Enterprise', 99.99, 1, 100, 1000, 1, 'activo');

-- Mostrar planes existentes
SELECT * FROM plan;
