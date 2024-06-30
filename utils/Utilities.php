<?php
class Utilities
{

    /**
     * Funcion que permite cambiar el formato de una fecha
     * @param String $fechaSinFormato fecha a ser formateada
     * @param string $formato formato de la fecha. Se encuentran en la clase Constants.php
     * 
     * @return mixed $fechaFormateada
     */
    public static function changeDateFormat($fechaSinFormato, $formato)
    {

        $objetoFecha = new DateTime($fechaSinFormato);

        $fechaFormateada = $objetoFecha->format($formato);

        return $fechaFormateada;
    }

    /**
     * Funcion que permite convertir los caracteres de minusculas a mayusculas y viceversa incluyendo caracteres acentuados
     * @param int $option 0 para convertir de mayusculas a minusculas y 1 para convertir de minusculas a mayusculas
     * @param string $text cadena de texto a ser transformada
     * 
     * @return string cadena formateada
     */
    public static function convertCharacters($option, $text)
    {

        $uppercase = array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ');

        $lowerCase = array('á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ');

        return $option == 0 ? str_replace($uppercase, $lowerCase, strtolower($text)) :
            str_replace($lowerCase, $uppercase, strtolower($text));
    }
}
