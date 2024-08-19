<?php
class RecordNotFoundException extends Exception {
    public function __construct($message = "Registro no encontrado", $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}