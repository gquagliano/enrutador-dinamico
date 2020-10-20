<?php
/**
 * Controlador predeterminado.
 */

namespace clasesPublicas;
    
class predeterminado {
    public function inicio() {
        echo '¡Hola!';
    }

    public function error($enrutador) {
        echo 'Error '.$enrutador->obtenerCodigoError();
    }
}