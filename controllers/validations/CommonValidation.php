<?php
class CommonValidation
{
    /**
     * Validacion para determinar si una fecha cumple con un formato aceptado antes de realizar su conversion al formato soportado por la base de datos
     * @param string $date fecha a ser evaluada
     * @return bool flag que indica si la fecha es inválida 
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

    public static function isInvalidInteger($id): bool {
        return !(is_int($id) && $id > 0);
    }

    public static function hasInvalidLength($cadena, int $lenght): bool {
        return empty($cadena) || strlen($cadena) > $lenght;
    }

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
