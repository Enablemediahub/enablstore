<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\enablstore\app\Models\Subscription.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Subscription
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.6-8.2.12-d36d1bb9ca1d0f76ce42a2a53f70a3cc7a5c38a284e070ee969dc0b1298b1d66',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Subscription',
        'filename' => 'C:/xampp/htdocs/enablstore/app/Models/Subscription.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Subscription',
    'shortName' => 'Subscription',
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
        'declaringClassName' => 'App\\Models\\Subscription',
        'implementingClassName' => 'App\\Models\\Subscription',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'tenant_id\', \'plan_id\', \'provider\', \'provider_reference\', \'status\', \'starts_at\', \'renews_at\', \'ends_at\', \'grace_ends_at\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 12,
            'endLine' => 23,
            'startTokenPos' => 41,
            'startFilePos' => 223,
            'endTokenPos' => 73,
            'endFilePos' => 454,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 12,
        'endLine' => 23,
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
        'declaringClassName' => 'App\\Models\\Subscription',
        'implementingClassName' => 'App\\Models\\Subscription',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'metadata\' => \'array\', \'starts_at\' => \'datetime\', \'renews_at\' => \'datetime\', \'ends_at\' => \'datetime\', \'grace_ends_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 31,
            'startTokenPos' => 82,
            'startFilePos' => 483,
            'endTokenPos' => 119,
            'endFilePos' => 668,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
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
      'tenant' => 
      array (
        'name' => 'tenant',
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
 * @return BelongsTo<Tenant, $this>
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
        'declaringClassName' => 'App\\Models\\Subscription',
        'implementingClassName' => 'App\\Models\\Subscription',
        'currentClassName' => 'App\\Models\\Subscription',
        'aliasName' => NULL,
      ),
      'plan' => 
      array (
        'name' => 'plan',
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
 * @return BelongsTo<Plan, $this>
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
        'declaringClassName' => 'App\\Models\\Subscription',
        'implementingClassName' => 'App\\Models\\Subscription',
        'currentClassName' => 'App\\Models\\Subscription',
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