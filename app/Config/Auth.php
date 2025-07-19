<?php

declare(strict_types=1);

namespace Config;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Shield\Config\Auth as ShieldAuth; // ← ESTE ES EL CORRECTO
use CodeIgniter\Shield\Authentication\Actions\ActionInterface;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Authentication\Authenticators\HmacSha256;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Passwords\CompositionValidator;
use CodeIgniter\Shield\Authentication\Passwords\DictionaryValidator;
use CodeIgniter\Shield\Authentication\Passwords\NothingPersonalValidator;
use CodeIgniter\Shield\Authentication\Passwords\PwnedValidator;
use CodeIgniter\Shield\Authentication\Passwords\ValidatorInterface;
use CodeIgniter\Shield\Models\UserModel;


class Auth extends ShieldAuth
{
 // Constants for Record Login Attempts. Do not change.
    public const RECORD_LOGIN_ATTEMPT_NONE    = 0; // Do not record at all
    public const RECORD_LOGIN_ATTEMPT_FAILURE = 1; // Record only failures
    public const RECORD_LOGIN_ATTEMPT_ALL     = 2; // Record all login attempts
    

 public array $views = [
        'login'                       => 'auth/login',
        'register'                    => 'auth/register',
        'layout'                      => 'layouts/auth',
        'action_email_2fa'            => '\CodeIgniter\Shield\Views\email_2fa_show',
        'action_email_2fa_verify'     => '\CodeIgniter\Shield\Views\email_2fa_verify',
        'action_email_2fa_email'      => '\CodeIgniter\Shield\Views\Email\email_2fa_email',
        'action_email_activate_show'  => '\CodeIgniter\Shield\Views\email_activate_show',
        'action_email_activate_email' => '\CodeIgniter\Shield\Views\Email\email_activate_email',
        'magic-link-login'            => 'auth/magic_link_form',
        'magic-link-message'          => '\CodeIgniter\Shield\Views\magic_link_message',
        'magic-link-email'            => '\CodeIgniter\Shield\Views\Email\magic_link_email',
    ];

    public array $redirects = [
        'register' => '/dashboard',
        'login'    => '/dashboard', 
        'logout'   => '/login',
    ];


    public array $authenticators = [
        'tokens'  => AccessTokens::class,
        'session' => Session::class,
        'hmac'    => HmacSha256::class,
        // 'jwt'     => JWT::class,
    ];

    public string $defaultAuthenticator = 'session';


    public array $authenticationChain = [
        'session',
        'tokens',
        'hmac',
        // 'jwt',
    ];
    public bool $allowRegistration = true;

    public bool $recordActiveDate = true;

    public bool $allowMagicLinkLogins = true;

    public int $magicLinkLifetime = HOUR;

        public array $sessionConfig = [
        'field'              => 'user',
        'allowRemembering'   => true,
        'rememberCookieName' => 'remember',
        'rememberLength'     => 30 * DAY,
    ];

    public array $usernameValidationRules = [
        'label' => 'Auth.username',
        'rules' => [
            'required',
            'max_length[30]',
            'min_length[3]',
            'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
        ],
    ];
    
    public array $emailValidationRules = [
        'label' => 'Auth.email',
        'rules' => [
            'required',
            'max_length[254]',
            'valid_email',
        ],
    ];
    
    public int $minimumPasswordStrength = 0;


    public array $passwordValidators = [
        CompositionValidator::class,
        NothingPersonalValidator::class,
        DictionaryValidator::class,
        // PwnedValidator::class,
    ];

    public array $validFields = [
        'email',
        // 'username', // ← DESACTIVADO
    ];


    public string $hashAlgorithm = PASSWORD_DEFAULT;

    public array $tables = [
        'users'             => 'users',
        'identities'        => 'auth_identities',
        'logins'            => 'auth_logins',
        'token_logins'      => 'auth_token_logins',
        'remember_tokens'   => 'auth_remember_tokens',
        'groups_users'      => 'auth_groups_users',
        'permissions_users' => 'auth_permissions_users',
    ];


    public string $userProvider = \App\Models\UserModel::class;

    /**
     * Valid Fields - SOLO EMAIL (quitar username)
     */

    /**
     * ============================================================================
     * ACCIONES - DEJAR VACÍO PARA EVITAR PROBLEMAS
     * ============================================================================
     */

    /**
     * ============================================================================
     * REGLAS DE VALIDACIÓN MÍNIMAS
     * ============================================================================
     */
    
    /**
     * Username validation rules - VACÍO
     */


    /**
     * Email validation rules
     */

    /**
     * Password validation rules
     */
    public array $passwordValidationRules = [
        'required',
        'min_length[8]',
        'max_length[255]',
    ];

    /**
     * ============================================================================
     * CONFIGURACIÓN BÁSICA
     * ============================================================================
     */
    
    public bool $requireEmailConfirmation = false;

    /**
     * ============================================================================
     * REDIRECCIONES
     * ============================================================================
     */

    public array $actions = [
        'register' => null,
        'login'    => null,

    ];

    /**
     * ============================================================================
     * MÉTODO DE REDIRECCIÓN PERSONALIZADA
     * ============================================================================
     */
    public function loginRedirect(): string
    {
        if (auth()->loggedIn()) {
            $user = auth()->user();
            
            if ($user->inGroup('admin', 'superadmin')) {
                return site_url('/admin/dashboard');
            }
            
            if ($user->inGroup('cliente')) {
                return site_url('/cliente/dashboard');
            }
        }
        
        return site_url('/dashboard');
    }

    public function registerRedirect(): string
    {
        return '/login';
    }
}