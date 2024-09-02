<?php
class Utilities
{

    /**
     * Función que permite cambiar el formato de una fecha
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
     * Función que permite convertir los caracteres de minusculas a mayusculas y viceversa incluyendo caracteres acentuados
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

    /**
     * Función que permite el seteo de un mensaje de error en una variable de sesion
     * @param string $message mensaje de error customizado
     */
    public static function setErrorMessage($message)
    {
        $_SESSION['error_message'] = $message;
    }

    /**
     * Función que permite el seteo de un mensaje de éxito en una variable de sesion
     * @param string $message mensaje de éxito customizado
     */
    public static function setSuccessMessage($message)
    {
        $_SESSION['success_message'] = $message;
    }

    /**
     * Función que permite el seteo de un mensaje de advertencia en una variable de sesion
     * @param string $message mensaje de advertencia customizado
     */
    public static function setWarningMessage($message)
    {
        $_SESSION['warning_message'] = $message;
    }

    /**
     * Función que permite identificar los elementos existentes y no existentes dentro de una lista de
     * ids a actualizar discriminados por un estado
     * @param array $newObjectIds arreglo de ids nuevos
     * @param array $objectIdsSaved arreglo de ids actuales en la base de datos
     * 
     * @return array $objectToUpdate con los elementos a actualizar. La llave corresponde al id
     * del objeto y el valor a su nuevo estado
     */
    public static function setArrayToUpdate(array $newObjectIds, array $objectIdsSaved): array
    {
        $objectToUpdate = [];
        foreach ($objectIdsSaved as $currentObjectItem) {

            $objectToUpdate[$currentObjectItem] = in_array($currentObjectItem, $newObjectIds) ? 1 : 0;
        }

        return $objectToUpdate;
    }

    /**
     * Función para concatenar strings.
     */
    public static function concatStrings() {
        $args = func_get_args();
        $result = '';
    
        foreach ($args as $arg) {
            $result .= $arg;
        }
    
        return $result;
    }
}
