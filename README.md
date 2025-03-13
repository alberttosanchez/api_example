# LEEME (README) #

Esta es la documentación para el proyecto ***Sumate*** para el desarrollo de la API que controla los recursos de **back-end.**

En el siguiente enlace encontrará la [documentación](#) detallada de cada:
* Función
* Constante
* Clase
* Interfaz
* Rasgo
* constante de clase
* Propiedad
* Método



### Como actualizar la documentación ###

Para actualizar la documentación se ha utilizado el proyecto de codigo abierto **Docker**, tal como lo sugiere
la documentacion oficial de [PHPDocumentor](https://docs.phpdoc.org/), luego de instalar a docker, abra una consola y ubiquese en el directorio de proyecto donde desea incluir o actualizar la documentación y ejecute el siguiente comando:

```
    docker run --rm -v "$(pwd):/data" "phpdoc/phpdoc:3" --ignore ./vendor/
```

 Docker buscar la libreria phpdoc si no la encuentra la descargará y luego creara un directorio con los archivos de la cache de phpdoc y luego creara la documentacion dentro de esta carpeta.

 ```
    --ignore ./vendor/
 ```
 El comando anterior indica que al generar la documentacion se debe ignorar el directorio vendor, este directorio contiene las depedencias instaladas por **[composer.](https://getcomposer.org/)** Puede ignorar otras carpetar que considere necesarias.

 Se añadió un script para composer para ejecutar el comando anterior en solo dos palabras:

 ```
    composer phpdoc
 ```
