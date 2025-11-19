# Sistema de Parqueadero V.S - Guía de Instalación y Uso

## 📋 Resumen de Funcionalidades Implementadas

### ✅ Funcionalidades Completadas

1. **Sistema de Login** - Autenticación segura con contraseñas hasheadas
2. **Recuperar Contraseña** - Cambio de contraseña verificando si el usuario existe
3. **Logout** - Cierre de sesión seguro
4. **Panel de Administración** - Protegido con verificación de sesión

## 🚀 Configuración de la Base de Datos

### Paso 1: Crear la base de datos y tablas

Ejecuta el script SQL en `db/crear_tablas_completas.sql` en tu base de datos MySQL:

Este script crea:
- Base de datos `parqueadero_vale`
- Tabla `usuario` para administradores
- Tabla `motos` para las motocicletas
- Tabla `propietarios` para los dueños
- Tabla `registros` para entrada/salida
- Tabla `pagos` para transacciones
- Tabla `ticket` para tickets de ingreso/salida
- Usuario de administrador por defecto

### Paso 2: Verificar usuario de administrador

El script ya incluye un usuario de administrador:

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

## ⚙️ Configuración de Conexión

Verifica que la configuración en `db/conexion.php` sea correcta:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'parqueadero_vale';
```

Ajusta estos valores según tu configuración local de MySQL.

## 📝 Cómo Usar el Sistema

### 1. Login
1. Accede a `http://localhost/parqueadero_vale/`
2. Ingresa tus credenciales:
   - Usuario: `admin`
   - Contraseña: `admin123`
3. Haz clic en "INGRESAR"

### 2. Recuperar Contraseña
Si olvidaste tu contraseña:
1. En la página de login, haz clic en "¿Olvidaste tu contraseña?"
2. Ingresa tu usuario
3. Ingresa tu nueva contraseña
4. Confirma la nueva contraseña
5. Haz clic en "CAMBIAR CONTRASEÑA"

**Nota:** El sistema verifica que el usuario exista antes de permitir el cambio.

### 3. Logout
1. En el panel de administración
2. Haz clic en "Cerrar Sesión"

## 📁 Estructura de Archivos

```
parqueadero_vale/
├── admin/
│   ├── index.php              # Panel de administración
│   └── vistas/                # Vistas del admin
├── controladores/
│   ├── loginController.php    # Controlador de login
│   ├── logoutController.php   # Controlador de logout
│   └── recuperarContraseñaController.php  # Controlador de recuperación
├── css/
│   └── login.css              # Estilos del login
├── db/
│   ├── conexion.php           # Conexión a la base de datos
│   ├── crear_tablas_completas.sql  # Script de creación de BD
│   └── parqueadero_vale.txt   # Estructura de tablas original
├── index.php                  # Página de login
└── recuperar_contraseña.php   # Página de recuperación
```

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Verificación de sesión en páginas protegidas
- ✅ Sanitización de datos de entrada
- ✅ Uso de prepared statements para prevenir SQL injection
- ✅ Validación de campos requeridos

## 📊 Requerimientos del Sistema

Según el archivo `requerimentos`:

### Completados ✅
- [x] Admin debe poder ingresar con usuario y contraseña
- [x] Admin debe poder cambiar la contraseña verificando si existe
- [x] Admin debe poder desloguearse

### Pendientes 📝
- [ ] Registrar moto_propietario
- [ ] CRUD completo de usuarios
- [ ] Dashboard con estadísticas
- [ ] Exportar a Excel con filtros
- [ ] Búsqueda general de cualquier dato
- [ ] Generar ticket de ingreso/salida
- [ ] Cálculo automático de tarifas (6,000 por día)
- [ ] Gestión de registros de entrada/salida

## 🛠️ Próximos Pasos

1. Implementar CRUD de motos y propietarios
2. Crear sistema de registro de entrada/salida
3. Implementar cálculo automático de tarifas
4. Generar tickets de ingreso/salida
5. Dashboard con estadísticas
6. Sistema de exportación a Excel
7. Búsqueda avanzada

## 💡 Notas Importantes

- Las contraseñas están hasheadas usando `password_hash()` de PHP
- Para generar un nuevo hash de contraseña, usa:
  ```php
  echo password_hash('tu_contraseña', PASSWORD_DEFAULT);
  ```
- Los mensajes de error se muestran con animación y formato mejorado
- El sistema verifica automáticamente si el usuario ya está logueado
- La recuperación de contraseña requiere que el usuario exista en la base de datos

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
Verifica que:
- MySQL esté ejecutándose
- La base de datos `parqueadero_vale` exista
- Las credenciales en `db/conexion.php` sean correctas

### Error de login
Verifica que:
- El usuario exista en la base de datos
- La contraseña sea correcta
- La sesión de PHP esté habilitada

### Error de recuperación de contraseña
Verifica que:
- El usuario exista en la base de datos
- Las contraseñas coincidan
- La contraseña tenga al menos 6 caracteres

## 📞 Soporte

Si encuentras algún problema, verifica:
1. La configuración de la base de datos
2. Los permisos de los archivos PHP
3. Los logs de errores de PHP
4. La configuración de sesiones en php.ini

## 📄 Licencia

Sistema desarrollado para gestión de parqueadero de motocicletas.

---

**Versión:** 1.0  
**Última actualización:** 2024

