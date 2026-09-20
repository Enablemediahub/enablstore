<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByPath.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\Tenancy\Middleware\InitializeTenancyByPath
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-59696cd63797d51ca15c684b2cbf9d7659a7ef96310389e52a198a19b66af9ca-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByPath.php',
      ),
    ),
    'namespace' => 'Stancl\\Tenancy\\Middleware',
    'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
    'shortName' => 'InitializeTenancyByPath',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 14,
    'endLine' => 47,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Stancl\\Tenancy\\Middleware\\IdentificationMiddleware',
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
      'onFail' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'name' => 'onFail',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
        'startColumn' => 5,
        'endColumn' => 26,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'tenancy' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'name' => 'tenancy',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var Tenancy */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 20,
        'startColumn' => 5,
        'endColumn' => 23,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'resolver' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'name' => 'resolver',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var PathTenantResolver */',
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 24,
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
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'tenancy' => 
          array (
            'name' => 'tenancy',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Stancl\\Tenancy\\Tenancy',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 33,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'resolver' => 
          array (
            'name' => 'resolver',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Stancl\\Tenancy\\Resolvers\\PathTenantResolver',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 51,
            'endColumn' => 78,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 25,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'aliasName' => NULL,
      ),
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
          'request' => 
          array (
            'name' => 'request',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Request',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 31,
            'endLine' => 31,
            'startColumn' => 28,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'next' => 
          array (
            'name' => 'next',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 31,
            'endLine' => 31,
            'startColumn' => 46,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 31,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByPath',
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