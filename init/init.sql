CREATE TABLE `bodega` (
  `Id_Bodega` int(11) NOT NULL,
  `Nombre_Bodega` varchar(45) NOT NULL,
  `Ubicacion` varchar(45) NOT NULL,
  `Estado` enum('Disponible','Temporal','No Conforme') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `bodega`
--

INSERT INTO `bodega` (`Id_Bodega`, `Nombre_Bodega`, `Ubicacion`, `Estado`) VALUES
(1, 'Ubicacion 1', 'Nariño Principal', 'Disponible'),
(2, 'Ubicacion 2', 'Nariño Ppal', 'Temporal'),
(9, 'Ubicacion 3', 'El Rosario', 'Temporal'),
(13, 'Ubicacion 4', 'Pasto', 'Disponible'),
(14, 'Ubicacion 5', 'La Cruz', 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `Id_cliente` int(11) NOT NULL,
  `No_NIT` int(11) NOT NULL,
  `Nombre_Cliente` varchar(45) NOT NULL,
  `Direccion` varchar(45) NOT NULL,
  `No_Telefono` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`Id_cliente`, `No_NIT`, `Nombre_Cliente`, `Direccion`, `No_Telefono`, `Email`) VALUES
(1, 56789098, 'Orlando Moreno', 'El Estero Cra. 7 No. 8-75 Sur', 312587985, 'omoreno@gmail.com'),
(2, 25856558, 'Sonfia Nuñez', 'El Saldo Calle 3 No. 15-18', 312354451, 'sofianuñez85@hotmail.com'),
(3, 2147483647, 'Alberto Pinilla', 'Cra 15 No. 12-34', 2147483647, 'albertop@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_egreso`
--

CREATE TABLE `detalle_egreso` (
  `Id_Detalle_Egreso` int(11) NOT NULL,
  `Id_Egreso` int(11) NOT NULL,
  `Id_cliente` int(11) NOT NULL,
  `ID_Bodega` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `PVP` double NOT NULL,
  `Subtotal_Egreso` double NOT NULL,
  `IVA_Egreso` double NOT NULL,
  `Total_Egreso` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_egreso`
--

INSERT INTO `detalle_egreso` (`Id_Detalle_Egreso`, `Id_Egreso`, `Id_cliente`, `ID_Bodega`, `ID_Producto`, `Cantidad`, `PVP`, `Subtotal_Egreso`, `IVA_Egreso`, `Total_Egreso`) VALUES
(2, 2, 0, 1, 9, 7, 0, 0, 0, 0),
(3, 3, 0, 2, 9, 6, 0, 0, 0, 0),
(4, 4, 0, 1, 9, 1, 0, 0, 0, 0),
(5, 5, 0, 1, 10, 1, 0, 0, 0, 0),
(6, 6, 0, 1, 10, 1, 0, 0, 0, 0),
(10, 15, 1, 1, 10, 1, 0, 0, 0, 0),
(11, 16, 2, 1, 10, 1, 4, 0, 0, 0),
(12, 17, 2, 1, 10, 1, 4, 0, 0, 0),
(17, 22, 2, 2, 10, 1, 4, 0, 0, 0),
(18, 23, 1, 1, 9, 1, 14, 0, 0, 0),
(19, 23, 1, 1, 11, 2, 3.5, 0, 0, 0),
(20, 24, 2, 1, 9, 1, 14, 0, 0, 0),
(21, 24, 2, 1, 12, 1, 5, 0, 0, 0),
(22, 25, 2, 1, 10, 8, 4, 0, 0, 0),
(23, 26, 2, 1, 10, 7, 4, 0, 0, 0),
(24, 27, 1, 1, 9, 7, 14, 0, 0, 0),
(25, 28, 1, 1, 10, 5, 4, 0, 0, 0),
(26, 29, 1, 1, 9, 10, 14, 0, 0, 0),
(27, 34, 0, 1, 10, 10, 4000, 0, 0, 0),
(28, 35, 0, 1, 10, 10, 4000, 0, 0, 0),
(29, 35, 0, 1, 11, 10, 3500, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ingreso`
--

CREATE TABLE `detalle_ingreso` (
  `ID_Detalle_Ingreso` int(11) NOT NULL,
  `ID_Ingreso` int(11) NOT NULL,
  `ID_Proveedor` int(45) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `ID_Bodega` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Costo_Unitario` double NOT NULL,
  `Subtotal_Ingreso` double NOT NULL,
  `IVA_Ingreso` double NOT NULL,
  `Total_Ingreso` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_ingreso`
--

INSERT INTO `detalle_ingreso` (`ID_Detalle_Ingreso`, `ID_Ingreso`, `ID_Proveedor`, `ID_Producto`, `ID_Bodega`, `Cantidad`, `Costo_Unitario`, `Subtotal_Ingreso`, `IVA_Ingreso`, `Total_Ingreso`) VALUES
(1, 6, 0, 9, 1, 10, 4.5, 0, 0, 0),
(2, 7, 0, 9, 1, 5, 7800, 0, 0, 0),
(3, 8, 0, 9, 1, 12, 3500, 0, 0, 0),
(4, 9, 0, 12, 1, 20, 3500, 0, 0, 0),
(5, 10, 0, 9, 1, 10, 3500, 0, 0, 0),
(6, 11, 0, 10, 1, 10, 3500, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egreso_producto`
--

CREATE TABLE `egreso_producto` (
  `Id_Egreso` int(11) NOT NULL,
  `Tipo_Egreso` enum('Factura','Ajuste') NOT NULL,
  `Id_cliente` int(11) NOT NULL,
  `Fecha_Egreso` date NOT NULL,
  `Subtotal_Egreso` decimal(10,2) NOT NULL,
  `IVA_Egreso` decimal(10,2) NOT NULL,
  `Total_Egreso` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `egreso_producto`
--

INSERT INTO `egreso_producto` (`Id_Egreso`, `Tipo_Egreso`, `Id_cliente`, `Fecha_Egreso`, `Subtotal_Egreso`, `IVA_Egreso`, `Total_Egreso`) VALUES
(1, '', 2, '2025-06-18', 0.00, 0.00, 116620.00),
(2, 'Factura', 2, '2025-06-18', 0.00, 0.00, 116620.00),
(3, '', 1, '2025-06-18', 0.00, 0.00, 99960.00),
(4, '', 1, '2025-06-18', 0.00, 0.00, 16660.00),
(5, '', 1, '2025-08-11', 0.00, 0.00, 0.00),
(6, '', 2, '2025-08-11', 4.00, 0.00, 4.17),
(7, '', 2, '2025-08-11', 4.00, 0.00, 4.17),
(8, '', 2, '2025-08-11', 4.00, 0.00, 4.17),
(9, '', 1, '2025-08-11', 4.00, 0.00, 4.17),
(10, '', 1, '2025-08-11', 4.00, 0.00, 4.17),
(11, '', 2, '2025-08-11', 14.00, 3.00, 16.66),
(12, '', 1, '2025-08-11', 4.00, 66500.00, 4.17),
(13, '', 1, '2025-08-11', 4.00, 66500.00, 4.17),
(14, '', 3, '2025-08-11', 0.00, 0.00, 0.00),
(15, '', 1, '2025-08-11', 0.00, 0.00, 0.00),
(16, '', 2, '2025-08-11', 4.00, 0.00, 4.17),
(17, 'Factura', 2, '2025-08-11', 4.00, 0.00, 4.17),
(18, 'Factura', 1, '2025-08-13', 0.00, 0.00, 0.00),
(19, 'Factura', 2, '2025-08-13', 0.00, 0.00, 0.00),
(20, 'Factura', 3, '2025-08-13', 0.00, 0.00, 0.00),
(21, 'Factura', 2, '2025-08-13', 0.00, 0.00, 0.00),
(22, 'Factura', 2, '2025-08-12', 4.00, 0.00, 4.17),
(23, 'Factura', 1, '2025-08-23', 21.00, 0.00, 24.99),
(24, 'Factura', 2, '2025-08-23', 19.00, 0.00, 22.61),
(25, 'Factura', 2, '2025-08-23', 32.00, 6.00, 38.08),
(26, 'Factura', 2, '2025-08-23', 28.00, 5.00, 33.32),
(27, 'Factura', 1, '2025-08-23', 98.00, 18.62, 116.62),
(28, 'Factura', 1, '2025-08-23', 20000.00, 3800.00, 23800.00),
(29, 'Factura', 1, '2025-08-23', 140.00, 26.60, 166.60),
(30, 'Factura', 1, '2025-08-23', 21000.00, 3990.00, 24990.00),
(31, 'Factura', 2, '2025-08-23', 98000.00, 18620.00, 116620.00),
(32, 'Factura', 1, '2025-08-23', 40000.00, 7600.00, 47600.00),
(33, 'Factura', 2, '2025-08-23', 60000.00, 11400.00, 71400.00),
(34, 'Factura', 1, '2025-08-23', 40000.00, 7600.00, 47600.00),
(35, 'Factura', 1, '2025-08-25', 75000.00, 14250.00, 89250.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso_producto`
--

CREATE TABLE `ingreso_producto` (
  `ID_Ingreso` int(11) NOT NULL,
  `Tipo_Ingreso` enum('Orden de Compra','Ajuste x Inventario','Ajuste x Produccion') NOT NULL,
  `ID_Proveedor` int(11) NOT NULL,
  `No_Doc_Proveedor` int(11) NOT NULL,
  `Fecha_Ingreso` date NOT NULL,
  `Estado` enum('Pendiente','Grabado','','') NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `IVA` decimal(10,2) NOT NULL,
  `Total` decimal(10,2) NOT NULL,
  `Fecha_Creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ingreso_producto`
--

INSERT INTO `ingreso_producto` (`ID_Ingreso`, `Tipo_Ingreso`, `ID_Proveedor`, `No_Doc_Proveedor`, `Fecha_Ingreso`, `Estado`, `Subtotal`, `IVA`, `Total`, `Fecha_Creacion`) VALUES
(5, 'Orden de Compra', 1, 10003, '2025-04-02', 'Grabado', 0.00, 0.00, 0.00, '2025-06-17 00:00:00'),
(6, 'Orden de Compra', 1, 5245845, '2025-06-18', 'Pendiente', 45.00, 8.55, 53.55, '2025-06-18 21:58:24'),
(7, 'Orden de Compra', 1, 85987254, '2025-06-18', 'Pendiente', 39000.00, 7410.00, 46410.00, '2025-06-18 23:19:32'),
(8, 'Orden de Compra', 1, 587874, '2025-08-10', 'Pendiente', 42000.00, 7980.00, 49980.00, '2025-08-10 18:00:24'),
(9, 'Orden de Compra', 1, 586854, '2025-08-10', 'Pendiente', 70000.00, 13300.00, 83300.00, '2025-08-10 18:24:06'),
(10, 'Orden de Compra', 2, 5898758, '2025-08-11', 'Pendiente', 35000.00, 6650.00, 41650.00, '2025-08-11 21:07:10'),
(11, 'Orden de Compra', 1, 45789825, '2025-08-20', 'Pendiente', 35000.00, 6650.00, 41650.00, '2025-08-20 21:52:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `ID_Producto` int(11) NOT NULL,
  `ID_Bodega` int(11) NOT NULL,
  `Stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Stock_Minimo` decimal(10,2) DEFAULT 0.00,
  `Stock_Optimo` decimal(10,2) DEFAULT 0.00,
  `Fecha_Actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`ID_Producto`, `ID_Bodega`, `Stock`, `Stock_Minimo`, `Stock_Optimo`, `Fecha_Actualizacion`) VALUES
(11, 1, -9.00, 0.00, 0.00, '2025-08-26 03:44:10'),
(12, 1, 21.00, 0.00, 0.00, '2025-08-10 23:24:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id_modulo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `carpeta` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `id_perfil` int(10) UNSIGNED NOT NULL,
  `nombre_perfil` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `perfiles`
--

INSERT INTO `perfiles` (`id_perfil`, `nombre_perfil`, `descripcion`, `estado`) VALUES
(1, 'Administracion', 'Permisos generales del sistema, parametrización de usuarios y acceso general a modulos.', 'activo'),
(2, 'Compras', 'Permisos al módulo de abastecimiento e inventarios', 'activo'),
(3, 'Ventas', 'Acceso a modulos de ventas', 'activo'),
(4, 'Logistica', 'Acceso a modulos de Inventarios', 'activo'),
(5, 'Contabilidad', 'Acceso a los moduloso contables y costos.', 'activo'),
(6, 'Personal', 'Acceso a los moduoos de recuros humanos y nomina', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_permiso`
--

CREATE TABLE `perfil_permiso` (
  `id_perfil` int(10) UNSIGNED NOT NULL,
  `id_permiso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(10) UNSIGNED NOT NULL,
  `modulo` varchar(60) NOT NULL,
  `accion` enum('ver','crear','editar','eliminar','anular','') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `modulo`, `accion`, `descripcion`) VALUES
(1, 'Usuarios', 'ver', 'Permite ver los usuarios registrados'),
(2, 'Usuarios', 'crear', 'Permite crear nuevos usuarios'),
(3, 'Usuarios', 'editar', 'Permite editar usuarios existentes'),
(4, 'Usuarios', 'eliminar', 'Permite eliminar usuarios'),
(5, 'Configuración', 'ver', 'Permite ver la configuración del sistema'),
(6, 'Configuración', 'editar', 'Permite modificar la configuración del sistema'),
(7, 'Proveedores', 'ver', 'Permite ver los proveedores'),
(8, 'Proveedores', 'crear', 'Permite registrar proveedores'),
(9, 'Proveedores', 'editar', 'Permite editar proveedores'),
(10, 'Proveedores', 'eliminar', 'Permite eliminar proveedores'),
(11, 'Compras y Ajustes', 'ver', 'Permite ver las compras registradas'),
(12, 'Compras y Ajustes', 'crear', 'Permite registrar nuevas compras'),
(13, 'Compras y Ajustes', 'editar', 'Permite modificar compras existentes'),
(14, 'Compras y Ajustes', 'anular', 'Permite anular compras'),
(15, 'Anulaciones', 'ver', 'Permite ver documentos anulados'),
(16, 'Anulaciones', 'anular', 'Permite anular documentos'),
(17, 'Clientes', 'ver', 'Permite ver clientes registrados'),
(18, 'Clientes', 'crear', 'Permite crear nuevos clientes'),
(19, 'Clientes', 'editar', 'Permite editar clientes'),
(20, 'Clientes', 'eliminar', 'Permite eliminar clientes'),
(21, 'Facturación', 'ver', 'Permite ver facturas emitidas'),
(22, 'Facturación', 'crear', 'Permite emitir nuevas facturas'),
(23, 'Facturación', 'editar', 'Permite modificar facturas existentes'),
(24, 'Facturación', 'anular', 'Permite anular facturas'),
(25, 'Notas Débito y Crédito', 'ver', 'Permite ver notas de débito y crédito'),
(26, 'Notas Débito y Crédito', 'crear', 'Permite crear nuevas notas'),
(27, 'Notas Débito y Crédito', 'editar', 'Permite editar notas existentes'),
(28, 'Notas Débito y Crédito', '', 'Permite anular notas'),
(29, 'Stocks', 'ver', 'Permite ver los stocks de productos'),
(30, 'Stocks', '', 'Permite realizar ajustes de stock'),
(31, 'Productos', 'ver', 'Permite ver productos'),
(32, 'Productos', 'crear', 'Permite registrar productos'),
(33, 'Productos', 'editar', 'Permite modificar productos'),
(34, 'Productos', 'eliminar', 'Permite eliminar productos'),
(35, 'Bodegas', 'ver', 'Permite ver bodegas'),
(36, 'Bodegas', 'crear', 'Permite crear nuevas bodegas'),
(37, 'Bodegas', 'editar', 'Permite editar bodegas existentes'),
(38, 'Bodegas', 'eliminar', 'Permite eliminar bodegas'),
(39, 'Ajustes de Inventario', 'ver', 'Permite ver ajustes de inventario'),
(40, 'Ajustes de Inventario', 'crear', 'Permite registrar ajustes de inventario'),
(41, 'Ajustes de Inventario', 'editar', 'Permite modificar ajustes de inventario'),
(42, 'Ajustes de Inventario', 'eliminar', 'Permite eliminar ajustes de inventario'),
(43, 'Reportes', 'ver', 'Permite ver todos los reportes del sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_Producto` int(11) NOT NULL,
  `ID_Proveedor` int(11) NOT NULL,
  `Nombre_Producto` varchar(45) NOT NULL,
  `Tipo` enum('Mercancia','Insumos','Suministros','Muebles y Enseres') NOT NULL,
  `Categoria` enum('Helados','Lacteos','Yogurt','') NOT NULL,
  `Und_Empaque` enum('Unidad','x10','x12','x24') NOT NULL,
  `Stock` int(45) NOT NULL,
  `Stock_Minimo` int(45) NOT NULL,
  `Stock_Optimo` int(45) NOT NULL,
  `Costo_Unitario` double NOT NULL,
  `PVP` double NOT NULL,
  `ID_Bodega` int(11) NOT NULL,
  `Estado` enum('Disponible','En Revisión','','') NOT NULL,
  `Marca` enum('Crem Helado','Topsy','Popsy','Pinguino') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`ID_Producto`, `ID_Proveedor`, `Nombre_Producto`, `Tipo`, `Categoria`, `Und_Empaque`, `Stock`, `Stock_Minimo`, `Stock_Optimo`, `Costo_Unitario`, `PVP`, `ID_Bodega`, `Estado`, `Marca`) VALUES
(9, 1, 'Copo Helado 150gr', 'Mercancia', 'Helados', 'x10', 20, 10, 20, 10000, 14000, 1, 'Disponible', 'Crem Helado'),
(10, 1, 'Helado Supremo 100gr', 'Mercancia', 'Helados', 'x10', 30, 10, 20, 3000, 4000, 1, 'Disponible', 'Crem Helado'),
(11, 1, 'Helado Supremo 70gr', 'Mercancia', 'Helados', 'x10', 50, 10, 20, 2500, 3500, 1, 'Disponible', 'Crem Helado'),
(12, 1, 'Cono Super 100gr', 'Mercancia', 'Helados', 'Unidad', 10, 10, 20, 3500, 5000, 1, 'Disponible', 'Topsy');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `ID_Proveedor` int(11) NOT NULL,
  `No_NIT` int(11) NOT NULL,
  `Nombre_Proveedor` varchar(45) NOT NULL,
  `Ciudad` varchar(45) NOT NULL,
  `Direccion` varchar(45) NOT NULL,
  `Tel_Contacto` int(11) NOT NULL,
  `Asesor_Contacto` varchar(45) NOT NULL,
  `Productos_Venta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Proveedores';

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`ID_Proveedor`, `No_NIT`, `Nombre_Proveedor`, `Ciudad`, `Direccion`, `Tel_Contacto`, `Asesor_Contacto`, `Productos_Venta`) VALUES
(1, 91247010, 'Industrias lacteas S.A.S', 'Bogota D.C', 'Cdra 70D No. 48-54', 555555, 'Orlando Moreno', 'Leche, Helados, Yogurt'),
(2, 56789098, 'Industrias Alimenticias Heladin', 'Medellin', 'Cra 23 No. 34-56', 304567890, 'Eduardo Maldonado', 'Helado, Queso, Yogurt, Leche'),
(3, 67890456, 'Industrias Persy', 'Bogota D.C.', 'Cra 45 No. 12-67', 302456543, 'Alicia Barragan', 'Helado, Queso, Yogurt, Leche'),
(4, 58985758, 'Helados Robin Hood', 'Pasto Nariño', 'Cra 18 No. 35-45', 214748365, 'Pepito Jimenez', 'Helado, Yogurt'),
(7, 45789789, 'Helados Don Tebi', 'Bogota D.C.', 'Cra 12 45-45', 305789456, 'Domingo Cardenas', 'Helados, Yogurt');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `ID_reporte` int(11) NOT NULL,
  `ID_usuario` int(11) NOT NULL,
  `Tipo_reporte` varchar(45) NOT NULL,
  `Fecha_reporte` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_perfil` int(100) NOT NULL,
  `no_identificacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(45) NOT NULL,
  `ciudad` varchar(45) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `cargo` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(100) NOT NULL,
  `Estado` enum('Activo','Inactivo','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Usuarios del Sistema';

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_perfil`, `no_identificacion`, `nombre`, `apellido`, `ciudad`, `direccion`, `telefono`, `cargo`, `email`, `contrasena`, `Estado`) VALUES
(1, 1, 1023861067, 'Edward Herrera', '', 'Pasto Nariño', 'Cra 26D No. 35C-30 Sur', '2147483647', 'Jefe de Sistemas', 'pepito@hotmail.com', '$2y$10$IyH7CuVfH1kXGY.285v.oeSthNQ6Bc0PTd9MqOji.NxhWhEA5ydRK', 'Activo'),
(2, 3, 1010175086, 'Viviana Caballero', '', 'Bogota', 'Calle 43A Sur No. 12A-55', '318525607', 'Asesor', 'pepita@gmail.com', '$2y$10$IpJOdn.IIiuh2rf35s8FRex0M9fAoXqNXO91W3ZXjtdrf9FBZltCO', 'Activo'),
(5, 4, 67889096, 'Maria Alejandra Guerrero', '', 'Bogota DC. Colombia', 'Calle 45 No. 15-56', '302345678', 'Operario', 'mague17@gmail.com', '$2y$10$y6dnGoNOil7JU0v2VgTF8Ocl1j.RDh9Q0PsMhQfcnm36CXnsY.ad6', 'Activo'),
(7, 2, 79123456, 'Andres Orozco', '', 'Bogota DC. Colombia', 'Cra 12 No. 54-65 Sur', '301456789', 'Jefe De Compras', 'aorozco@gmail.com', '$2y$10$dVmGc6hSSIg1k48iT8i8Ve/pIMu.dpiYOAQ.yVfrqcrlYGBMDtu.e', 'Activo'),
(8, 1, 1025896478, 'Alejandra Castillo', '', 'La Cruz Nariño', 'Calle 5 no. 4-54', '3126756345', 'Directora', 'acastillo@gmail.com', '$2y$10$87AbwOjKj9xqWBNpmrfWzORphoJXlzfvHTXaObUGyECK0GW.xWTvC', 'Activo'),
(11, 2, 25879456, 'Alberto Soriano', '', 'Pasto Nariño', 'cra 18 No. 12-34', '308452598', 'Analista', 'ablertosorinano45@gmail.com', '$2y$10$ArV1eOhFqoerj4tD04CmxesXrAcCQsyZO9kMq4uk3G8fdXED7ly9q', 'Activo'),
(12, 3, 924994767, 'Lia Cardenas', '', 'Guayaquil', 'Mapasingue Oeste Av. 2da #222 y Calle 2da', '0996746638', 'Jefe', 'lia_isabel14@hotmail.com', '$2y$10$uimOqMwhEKcDYmVnES8fv.YYStbczwL1rd8QVlpo6BrRDD6j465/u', 'Activo'),
(13, 1, 907824684, 'Carlos Cardenas', '', 'Bogota DC. Colombia', 'Cra 68 No. 13-54', '31245787636', 'Gerente General', 'carlos.cardenas.macias@gmail.com', '$2y$10$6NbbEnotEavu2M4uE4agXeZ66ECoJWWIr6Rn5wX53DPSzacPK0Sdu', 'Activo'),
(14, 1, 65789855, 'Orlando Caballero', '', 'La Cruz', 'Cra 7 noi. 8-25', '312456789', 'Operario', 'orlandocaballero@gmail.comn', '$2y$10$3UGggs673VAm6tRTZZ0Qj.XMNTv3Fr5xAMIZtq9cdQJ.iebAK4H32', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bodega`
--
ALTER TABLE `bodega`
  ADD PRIMARY KEY (`Id_Bodega`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Id_cliente`);

--
-- Indices de la tabla `detalle_egreso`
--
ALTER TABLE `detalle_egreso`
  ADD PRIMARY KEY (`Id_Detalle_Egreso`),
  ADD KEY `No_Factura` (`Id_Egreso`),
  ADD KEY `Cod_producto` (`ID_Producto`),
  ADD KEY `Cod_Bodega` (`ID_Bodega`),
  ADD KEY `Id_cliente` (`Id_cliente`);

--
-- Indices de la tabla `detalle_ingreso`
--
ALTER TABLE `detalle_ingreso`
  ADD PRIMARY KEY (`ID_Detalle_Ingreso`),
  ADD KEY `No_Ingreso` (`ID_Ingreso`),
  ADD KEY `Cod_producto` (`ID_Producto`),
  ADD KEY `ID_Bodega` (`ID_Bodega`);

--
-- Indices de la tabla `egreso_producto`
--
ALTER TABLE `egreso_producto`
  ADD PRIMARY KEY (`Id_Egreso`),
  ADD KEY `Cod_cliente` (`Id_cliente`);

--
-- Indices de la tabla `ingreso_producto`
--
ALTER TABLE `ingreso_producto`
  ADD PRIMARY KEY (`ID_Ingreso`),
  ADD KEY `Cod_Proveedor` (`ID_Proveedor`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`ID_Producto`,`ID_Bodega`),
  ADD KEY `ID_Bodega` (`ID_Bodega`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id_modulo`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `nombre` (`nombre_perfil`);

--
-- Indices de la tabla `perfil_permiso`
--
ALTER TABLE `perfil_permiso`
  ADD PRIMARY KEY (`id_perfil`,`id_permiso`),
  ADD KEY `fk_pp_permiso` (`id_permiso`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `uniq_permiso` (`modulo`,`accion`),
  ADD KEY `idx_modulo` (`modulo`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_Producto`),
  ADD KEY `Proveedor_Principal` (`ID_Proveedor`),
  ADD KEY `ID_Bodega` (`ID_Bodega`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`ID_Proveedor`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`ID_reporte`),
  ADD KEY `ID_usuario` (`ID_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `No_Identificacion_3` (`no_identificacion`),
  ADD KEY `No_Identificacion` (`no_identificacion`),
  ADD KEY `No_Identificacion_2` (`no_identificacion`),
  ADD KEY `perfiles` (`id_perfil`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bodega`
--
ALTER TABLE `bodega`
  MODIFY `Id_Bodega` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `Id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_egreso`
--
ALTER TABLE `detalle_egreso`
  MODIFY `Id_Detalle_Egreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `detalle_ingreso`
--
ALTER TABLE `detalle_ingreso`
  MODIFY `ID_Detalle_Ingreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `egreso_producto`
--
ALTER TABLE `egreso_producto`
  MODIFY `Id_Egreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `ingreso_producto`
--
ALTER TABLE `ingreso_producto`
  MODIFY `ID_Ingreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  MODIFY `id_perfil` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_Producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `ID_Proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `ID_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_egreso`
--
ALTER TABLE `detalle_egreso`
  ADD CONSTRAINT `detalle_egreso_ibfk_1` FOREIGN KEY (`Id_producto`) REFERENCES `producto` (`ID_Producto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `detalle_egreso_ibfk_2` FOREIGN KEY (`Id_Egreso`) REFERENCES `egreso_producto` (`Id_Egreso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `detalle_egreso_ibfk_3` FOREIGN KEY (`ID_Bodega`) REFERENCES `bodega` (`Id_Bodega`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_ingreso`
--
ALTER TABLE `detalle_ingreso`
  ADD CONSTRAINT `detalle_ingreso_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `detalle_ingreso_ibfk_2` FOREIGN KEY (`ID_Ingreso`) REFERENCES `ingreso_producto` (`ID_Ingreso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `detalle_ingreso_ibfk_3` FOREIGN KEY (`ID_Bodega`) REFERENCES `bodega` (`Id_Bodega`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `egreso_producto`
--
ALTER TABLE `egreso_producto`
  ADD CONSTRAINT `egreso_producto_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `cliente` (`Id_cliente`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ingreso_producto`
--
ALTER TABLE `ingreso_producto`
  ADD CONSTRAINT `ingreso_producto_ibfk_2` FOREIGN KEY (`ID_Proveedor`) REFERENCES `proveedor` (`ID_Proveedor`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`),
  ADD CONSTRAINT `inventario_ibfk_2` FOREIGN KEY (`ID_Bodega`) REFERENCES `bodega` (`Id_Bodega`);

--
-- Filtros para la tabla `perfil_permiso`
--
ALTER TABLE `perfil_permiso`
  ADD CONSTRAINT `fk_pp_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_3` FOREIGN KEY (`ID_Bodega`) REFERENCES `bodega` (`Id_Bodega`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `producto_ibfk_4` FOREIGN KEY (`ID_Proveedor`) REFERENCES `proveedor` (`ID_Proveedor`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
