<?php
/**
 * Enrutador dinámico.
 */
class enrutador {
    private $base='';
    private $uri;
    private $ruta=null;
    private $clase=null;
    private $metodo=null;
    private $error=false;
    private $instancia=null;

    private $rutaClases=__DIR__.'/clases/';
    private $espacio='\\clasesPublicas\\';
    private $rutaInicio=__DIR__.'/clases/predeterminado.php';
    private $claseInicio='\\clasesPublicas\\predeterminado';
    private $metodoInicio='inicio';
    private $metodoError='error';

    const errorRutaInvalida=1;
    const errorArchivoNoEncontrado=2;
    const errorClaseNoEncontrada=3;
    const errorMetodoNoEncontrado=4;
    
    /**
     * Constructor.
     * @var string $base Ruta base.
     * @var string $uri URI a analizar.
     */
    function __construct($base=null,$uri=null) {
        $this->establecerUri($base,$uri);
    }

    /**
     * Establece la ruta a analizar.
     * @var string $base Ruta base.
     * @var string $uri URI a analizar.
     * @return enrutador
     */
    public function establecerUri($base,$uri) {
        $this->reestablecer();
        $this->base=$base;
        if($uri) {
            $this->uri=$uri;
            $this->analizar();
        }
        return $this;
    }

    /**
     * Reestablece los parámetros tras el análisis de una URI.
     * @var bool $todo Si es true, reestablecerá todos los parámetros. En caso contrario, solo los relacionados a la ejecución de la solicitud, preservando la URI.
     * @return enrutador
     */
    public function reestablecer($todo=true) {
        if($todo) $this->uri=null;
        $this->ruta=null;
        $this->clase=null;
        $this->metodo=null;
        $this->error=false;
        $this->instancia=null;
        return $this;
    }

    /**
     * Ejecuta la solicitud establecida. Devuelve el valor de retorno del método.
     * @return mixed
     */
    public function ejecutar() {
        //Clase por defecto y método de error
        if(!$this->clase||$this->error) {
            include_once($this->rutaInicio);
            $this->clase=$this->claseInicio;
            $this->metodo=$this->error?$this->metodoError:null;
        }

        //Método por defecto
        if(!$this->metodo) $this->metodo=$this->metodoInicio;

        //Se asume que las clases y métodos predeterminados existen, no se realiza otra validación
        $this->instancia=new $this->clase;
        return call_user_func_array([$this->instancia,$this->metodo],[$this]);    
    }

    /**
     * Devuelve la clase de usuario luego de ejecutar la solicitud.
     * @return mixed
     */
    public function obtenerInstancia() {
        return $this->instancia;
    }

    /**
     * Devuelve el código de error, o false.
     * @return mixed
     */
    public function obtenerCodigoError() {
        return $this->error;
    }

    /**
     * Analiza la ruta establecida.
     */
    protected function analizar() {
        $this->reestablecer(false);

        //Limpiar
        $this->uri=urldecode($this->uri);
        $this->uri=filter_var($this->uri,FILTER_UNSAFE_RAW,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);

        //Remover base
        $this->uri=substr($this->uri,strlen($this->base));

        //Separar la URL
        $uri=trim(trim(parse_url($this->uri)['path']),'/');

        //Solicitando el raíz
        if(!$this->uri) return;

        $partes=$this->separarRuta($uri);
        if($partes->dir) {
            //Con múltiples componentes (una o múltiples /), tomar el último elemento como método, el resto como clase
            $this->clase=$partes->dir;
            $this->metodo=$partes->base;
        } else {
            //Con un solo componente, tomar la base como clase
            $this->clase=$partes->base;
        }

        //Validar caracteres admitidos. Previamente se removieron todos los caracteres de control y ascii extendido para poder usar expresiones regulares.
        if(preg_match('/[^a-z0-9_\/-]/i',$this->clase)||preg_match('/[^a-z0-9_-]/i',$this->metodo)) {
            $this->error=self::errorRutaInvalida;
            return;
        }

        //Validar e incluir el archivo
        $this->ruta=$this->rutaClases.$this->clase.'.php';
        if(!file_exists($this->ruta)) {
            $this->error=self::errorArchivoNoEncontrado;
            return;
        }
        include_once($this->ruta);

        //Convertir nombres, por ejemplo mi-clase.php debe contener miClase
        $this->clase=$this->prepararNombre($this->clase); //aquí se mantienen las /
        $this->metodo=$this->prepararNombre($this->metodo); //aquí las / ya fueron excluídas en la validación del nombre de método

        //Separar ruta del nombre
        $espacio=$this->espacio;
        $partes=$this->separarRuta($this->clase);
        if($partes->dir) {
            //dir/clase -> \clasesPublicas\dir
            $espacio.=str_replace('/','\\',$partes->dir).'\\';
            $this->clase=$partes->base;
        }
        //Anteponer espacio de nombres
        $this->clase=$espacio.$this->clase;

        if(!class_exists($this->clase)) {
            $this->error=self::errorClaseNoEncontrada;
            return;
        }

        if(!method_exists($this->clase,$this->metodo?$this->metodo:$this->metodoInicio)) {
            $this->error=self::errorMetodoNoEncontrado;
            return;
        }
    }

    /**
     * Separa una ruta en directorio y nombre, equivalente a dirname() y basename(), pero aislado del sistema de archivos local.
     * @var string $ruta Ruta a procesar
     * @return object
     */
    protected function separarRuta($ruta) {
        $partes=explode('/',$ruta);
        return (object)[
            'base'=>array_pop($partes),
            'dir'=>implode('/',$partes)
        ];
    }

    /**
     * Prepara un nombre de clase o método. El valor provisto se asume ya sanitizado.
     * @var string $nombre Nombre a procesar
     * @return string
     */
    protected function prepararNombre($nombre) {
        $partes=explode('-',strtolower($nombre));
        $resultado=array_shift($partes);
        foreach($partes as $parte) $resultado.=ucfirst($parte);
        return $resultado;
    }
}

//Ejecutar
(new enrutador('/enrutador-dinamico/enrutador-dinamico/',$_SERVER['REQUEST_URI']))->ejecutar();