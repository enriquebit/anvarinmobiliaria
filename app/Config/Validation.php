<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
    /**
     * Validaciones personalizadas para registro
     * OVERRIDE Shield - Agregar campos de cliente inmobiliario
     */

        public array $login = [
        'email' => [
            'label' => 'Email',
            'rules' => [
                'required',
                'valid_email',
                // ❌ NO incluir 'is_unique' aquí - eso es para registro
            ],
            'errors' => [
                'required' => 'El email es obligatorio',
                'valid_email' => 'Debe ser un email válido',
            ]
        ],
        'password' => [
            'label' => 'Contraseña',
            'rules' => [
                'required',
            ],
            'errors' => [
                'required' => 'La contraseña es obligatoria',
            ]
        ],
    ];
    public array $registration = [
        // ✅ INFORMACIÓN PERSONAL BÁSICA
        'nombres' => [
            'label' => 'Nombres',
            'rules' => [
                'required',
                'min_length[2]',
                'max_length[100]',
                'regex_match[/^[a-zA-ZÀ-ÿ\s]+$/]',
            ],
            'errors' => [
                'required' => 'Los nombres son obligatorios',
                'min_length' => 'Los nombres deben tener al menos 2 caracteres',
                'max_length' => 'Los nombres no pueden exceder 100 caracteres',
                'regex_match' => 'Los nombres solo pueden contener letras, acentos y espacios',
            ]
        ],
        
        'apellido_paterno' => [
            'label' => 'Apellido Paterno',
            'rules' => [
                'required',
                'min_length[2]',
                'max_length[50]',
                'regex_match[/^[a-zA-ZÀ-ÿ\s]+$/]',
            ],
            'errors' => [
                'required' => 'El apellido paterno es obligatorio',
                'min_length' => 'El apellido paterno debe tener al menos 2 caracteres',
                'regex_match' => 'El apellido paterno solo puede contener letras y espacios',
            ]
        ],
        
        'apellido_materno' => [
            'label' => 'Apellido Materno',
            'rules' => [
                'required',
                'min_length[2]',
                'max_length[50]',
                'regex_match[/^[a-zA-ZÀ-ÿ\s]+$/]',
            ],
            'errors' => [
                'required' => 'El apellido materno es obligatorio',
                'min_length' => 'El apellido materno debe tener al menos 2 caracteres',
                'regex_match' => 'El apellido materno solo puede contener letras y espacios',
            ]
        ],
        
        'telefono' => [
            'label' => 'Teléfono',
            'rules' => [
                'required',
                'min_length[10]',
                'max_length[15]',
                'regex_match[/^[0-9]+$/]',
            ],
            'errors' => [
                'required' => 'El teléfono es obligatorio',
                'min_length' => 'El teléfono debe tener al menos 10 dígitos',
                'max_length' => 'El teléfono no puede tener más de 15 dígitos',
                'regex_match' => 'El teléfono solo puede contener números',
            ]
        ],
        
        // ✅ INFORMACIÓN DE ACCESO
        'email' => [
            'label' => 'Email',
            'rules' => [
                'required',
                'max_length[254]',
                'valid_email',
                'is_unique[auth_identities.secret]',
            ],
            'errors' => [
                'required' => 'El email es obligatorio',
                'max_length' => 'El email es demasiado largo',
                'valid_email' => 'Debe ser un email válido',
                'is_unique' => 'Este email ya está registrado',
            ]
        ],
        
        'password' => [
            'label' => 'Contraseña',
            'rules' => [
                'required',
                'min_length[8]',
                'max_length[255]',
            ],
            'errors' => [
                'required' => 'La contraseña es obligatoria',
                'min_length' => 'La contraseña debe tener al menos 8 caracteres',
                'max_length' => 'La contraseña es demasiado larga',
            ]
        ],
        
        'password_confirm' => [
            'label' => 'Confirmar Contraseña',
            'rules' => [
                'required',
                'matches[password]',
            ],
            'errors' => [
                'required' => 'Debes confirmar la contraseña',
                'matches' => 'Las contraseñas no coinciden',
            ]
        ],
        
        'terms' => [
            'label' => 'Términos y Condiciones',
            'rules' => [
                'required',
            ],
            'errors' => [
                'required' => 'Debes aceptar los términos y condiciones',
            ]
        ],
    ];
}
