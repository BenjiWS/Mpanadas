<?php

/**
 * Agrupación de los permisos (columnas type y type_description de la tabla permissions).
 * Son enums: un grupo nuevo se agrega aquí y requiere volver a crear la base.
 */
return [
    'type_model' => [
        'Categories' => 'Categories',
        'Products' => 'Products',
        'Ingredients' => 'Ingredients',
        'Combos' => 'Combos',
        'Offers' => 'Offers',
        'Users' => 'Users',
        'Roles' => 'Roles',
    ],
    'type_description' => [
        'Categorias' => 'Categorias',
        'Productos' => 'Productos',
        'Ingredientes' => 'Ingredientes',
        'Combos' => 'Combos',
        'Ofertas' => 'Ofertas',
        'Usuarios' => 'Usuarios',
        'Roles' => 'Roles',
    ],
    // Nombre con tildes que se muestra para cada grupo (los valores del enum van sin tildes).
    'type_labels' => [
        'Categories' => 'Categorías',
        'Products' => 'Productos',
        'Ingredients' => 'Ingredientes',
        'Combos' => 'Combos',
        'Offers' => 'Ofertas',
        'Users' => 'Usuarios',
        'Roles' => 'Roles',
    ],
];
