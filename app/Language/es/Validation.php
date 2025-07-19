<?php
/**
 * Traducciones de Validación en Español
 * Crear: app/Language/es/Validation.php
 */

return [
    // Core Messages
    'noRuleSets'      => 'No hay conjuntos de reglas especificados en la configuración de Validación.',
    'ruleNotFound'    => '"{0}" no es una regla válida.',
    'groupNotFound'   => '"{0}" no es un grupo de reglas de validación.',
    'groupNotArray'   => 'El grupo de reglas "{0}" debe ser un array.',
    'invalidTemplate' => '"{0}" no es una plantilla de Validación válida.',

    // Rule Messages
    'alpha'                 => 'El campo {field} solo puede contener caracteres alfabéticos.',
    'alpha_dash'            => 'El campo {field} solo puede contener caracteres alfanuméricos, guiones bajos y guiones.',
    'alpha_numeric'         => 'El campo {field} solo puede contener caracteres alfanuméricos.',
    'alpha_numeric_punct'   => 'El campo {field} solo puede contener caracteres alfanuméricos, espacios y ~ ! # $ % & * - _ + = | : . caracteres de puntuación.',
    'alpha_numeric_space'   => 'El campo {field} solo puede contener caracteres alfanuméricos y espacios.',
    'alpha_space'           => 'El campo {field} solo puede contener caracteres alfabéticos y espacios.',
    'decimal'               => 'El campo {field} debe contener un número decimal.',
    'differs'               => 'El campo {field} debe diferir del campo {param}.',
    'equals'                => 'El campo {field} debe ser exactamente: {param}.',
    'exact_length'          => 'El campo {field} debe tener exactamente {param} caracteres de longitud.',
    'greater_than'          => 'El campo {field} debe contener un número mayor que {param}.',
    'greater_than_equal_to' => 'El campo {field} debe contener un número mayor o igual a {param}.',
    'hex'                   => 'El campo {field} solo puede contener caracteres hexadecimales.',
    'in_list'               => 'El campo {field} debe ser uno de: {param}.',
    'integer'               => 'El campo {field} debe contener un entero.',
    'is_natural'            => 'El campo {field} solo debe contener dígitos.',
    'is_natural_no_zero'    => 'El campo {field} solo debe contener dígitos y debe ser mayor que cero.',
    'is_not_unique'         => 'El campo {field} debe contener un valor que existe previamente en la base de datos.',
    'is_unique'             => 'El campo {field} debe contener un valor único.',
    'less_than'             => 'El campo {field} debe contener un número menor que {param}.',
    'less_than_equal_to'    => 'El campo {field} debe contener un número menor o igual a {param}.',
    'matches'               => 'El campo {field} no coincide con el campo {param}.',
    'max_length'            => 'El campo {field} no puede exceder {param} caracteres de longitud.',
    'min_length'            => 'El campo {field} debe tener al menos {param} caracteres de longitud.',
    'not_equals'            => 'El campo {field} no puede ser: {param}.',
    'not_in_list'           => 'El campo {field} no debe ser uno de: {param}.',
    'numeric'               => 'El campo {field} debe contener solo números.',
    'regex_match'           => 'El campo {field} no tiene el formato correcto.',
    'required'              => 'El campo {field} es obligatorio.',
    'required_with'         => 'El campo {field} es obligatorio cuando {param} está presente.',
    'required_without'      => 'El campo {field} es obligatorio cuando {param} no está presente.',
    'string'                => 'El campo {field} debe ser una cadena válida.',
    'timezone'              => 'El campo {field} debe ser una zona horaria válida.',
    'valid_base64'          => 'El campo {field} debe ser una cadena base64 válida.',
    'valid_email'           => 'El campo {field} debe contener una dirección de correo electrónico válida.',
    'valid_emails'          => 'El campo {field} debe contener todas las direcciones de correo electrónico válidas.',
    'valid_ip'              => 'El campo {field} debe contener una IP válida.',
    'valid_url'             => 'El campo {field} debe contener una URL válida.',
    'valid_url_strict'      => 'El campo {field} debe contener una URL válida.',
    'valid_date'            => 'El campo {field} debe contener una fecha válida.',
    'valid_json'            => 'El campo {field} debe contener JSON válido.',

    // Credit Cards
    'valid_cc_num' => 'El campo {field} no parece ser un número de tarjeta de crédito válido.',

    // Files
    'uploaded' => 'El campo {field} no es un archivo subido válido.',
    'max_size' => 'El campo {field} es demasiado grande.',
    'is_image' => 'El campo {field} no es un archivo de imagen subido válido.',
    'mime_in'  => 'El campo {field} no tiene un tipo mime válido.',
    'ext_in'   => 'El campo {field} no tiene una extensión de archivo válida.',
    'max_dims' => 'El campo {field} no es una imagen, o es demasiado ancha o alta.',
];