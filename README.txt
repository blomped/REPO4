GESTIÓN DE ARTÍCULOS — PHP + MYSQL

ARCHIVOS
- index.php: interfaz y operaciones CRUD (crear, listar, buscar, editar y eliminar).
- config.php: conexión MySQL y creación automática de la tabla si no existe.
- styles.css: diseño adaptable con fondo abstracto y botones en degradados rojos.

INSTALACIÓN
1. Sube los tres archivos PHP/CSS a una carpeta de tu hosting compatible con PHP.
2. Comprueba que el hosting permita conexiones MySQL y que las credenciales sean correctas.
3. Abre index.php desde el navegador.
4. Al cargar la aplicación, config.php intentará crear la tabla `articulos` si aún no existe.

BASE DE DATOS CONFIGURADA
Host: mysql-bomboclatsito.alwaysdata.net
Base de datos: bomboclatsito_articulosgit
Usuario: bomboclatsito

CAMPOS
- nombre
- marca
- unidad
- ubicacion

SEGURIDAD
Las consultas usan PDO y sentencias preparadas. Las acciones de formulario incluyen un token CSRF y los valores se escapan al mostrarse en HTML. Para producción, usa HTTPS y evita publicar las credenciales en repositorios públicos. Si la contraseña real se compartió públicamente, cámbiala en el panel del proveedor y actualiza config.php.
