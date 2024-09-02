<?php
class CommonValidation
{
    /**
     * Valida si una fecha cumple con un formato aceptado antes de realizar su conversion al formato soportado por la base de datos.
     * @param string $date fecha a ser evaluada.
     * @return bool TRUE si la fecha es inválida y FALSE si la fecha es válida.
     */
    public static function isInvalidDate($date, $formats = ['Y-m-d', 'd/m/Y', 'm-d-Y', 'Y-m-d H:i:s'])
    {

        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida si una variable es un número entero positivo.
     * @param mixed $id ID a ser validado.
     * @return bool TRUE si la variable es un número entero positivo y FALSE si no es un número entero positivo.
     */
    public static function isInvalidInteger($id): bool {
        return !(is_numeric($id) && $id > 0);
    }

    /**
     * Valida si una cadena tiene una longitud válida.
     * @param mixed $cadena Cadena de texto
     * @param int $length Longitud de la cadena
     * @return bool TRUE si la candena esta vacia o si la longitud es mayor a $length y FALSE si la cadena no está vacia o si la longitud es menor a $length
     */
    public static function hasInvalidLength($cadena, int $lenght): bool {
        return empty($cadena) || strlen($cadena) > $lenght;
    }

    /**
     * Valida si una lista tiene numeros enteros positivos
     * @param array $list Lista de elementos
     * @return bool TRUE si la lista está vacia o contiene elementos que no sean numéricos o menos a cero y FALSE si la lista no está vacia o contiene elementos númericos mayores a cero
     */
    public static function isInvalidIntegerList($list): bool {

        if (empty($list)) {
            return true;
        }

        foreach($list as $item){
            if (!is_numeric($item) || $item <= 0){
                return true;
            }
        }

        return false;
    }
}
