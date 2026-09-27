<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\enablstore\app\Models\Sale.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Sale
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.6-8.2.12-af9188314c8dd0d175d94422fa73159b7b65108980777d60204a519c3c86bbca',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Sale',
        'filename' => 'C:/xampp/htdocs/enablstore/app/Models/Sale.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Sale',
    'shortName' => 'Sale',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 53,
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
        'declaringClassName' => 'App\\Models\\Sale',
        'implementingClassName' => 'App\\Models\\Sale',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'transaction_uuid\', \'cashier_name\', \'customer_name\', \'customer_phone\', \'customer_email\', \'subtotal_minor\', \'discount_minor\', \'discount_type\', \'discount_reason\', \'total_minor\', \'currency\', \'payment_method\', \'source\', \'delivery_location\', \'status\', \'completed_at\']',
          'attributes' => 
          array (
            'startLine' => 12,
            'endLine' => 29,
            'startTokenPos' => 41,
            'startFilePos' => 202,
            'endTokenPos' => 91,
            'endFilePos' => 599,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 12,
        'endLine' => 29,
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
        'declaringClassName' => 'App\\Models\\Sale',
        'implementingClassName' => 'App\\Models\\Sale',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'subtotal_minor\' => \'integer\', \'discount_minor\' => \'integer\', \'total_minor\' => \'integer\', \'completed_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 31,
            'endLine' => 36,
            'startTokenPos' => 100,
            'startFilePos' => 626,
            'endTokenPos' => 130,
            'endFilePos' => 784,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 31,
        'endLine' => 36,
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
      'items' => 
      array (
        'name' => 'items',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return HasMany<SaleItem, $this>
 */',
        'startLine' => 41,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Sale',
        'implementingClassName' => 'App\\Models\\Sale',
        'currentClassName' => 'App\\Models\\Sale',
        'aliasName' => NULL,
      ),
      'payments' => 
      array (
        'name' => 'payments',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return HasMany<SalePayment, $this>
 */',
        'startLine' => 49,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Sale',
        'implementingClassName' => 'App\\Models\\Sale',
        'currentClassName' => 'App\\Models\\Sale',
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