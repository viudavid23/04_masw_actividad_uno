<?php
class LanguageValidation {
    
    const NAME_MATCH_REGEXP = '/^[\p{L}]+(?:[\s]*[\p{L}]+)*$/u';
    const ISO_CODE_MATCH_REGEXP = '/^([A-Z]{2,3}|\d{3})$/';
    const COMPONENT_AUDIO = 'audio';
    const COMPONENT_SUBTITLE = 'subtitle';

    public static function validateName($name): bool {
        return empty($name) || !preg_match(self::NAME_MATCH_REGEXP, $name);
    }

    public static function validateIsoCode($isoCode): bool {
        return empty($isoCode) || !preg_match(self::ISO_CODE_MATCH_REGEXP, $isoCode);
    }

    public static function isInvalidIdDataType($id): bool {
        return !is_numeric($id) || $id <= 0;
    }

    public static function validateLanguageComponentsType($value): bool {
        return empty($value) || !is_array($value);
    }

    public static function validateLanguageComponentsKey($key): bool {
        return !isset($key[self::COMPONENT_AUDIO]) || !isset($key[self::COMPONENT_SUBTITLE]);
    }

    public static function validateLanguageComponentsValue($value): bool {
        return !in_array($value[self::COMPONENT_AUDIO], [0, 1]) || !in_array($value[self::COMPONENT_SUBTITLE], [0, 1]);
    }
}
