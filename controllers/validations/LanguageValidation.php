<?php
class LanguageValidation {
    
    public static function validateName($name): bool {
        return empty($name) || !preg_match('/^[\p{L}]+(?:[\s]*[\p{L}]+)*$/u', $name);
    }

    public static function validateIsoCode($isoCode): bool {
        return empty($isoCode) || !preg_match('/^([A-Z]{2,3}|\d{3})$/', $isoCode);
    }

    public static function validateIdDataType($id): bool {
        return !is_numeric($id) || $id <= 0;
    }
}
