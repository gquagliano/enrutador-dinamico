Concepto de enrutador de solicitudes dinámico, que no requiere establecer las rutas previamente.

#### Modo de uso

- La única configuración que se requiere es modificar la ruta base en la última línea de `index.php`. Ejemplo, si la URL es `http://localhost/enrutador-dinamico/` la ruta base es `/enrutador-dinamico/`.
- Crear las clases públicas en el directorio `clases` (incluso subdirectorios).
- Cada clase debe residir en un archivo `.php` y contener una clase del mismo nombre que el archivo en el espacio de nombres `clasesPublicas\\subdirectorio`.
- Si el nombre presenta `-` se convertirán a nombres válidos de clase y espacio de la forma: `mi-clase` -> `miClase`.
- El método predeterminado de una clase será `inicio()`.
- El método predeterminado será `predeterminado::inicio()` en `clases/predeterminado.php`.
- El método en caso de error será `predeterminado::error()` en `clases/predeterminado.php`.

#### Ejemplos

**Ruta: `/clase/metodo/`**

Archivo: `clase.php`

Espacio: `clasesPublicas`

Clase: `clase`

Método: `metodo`

    <?php
    namespace clasesPublicas;
    class clase {
        public function metodo() {
            echo 'Hola!';
        }
    }

**Ruta: `controladores/mi-controlador/mi-metodo/`**

Archivo: `clases/controladores/mi-controlador.php`

Espacio: `clasesPublicas\controladores`

Clase: `miControlador`

Método: `miMetodo`

    <?php
    namespace clasesPublicas\controladores;
    class miControlador {
        public function miMetodo() {
            echo 'Hola II!';
        }
    }

**Ruta: `/clase/`**

Archivo: `clase.php`

Espacio: `clasesPublicas`

Clase: `clase`

Método: `inicio`

    <?php
    namespace clasesPublicas;
    class clase {
        public function inicio() {
            echo 'Hola III!';
        }
    }

#### Objetivo

El propósito de este repositorio es explorar el potencial y las implicaciones de seguridad de este mecanimsmo.

Si te parece útil, sentite libre de utilizarlo tal cual o integrarlo en otros proyectos.

De lo contrario, estoy dispuesto a aceptar que es basura siempre que el comentario sea acompañado de una explicación técnica que nos enriquezca a todos.

#### TODO

- Verbos. Lo haría configurable en cada método con comentarios de documentación.
- Redireccionamientos.
- Personalizar formato de URI (que no sea siempre `ruta/clase/metodo`).
