<?php

/**
 * Agrupación de los permisos (columnas type y type_description de la tabla permissions).
 * Son enums: un grupo nuevo se agrega aquí y requiere volver a crear la base.
 */
return [
    'type_model' => [
        'Categories' => 'Categories',
        'Products' => 'Products',
        'Users' => 'Users',
        'Roles' => 'Roles',
    ],
    'type_description' => [
        'Categorias' => 'Categorias',
        'Productos' => 'Productos',
        'Usuarios' => 'Usuarios',
        'Roles' => 'Roles',
    ],
];
