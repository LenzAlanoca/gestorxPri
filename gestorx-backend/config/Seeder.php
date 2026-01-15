<?php

/**
 * DATABASE SEEDER - GestorX
 * 
 * Inserta datos iniciales de prueba si no existen
 * Incluye: Planes, Roles, Permisos, Empresa de prueba, Usuario de prueba
 */

namespace GestorX\Database;

class Seeder
{
    private $connection;
    private $seeded = false;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Ejecuta todos los seeders
     */
    public function seed()
    {
        try {
            // Verificar si ya hay datos
            if ($this->hasData()) {
                return true;
            }

            // Ejecutar seeders en orden
            $this->seedPlanes();
            $this->seedRoles();
            $this->seedPermisos();
            $this->seedRolesPermisos();
            $this->seedUnidadesMedida();
            $this->seedEmpresaPrueba();

            $this->seeded = true;
            return true;
        } catch (\Exception $e) {
            error_log('Seeder error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existen datos
     */
    private function hasData()
    {
        try {
            $stmt = $this->connection->query("SELECT COUNT(*) FROM rol");
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Inserta planes de suscripción
     */
    private function seedPlanes()
    {
        $query = "INSERT INTO plan (nombre_plan, precio, duracion_meses, limite_usuarios, limite_productos, acceso_reportes, estado_plan)
                  VALUES 
                  ('Plan Básico', 0.00, 1, 5, 50, FALSE, 'activo'),
                  ('Plan Pro', 49.99, 1, 20, 500, TRUE, 'activo'),
                  ('Plan Enterprise', 199.99, 1, 100, 5000, TRUE, 'activo')";

        $this->connection->exec($query);
    }

    /**
     * Inserta roles
     */
    private function seedRoles()
    {
        $query = "INSERT INTO rol (nombre_rol, descripcion)
                  VALUES 
                  ('superadministrador', 'Acceso total al sistema'),
                  ('administrador', 'Gestión de empresa y usuarios'),
                  ('gerente', 'Gestión de inventario y ventas'),
                  ('cajero', 'Procesamiento de ventas'),
                  ('almacenero', 'Control de inventario')";

        $this->connection->exec($query);
    }

    /**
     * Inserta permisos
     */
    private function seedPermisos()
    {
        $permisos = [
            // Módulo Usuarios
            ['nombre_permiso' => 'crear_usuario', 'modulo' => 'usuarios', 'descripcion' => 'Crear nuevos usuarios'],
            ['nombre_permiso' => 'editar_usuario', 'modulo' => 'usuarios', 'descripcion' => 'Editar usuarios'],
            ['nombre_permiso' => 'eliminar_usuario', 'modulo' => 'usuarios', 'descripcion' => 'Eliminar usuarios'],
            ['nombre_permiso' => 'listar_usuarios', 'modulo' => 'usuarios', 'descripcion' => 'Ver lista de usuarios'],

            // Módulo Productos
            ['nombre_permiso' => 'crear_producto', 'modulo' => 'productos', 'descripcion' => 'Crear productos'],
            ['nombre_permiso' => 'editar_producto', 'modulo' => 'productos', 'descripcion' => 'Editar productos'],
            ['nombre_permiso' => 'eliminar_producto', 'modulo' => 'productos', 'descripcion' => 'Eliminar productos'],
            ['nombre_permiso' => 'listar_productos', 'modulo' => 'productos', 'descripcion' => 'Ver productos'],

            // Módulo Ventas
            ['nombre_permiso' => 'crear_venta', 'modulo' => 'ventas', 'descripcion' => 'Crear ventas'],
            ['nombre_permiso' => 'listar_ventas', 'modulo' => 'ventas', 'descripcion' => 'Ver ventas'],
            ['nombre_permiso' => 'anular_venta', 'modulo' => 'ventas', 'descripcion' => 'Anular ventas'],

            // Módulo Compras
            ['nombre_permiso' => 'crear_compra', 'modulo' => 'compras', 'descripcion' => 'Crear compras'],
            ['nombre_permiso' => 'listar_compras', 'modulo' => 'compras', 'descripcion' => 'Ver compras'],
            ['nombre_permiso' => 'anular_compra', 'modulo' => 'compras', 'descripcion' => 'Anular compras'],

            // Módulo Inventario
            ['nombre_permiso' => 'ver_inventario', 'modulo' => 'inventario', 'descripcion' => 'Ver inventario'],
            ['nombre_permiso' => 'ajustar_inventario', 'modulo' => 'inventario', 'descripcion' => 'Ajustar inventario'],

            // Módulo Reportes
            ['nombre_permiso' => 'ver_reportes', 'modulo' => 'reportes', 'descripcion' => 'Ver reportes'],
            ['nombre_permiso' => 'exportar_reportes', 'modulo' => 'reportes', 'descripcion' => 'Exportar reportes'],
        ];

        foreach ($permisos as $permiso) {
            $stmt = $this->connection->prepare(
                "INSERT INTO permiso (nombre_permiso, modulo, descripcion) 
                 VALUES (:nombre, :modulo, :desc)"
            );
            $stmt->execute([
                ':nombre' => $permiso['nombre_permiso'],
                ':modulo' => $permiso['modulo'],
                ':desc' => $permiso['descripcion']
            ]);
        }
    }

    /**
     * Asigna permisos a roles
     */
    private function seedRolesPermisos()
    {
        // Superadmin - todos los permisos
        $stmt = $this->connection->query("SELECT id_permiso FROM permiso");
        $permisos = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($permisos as $id_permiso) {
            $this->connection->prepare(
                "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (1, :id_permiso)"
            )->execute([':id_permiso' => $id_permiso]);
        }

        // Admin - permisos de usuarios, productos, inventario
        $admin_permisos = [
            'crear_usuario',
            'editar_usuario',
            'eliminar_usuario',
            'listar_usuarios',
            'crear_producto',
            'editar_producto',
            'eliminar_producto',
            'listar_productos',
            'ver_inventario',
            'ajustar_inventario',
            'ver_reportes'
        ];

        foreach ($admin_permisos as $perm) {
            $stmt = $this->connection->prepare(
                "SELECT id_permiso FROM permiso WHERE nombre_permiso = :nombre LIMIT 1"
            );
            $stmt->execute([':nombre' => $perm]);
            if ($id_permiso = $stmt->fetchColumn()) {
                $this->connection->prepare(
                    "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (2, :id_permiso)"
                )->execute([':id_permiso' => $id_permiso]);
            }
        }

        // Gerente - ventas, compras, inventario
        $gerente_permisos = [
            'crear_venta',
            'listar_ventas',
            'anular_venta',
            'crear_compra',
            'listar_compras',
            'anular_compra',
            'ver_inventario',
            'ajustar_inventario',
            'ver_reportes'
        ];

        foreach ($gerente_permisos as $perm) {
            $stmt = $this->connection->prepare(
                "SELECT id_permiso FROM permiso WHERE nombre_permiso = :nombre LIMIT 1"
            );
            $stmt->execute([':nombre' => $perm]);
            if ($id_permiso = $stmt->fetchColumn()) {
                $this->connection->prepare(
                    "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (3, :id_permiso)"
                )->execute([':id_permiso' => $id_permiso]);
            }
        }

        // Cajero - solo crear ventas
        $cajero_permisos = ['crear_venta', 'listar_ventas', 'ver_inventario'];
        foreach ($cajero_permisos as $perm) {
            $stmt = $this->connection->prepare(
                "SELECT id_permiso FROM permiso WHERE nombre_permiso = :nombre LIMIT 1"
            );
            $stmt->execute([':nombre' => $perm]);
            if ($id_permiso = $stmt->fetchColumn()) {
                $this->connection->prepare(
                    "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (4, :id_permiso)"
                )->execute([':id_permiso' => $id_permiso]);
            }
        }

        // Almacenero - solo inventario
        $almacenero_permisos = ['ver_inventario', 'ajustar_inventario'];
        foreach ($almacenero_permisos as $perm) {
            $stmt = $this->connection->prepare(
                "SELECT id_permiso FROM permiso WHERE nombre_permiso = :nombre LIMIT 1"
            );
            $stmt->execute([':nombre' => $perm]);
            if ($id_permiso = $stmt->fetchColumn()) {
                $this->connection->prepare(
                    "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (5, :id_permiso)"
                )->execute([':id_permiso' => $id_permiso]);
            }
        }
    }

    /**
     * Inserta unidades de medida
     */
    private function seedUnidadesMedida()
    {
        $query = "INSERT INTO unidad_medida (nombre, abreviatura, estado_unidad)
                  VALUES 
                  ('Unidad', 'UN', 'activo'),
                  ('Kilogramo', 'KG', 'activo'),
                  ('Litro', 'LT', 'activo'),
                  ('Metro', 'M', 'activo'),
                  ('Metro Cuadrado', 'M2', 'activo'),
                  ('Docena', 'DOC', 'activo'),
                  ('Caja', 'CJ', 'activo')";

        $this->connection->exec($query);
    }

    /**
     * Crea empresa y usuario de prueba
     * Email: admin@gestorx.test
     * Password: Admin@2026
     */
    private function seedEmpresaPrueba()
    {
        // Crear empresa de prueba
        $empresa_query = "INSERT INTO empresa 
                        (nombre_comercial, razon_social, direccion, telefono, correo_contacto, moneda, impuesto, stock_minimo_default, estado_empresa, fecha_registro, fecha_expiracion_suscripcion)
                        VALUES 
                        ('GestorX Demo', 'GestorX SAS', 'Av. Principal 123', '(+51) 987654321', 'admin@gestorx.test', 'PEN', 18.0, 10, 'activa', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))";

        $this->connection->exec($empresa_query);
        $id_empresa = $this->connection->lastInsertId();

        // Crear usuario admin de prueba
        $password_hash = password_hash('Admin@2026', PASSWORD_BCRYPT);
        $usuario_query = "INSERT INTO usuario 
                         (id_empresa, id_rol, nombre, apellido, correo, password_hash, estado_usuario, fecha_creacion)
                         VALUES 
                         (:id_empresa, 1, 'Admin', 'GestorX', 'admin@gestorx.test', :password, 'activo', NOW())";

        $stmt = $this->connection->prepare($usuario_query);
        $stmt->execute([
            ':id_empresa' => $id_empresa,
            ':password' => $password_hash
        ]);

        // Crear algunos usuarios de prueba adicionales
        $usuarios_adicionales = [
            ['nombre' => 'Juan', 'apellido' => 'Gerente', 'correo' => 'gerente@gestorx.test', 'rol' => 3, 'password' => 'Gerente@2026'],
            ['nombre' => 'María', 'apellido' => 'Cajera', 'correo' => 'cajera@gestorx.test', 'rol' => 4, 'password' => 'Cajera@2026'],
            ['nombre' => 'Carlos', 'apellido' => 'Almacén', 'correo' => 'almacen@gestorx.test', 'rol' => 5, 'password' => 'Almacen@2026'],
        ];

        foreach ($usuarios_adicionales as $usuario) {
            $pass_hash = password_hash($usuario['password'], PASSWORD_BCRYPT);
            $stmt = $this->connection->prepare(
                "INSERT INTO usuario (id_empresa, id_rol, nombre, apellido, correo, password_hash, estado_usuario, fecha_creacion)
                 VALUES (:id_empresa, :id_rol, :nombre, :apellido, :correo, :password, 'activo', NOW())"
            );
            $stmt->execute([
                ':id_empresa' => $id_empresa,
                ':id_rol' => $usuario['rol'],
                ':nombre' => $usuario['nombre'],
                ':apellido' => $usuario['apellido'],
                ':correo' => $usuario['correo'],
                ':password' => $pass_hash
            ]);
        }

        // Crear categorías de prueba
        $categorias = [
            'Electrónica',
            'Ropa y Accesorios',
            'Alimentos y Bebidas',
            'Hogar y Jardín',
            'Deportes',
            'Belleza y Cuidado Personal'
        ];

        foreach ($categorias as $categoria) {
            $stmt = $this->connection->prepare(
                "INSERT INTO categoria (id_empresa, nombre, estado_categoria) VALUES (:id_empresa, :nombre, 'activo')"
            );
            $stmt->execute([
                ':id_empresa' => $id_empresa,
                ':nombre' => $categoria
            ]);
        }

        // Crear algunos proveedores de prueba
        $proveedores = [
            ['nombre' => 'Proveedor Latino', 'telefono' => '987654321', 'correo' => 'contacto@proveedorlatino.com'],
            ['nombre' => 'Importaciones Asia', 'telefono' => '956789012', 'correo' => 'ventas@asiaimports.com'],
            ['nombre' => 'Distribuidora Nacional', 'telefono' => '912345678', 'correo' => 'info@distribuidora.com'],
        ];

        foreach ($proveedores as $proveedor) {
            $stmt = $this->connection->prepare(
                "INSERT INTO proveedor (id_empresa, nombre, telefono, correo, estado_proveedor)
                 VALUES (:id_empresa, :nombre, :tel, :correo, 'activo')"
            );
            $stmt->execute([
                ':id_empresa' => $id_empresa,
                ':nombre' => $proveedor['nombre'],
                ':tel' => $proveedor['telefono'],
                ':correo' => $proveedor['correo']
            ]);
        }

        // Crear productos de prueba
        $productos = [
            ['nombre' => 'Laptop Dell XPS', 'categoria' => 'Electrónica', 'precio' => 1500.00, 'stock' => 10],
            ['nombre' => 'Mouse Inalámbrico', 'categoria' => 'Electrónica', 'precio' => 25.00, 'stock' => 50],
            ['nombre' => 'Teclado Mecánico', 'categoria' => 'Electrónica', 'precio' => 80.00, 'stock' => 15],
            ['nombre' => 'Monitor 27 pulgadas', 'categoria' => 'Electrónica', 'precio' => 350.00, 'stock' => 8],
            ['nombre' => 'Camiseta Básica', 'categoria' => 'Ropa y Accesorios', 'precio' => 15.00, 'stock' => 100],
            ['nombre' => 'Pantalón Jeans', 'categoria' => 'Ropa y Accesorios', 'precio' => 45.00, 'stock' => 75],
            ['nombre' => 'Café Premium 500g', 'categoria' => 'Alimentos y Bebidas', 'precio' => 18.50, 'stock' => 200],
            ['nombre' => 'Chocolate Belga', 'categoria' => 'Alimentos y Bebidas', 'precio' => 12.00, 'stock' => 150],
        ];

        $id_unidad = 1; // Unidad
        foreach ($productos as $producto) {
            // Obtener ID de categoría
            $cat_stmt = $this->connection->prepare(
                "SELECT id_categoria FROM categoria WHERE id_empresa = :id_empresa AND nombre = :nombre LIMIT 1"
            );
            $cat_stmt->execute([
                ':id_empresa' => $id_empresa,
                ':nombre' => $producto['categoria']
            ]);
            $id_categoria = $cat_stmt->fetchColumn();

            if ($id_categoria) {
                $stmt = $this->connection->prepare(
                    "INSERT INTO producto (id_empresa, id_categoria, id_unidad, nombre, codigo, precio_venta, stock_actual, estado_producto)
                     VALUES (:id_empresa, :id_categoria, :id_unidad, :nombre, :codigo, :precio, :stock, 'activo')"
                );
                $stmt->execute([
                    ':id_empresa' => $id_empresa,
                    ':id_categoria' => $id_categoria,
                    ':id_unidad' => $id_unidad,
                    ':nombre' => $producto['nombre'],
                    ':codigo' => 'PROD-' . uniqid(),
                    ':precio' => $producto['precio'],
                    ':stock' => $producto['stock']
                ]);
            }
        }
    }

    public function isSeeded()
    {
        return $this->seeded;
    }
}

/**
 * Función helper para ejecutar seeder automáticamente
 */
function seedDatabase($connection)
{
    static $seeder = null;

    if ($seeder === null) {
        $seeder = new Seeder($connection);
        $seeder->seed();
    }

    return $seeder->isSeeded();
}
