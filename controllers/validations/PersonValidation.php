<?php
class PersonValidation {
   
    public static function validateNamesLastNameType($cadena): bool {
    $trimmedName = trim($cadena);

    if (empty($trimmedName)) {
        return true;
    }

    $parts = explode(' ', $trimmedName);

    foreach ($parts as $part) {
        if (!ctype_alpha($part)) {
            return true;
        }
    }

    return false;
    }

    public static function validateIdDataType($id): bool {
        return !(is_numeric($id) && $id > 0);
    }
}
