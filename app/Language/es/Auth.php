<?php
/**
 * Traducciones de Shield en Español
 * Crear: app/Language/es/Auth.php
 */

return [
    // Errores de autenticación
    'invalidCredentials'      => 'Las credenciales proporcionadas no son válidas.',
    'tooManyCredentials'     => 'Demasiados intentos de inicio de sesión. Inténtalo de nuevo en {time} segundos.',
    'bannedUser'             => 'Tu cuenta ha sido suspendida. Contacta al administrador.',
    'logOutSuccess'          => 'Has cerrado sesión exitosamente.',
    'magicLinkDetails'       => 'Te acabamos de enviar un correo con un enlace de inicio de sesión. Solo es válido por {minutes} minutos.',
    'magicLinkSubject'       => 'Tu enlace de inicio de sesión',
    'magicTokenNotFound'     => 'No se puede verificar el enlace.',
    'magicLinkExpired'       => 'El enlace ha expirado.',
    'checkYourEmail'         => 'Revisa tu correo electrónico.',
    'enterEmailForMagicLink' => 'Ingresa tu dirección de correo electrónico para recibir un enlace de inicio de sesión.',

    // Registro y activación
    'registerSuccess'        => 'Cuenta creada exitosamente. Revisa tu correo para activarla.',
    'registerClosed'         => 'El registro está temporalmente cerrado.',
    'activateNoUser'         => 'No se puede encontrar un usuario con esas credenciales.',
    'activateSubject'        => 'Activa tu cuenta',
    'activateSuccess'        => '¡Cuenta activada exitosamente! Ya puedes iniciar sesión.',
    'activationBlocked'      => 'Debes activar tu cuenta antes de iniciar sesión.',

    // Contraseñas
    'errorPasswordLength'    => 'La contraseña debe tener al menos {min} caracteres.',
    'suggestPasswordLength'  => 'Las frases de contraseña - hasta 255 caracteres - son más seguras y fáciles de recordar.',
    'errorPasswordCommon'    => 'La contraseña no debe ser una contraseña común.',
    'suggestPasswordCommon'  => 'La contraseña fue verificada contra más de 65,000 contraseñas comúnmente usadas o contraseñas que han sido filtradas por ataques.',
    'errorPasswordPersonal'  => 'La contraseña no puede contener información personal rehashed.',
    'suggestPasswordPersonal'=> 'Las variaciones de tu dirección de correo electrónico o nombre de usuario no deben usarse para contraseñas.',
    'errorPasswordTooSimilar'=> 'La contraseña es demasiado similar al nombre de usuario.',
    'suggestPasswordTooSimilar' => 'No uses partes de tu nombre de usuario en tu contraseña.',
    'errorPasswordPwned'     => 'La contraseña {password} ha sido expuesta debido a una violación de datos y ha sido vista {count, number} veces en {count, plural, one {una contraseña} other {contraseñas}} comprometidas.',
    'suggestPasswordPwned'   => '{password} nunca debe usarse como contraseña. Si la estás usando en algún lugar, cámbiala inmediatamente.',
    'errorPasswordEmpty'     => 'Se requiere una contraseña.',
    'errorPasswordTooLongBytes' => 'La contraseña no puede exceder {maxBytes} bytes de longitud.',
    'passwordChangeSuccess'  => 'Contraseña cambiada exitosamente',
    'userDoesNotExist'       => 'La contraseña no fue cambiada. El usuario no existe',
    'resetTokenExpired'      => 'El enlace de restablecimiento ha expirado.',

    // Verificación de email
    'emailActivateSubject'   => 'Activa tu cuenta',
    'emailActivateBody'      => 'Haz clic en el enlace de abajo para activar tu cuenta:',

    // 2FA
    'email2FASubject'        => 'Tu código de autenticación',
    'email2FABody'           => 'Tu código de autenticación es:',
    'invalid2FAToken'        => 'El código era incorrecto.',
    'need2FA'                => 'Debes completar la verificación de dos factores.',
    'needVerification'       => 'Revisa tu correo para completar la activación de cuenta.',

    // Permisos
    'notEnoughPrivilege'     => 'No tienes suficientes permisos.',
    'noPermission'           => 'No tienes permisos para realizar esa acción.',

    // Botones y enlaces
    'forgotPassword'         => '¿Olvidaste tu contraseña?',
    'enterEmailForInstructions' => 'Ingresa tu correo y te enviaremos instrucciones para restablecer tu contraseña.',
    'emailEnterCode'         => 'Confirma tu correo electrónico',
    'emailConfirmBody'       => 'Ingresa el código que acabamos de enviar a tu dirección de correo electrónico.',
    'newPassword'            => 'Nueva contraseña',
    'newPasswordRepeat'      => 'Repetir nueva contraseña',
];