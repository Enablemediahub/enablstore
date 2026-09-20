<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyBySubdomain.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-3a946bf4f48e0296ba167fa577441d7789e77300441455169e6e3e23f2372a0b-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Middleware/InitializeTenancyBySubdomain.php',
      ),
    ),
    'namespace' => 'Stancl\\Tenancy\\Middleware',
    'name' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
    'shortName' => 'InitializeTenancyBySubdomain',
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
    'endLine' => 77,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain',
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
      'subdomainIndex' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'name' => 'subdomainIndex',
        'modifiers' => 17,
        'type' => NULL,
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 22,
            'endLine' => 22,
            'startTokenPos' => 60,
            'startFilePos' => 520,
            'endTokenPos' => 60,
            'endFilePos' => 520,
          ),
        ),
        'docComment' => '/**
 * The index of the subdomain fragment in the hostname
 * split by `.`. 0 for first fragment, 1 if you prefix
 * your subdomain fragments with `www`.
 *
 * @var int
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'onFail' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'name' => 'onFail',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 26,
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
            'startLine' => 34,
            'endLine' => 34,
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
            'startLine' => 34,
            'endLine' => 34,
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
        'startLine' => 34,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'aliasName' => NULL,
      ),
      'makeSubdomain' => 
      array (
        'name' => 'makeSubdomain',
        'parameters' => 
        array (
          'hostname' => 
          array (
            'name' => 'hostname',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 59,
            'endLine' => 59,
            'startColumn' => 38,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return string|Response|Exception|mixed */',
        'startLine' => 59,
        'endLine' => 76,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Stancl\\Tenancy\\Middleware',
        'declaringClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'implementingClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
        'currentClassName' => 'Stancl\\Tenancy\\Middleware\\InitializeTenancyBySubdomain',
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