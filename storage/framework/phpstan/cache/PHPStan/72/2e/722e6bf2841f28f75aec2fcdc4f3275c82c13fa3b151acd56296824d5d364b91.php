<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/jobpipeline/src/JobPipeline.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\JobPipeline\JobPipeline
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-af4e0b43dad8b25b9238c29d67913b42e5f79e3de386d13a2a39720b69377bfe-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\JobPipeline\\JobPipeline',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/jobpipeline/src/JobPipeline.php',
      ),
    ),
    'namespace' => 'Stancl\\JobPipeline',
    'name' => 'Stancl\\JobPipeline\\JobPipeline',
    'shortName' => 'JobPipeline',
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
    'endLine' => 116,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'shouldBeQueuedByDefault' => 
      array (
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'name' => 'shouldBeQueuedByDefault',
        'modifiers' => 17,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 14,
            'endLine' => 14,
            'startTokenPos' => 50,
            'startFilePos' => 246,
            'endTokenPos' => 50,
            'endFilePos' => 250,
          ),
        ),
        'docComment' => '/** @var bool */',
        'attributes' => 
        array (
        ),
        'startLine' => 14,
        'endLine' => 14,
        'startColumn' => 5,
        'endColumn' => 51,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'jobs' => 
      array (
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'name' => 'jobs',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable[]|string[] */',
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
        'startColumn' => 5,
        'endColumn' => 17,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'send' => 
      array (
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'name' => 'send',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var callable|null */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 20,
        'startColumn' => 5,
        'endColumn' => 17,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'passable' => 
      array (
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'name' => 'passable',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * A value passed to the jobs. This is the return value of $send.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'shouldBeQueued' => 
      array (
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'name' => 'shouldBeQueued',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/** @var bool */',
        'attributes' => 
        array (
        ),
        'startLine' => 28,
        'endLine' => 28,
        'startColumn' => 5,
        'endColumn' => 27,
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
          'jobs' => 
          array (
            'name' => 'jobs',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 30,
            'endLine' => 30,
            'startColumn' => 33,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'send' => 
          array (
            'name' => 'send',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 30,
                'endLine' => 30,
                'startTokenPos' => 97,
                'startFilePos' => 574,
                'endTokenPos' => 97,
                'endFilePos' => 577,
              ),
            ),
            'type' => 
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
                      'name' => 'callable',
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 30,
            'endLine' => 30,
            'startColumn' => 40,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'shouldBeQueued' => 
          array (
            'name' => 'shouldBeQueued',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 30,
                'endLine' => 30,
                'startTokenPos' => 107,
                'startFilePos' => 604,
                'endTokenPos' => 107,
                'endFilePos' => 607,
              ),
            ),
            'type' => 
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
                      'name' => 'bool',
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 30,
            'endLine' => 30,
            'startColumn' => 64,
            'endColumn' => 91,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 30,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'make' => 
      array (
        'name' => 'make',
        'parameters' => 
        array (
          'jobs' => 
          array (
            'name' => 'jobs',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 41,
            'endLine' => 41,
            'startColumn' => 33,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'self',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @param callable[]|string[] $jobs */',
        'startLine' => 41,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'send' => 
      array (
        'name' => 'send',
        'parameters' => 
        array (
          'send' => 
          array (
            'name' => 'send',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 46,
            'endLine' => 46,
            'startColumn' => 26,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'self',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 46,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'shouldBeQueued' => 
      array (
        'name' => 'shouldBeQueued',
        'parameters' => 
        array (
          'shouldBeQueued' => 
          array (
            'name' => 'shouldBeQueued',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 53,
                'endLine' => 53,
                'startTokenPos' => 242,
                'startFilePos' => 1226,
                'endTokenPos' => 242,
                'endFilePos' => 1229,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 53,
            'endLine' => 53,
            'startColumn' => 36,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'self',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 53,
        'endLine' => 58,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 60,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'toListener' => 
      array (
        'name' => 'toListener',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Closure',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Generate a closure that can be used as a listener.
 */',
        'startLine' => 88,
        'endLine' => 99,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'aliasName' => NULL,
      ),
      'executable' => 
      array (
        'name' => 'executable',
        'parameters' => 
        array (
          'listenerArgs' => 
          array (
            'name' => 'listenerArgs',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 104,
            'endLine' => 104,
            'startColumn' => 32,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'self',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Return a serializable version of the current object.
 */',
        'startLine' => 104,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\JobPipeline',
        'declaringClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'implementingClassName' => 'Stancl\\JobPipeline\\JobPipeline',
        'currentClassName' => 'Stancl\\JobPipeline\\JobPipeline',
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