<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByRequestData.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\Tenancy\Middleware\InitializeTenancyByRequestData
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-0e2dbe95b5b569fd9dcd207422261c6ff7a1726f1dc822dc4bc3c17e6e26232b-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByRequestData.php',
      ),
    ),
    'namespace' => 'Stancl\\Tenancy\\Middleware',
    'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
    'shortName' => 'InitializeTenancyByRequestData',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 13,
    'endLine' => 63,
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
      'header' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'name' => 'header',
        'modifiers' => 17,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'X-Tenant\'',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 16,
            'startTokenPos' => 60,
            'startFilePos' => 371,
            'endTokenPos' => 60,
            'endFilePos' => 380,
          ),
        ),
        'docComment' => '/** @var string|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'queryParameter' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'name' => 'queryParameter',
        'modifiers' => 17,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'tenant\'',
          'attributes' => 
          array (
            'startLine' => 19,
            'endLine' => 19,
            'startTokenPos' => 73,
            'startFilePos' => 448,
            'endTokenPos' => 73,
            'endFilePos' => 455,
          ),
        ),
        'docComment' => '/** @var string|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 19,
        'startColumn' => 5,
        'endColumn' => 45,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'onFail' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'name' => 'onFail',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 22,
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
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'name' => 'tenancy',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var Tenancy */',
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 25,
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
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'name' => 'resolver',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var TenantResolver */',
        'attributes' => 
        array (
        ),
        'startLine' => 28,
        'endLine' => 28,
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
            'startLine' => 30,
            'endLine' => 30,
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
                'name' => 'Stancl\\Tenancy\\Resolvers\\RequestDataTenantResolver',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 30,
            'endLine' => 30,
            'startColumn' => 51,
            'endColumn' => 85,
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
        'startLine' => 30,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
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
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 43,
            'endLine' => 43,
            'startColumn' => 28,
            'endColumn' => 35,
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
            'startLine' => 43,
            'endLine' => 43,
            'startColumn' => 38,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Handle an incoming request.
 *
 * @param  \\Illuminate\\Http\\Request  $request
 * @param  \\Closure  $next
 * @return mixed
 */',
        'startLine' => 43,
        'endLine' => 50,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'aliasName' => NULL,
      ),
      'getPayload' => 
      array (
        'name' => 'getPayload',
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
            'startLine' => 52,
            'endLine' => 52,
            'startColumn' => 35,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 52,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByRequestData',
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