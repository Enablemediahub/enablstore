<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByDomain.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\Tenancy\Middleware\InitializeTenancyByDomain
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-9fc24c52859699dbac48e3dad1b31055691b07810c28357878bb3f87f4a592e4-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyByDomain.php',
      ),
    ),
    'namespace' => 'Stancl\\Tenancy\\Middleware',
    'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
    'shortName' => 'InitializeTenancyByDomain',
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
    'endLine' => 41,
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
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'name' => 'onFail',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 14,
        'endLine' => 14,
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
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'name' => 'tenancy',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var Tenancy */',
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
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
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'name' => 'resolver',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var DomainTenantResolver */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 20,
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
            'startLine' => 22,
            'endLine' => 22,
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
                'name' => 'Stancl\\Tenancy\\Resolvers\\DomainTenantResolver',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 22,
            'endLine' => 22,
            'startColumn' => 51,
            'endColumn' => 80,
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
        'startLine' => 22,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
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
            'startLine' => 35,
            'endLine' => 35,
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
            'startLine' => 35,
            'endLine' => 35,
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
        'startLine' => 35,
        'endLine' => 40,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
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