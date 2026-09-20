<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\enablstore\app\Models\Product.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Product
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.6-8.2.12-11464f72a5394bfa9dcfa4aeabd40bac707c776b77d707f7fc6198cd43c2da3c',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Product',
        'filename' => 'C:/xampp/htdocs/enablstore/app/Models/Product.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Product',
    'shortName' => 'Product',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 11,
    'endLine' => 48,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\Product',
        'implementingClassName' => 'App\\Models\\Product',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'category_id\', \'name\', \'slug\', \'sku\', \'barcode\', \'price_minor\', \'cost_minor\', \'currency\', \'description\', \'image_path\', \'is_active\']',
          'attributes' => 
          array (
            'startLine' => 13,
            'endLine' => 25,
            'startTokenPos' => 46,
            'startFilePos' => 258,
            'endTokenPos' => 81,
            'endFilePos' => 484,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 13,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\Product',
        'implementingClassName' => 'App\\Models\\Product',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'price_minor\' => \'integer\', \'cost_minor\' => \'integer\', \'is_active\' => \'boolean\']',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 31,
            'startTokenPos' => 90,
            'startFilePos' => 511,
            'endTokenPos' => 113,
            'endFilePos' => 622,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 31,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'category' => 
      array (
        'name' => 'category',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<Category, $this>
 */',
        'startLine' => 36,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Product',
        'implementingClassName' => 'App\\Models\\Product',
        'currentClassName' => 'App\\Models\\Product',
        'aliasName' => NULL,
      ),
      'inventoryStock' => 
      array (
        'name' => 'inventoryStock',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return HasOne<InventoryStock, $this>
 */',
        'startLine' => 44,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Product',
        'implementingClassName' => 'App\\Models\\Product',
        'currentClassName' => 'App\\Models\\Product',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));