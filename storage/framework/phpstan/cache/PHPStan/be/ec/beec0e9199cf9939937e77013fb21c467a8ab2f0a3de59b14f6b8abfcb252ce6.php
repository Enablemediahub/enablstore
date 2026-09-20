<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\enablstore\app\Models\Tenant.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Tenant
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.6-8.2.12-b1656beeae5bebd97abefc2245c81713f7482765f7470ad0c893df7dd4ecf076',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Tenant',
        'filename' => 'C:/xampp/htdocs/enablstore/app/Models/Tenant.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Tenant',
    'shortName' => 'Tenant',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 12,
    'endLine' => 45,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Stancl\\Tenancy\\Database\\Models\\Tenant',
    'implementsClassNames' => 
    array (
      0 => 'Stancl\\Tenancy\\Contracts\\TenantWithDatabase',
    ),
    'traitClassNames' => 
    array (
      0 => 'Stancl\\Tenancy\\Database\\Concerns\\HasDatabase',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\Tenant',
        'implementingClassName' => 'App\\Models\\Tenant',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'id\', \'name\', \'slug\', \'email\', \'phone\', \'status\', \'data\']',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 24,
            'startTokenPos' => 64,
            'startFilePos' => 377,
            'endTokenPos' => 87,
            'endFilePos' => 497,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 24,
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
        'declaringClassName' => 'App\\Models\\Tenant',
        'implementingClassName' => 'App\\Models\\Tenant',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'data\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 26,
            'endLine' => 28,
            'startTokenPos' => 96,
            'startFilePos' => 524,
            'endTokenPos' => 105,
            'endFilePos' => 557,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 26,
        'endLine' => 28,
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
      'subscriptions' => 
      array (
        'name' => 'subscriptions',
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
 * @return HasMany<Subscription, $this>
 */',
        'startLine' => 33,
        'endLine' => 36,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Tenant',
        'implementingClassName' => 'App\\Models\\Tenant',
        'currentClassName' => 'App\\Models\\Tenant',
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
 * @return HasMany<Payment, $this>
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
        'declaringClassName' => 'App\\Models\\Tenant',
        'implementingClassName' => 'App\\Models\\Tenant',
        'currentClassName' => 'App\\Models\\Tenant',
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