<?php
class DirectorValidation {
   
    public static function validateStageNameType($cadena): bool {
        return !empty($cadena) && !preg_match('/^[\p{L}]+(?:[\s]*[\p{L}]+)*$/u', $cadena);
    }

    public static function hasInvalidLength($cadena): bool {
        return empty($cadena) || strlen($cadena) > 5000;
    }

    public static function validateHeightDataType($height): bool {
        $height = floatval($height);
        return !(is_numeric($height) && $height > 0);
    }

    public static function isInvalidIdDataType($id): bool {
        return !(is_numeric($id) && $id > 0);
    }
}