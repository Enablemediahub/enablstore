<?php declare(strict_types = 1);

// odsl-C:/xampp/htdocs/enablstore/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-throw_if
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.6-8.2.12-f2bb75414d49f9ea454dfd2ad3f08fabee873f523b69079bce1f427173da86f8',
   'data' => 
  array (
    'name' => 'throw_if',
    'parameters' => 
    array (
      'condition' => 
      array (
        'name' => 'condition',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 419,
        'endLine' => 419,
        'startColumn' => 23,
        'endColumn' => 32,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'exception' => 
      array (
        'name' => 'exception',
        'default' => 
        array (
          'code' => '\'RuntimeException\'',
          'attributes' => 
          array (
            'startLine' => 419,
            'endLine' => 419,
            'startTokenPos' => 1728,
            'startFilePos' => 10450,
            'endTokenPos' => 1728,
            'endFilePos' => 10467,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 419,
        'endLine' => 419,
        'startColumn' => 35,
        'endColumn' => 65,
        'parameterIndex' => 1,
        'isOptional' => true,
      ),
      'parameters' => 
      array (
        'name' => 'parameters',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => true,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 419,
        'endLine' => 419,
        'startColumn' => 68,
        'endColumn' => 81,
        'parameterIndex' => 2,
        'isOptional' => true,
      ),
    ),
    'returnsReference' => false,
    'returnType' => NULL,
    'attributes' => 
    array (
    ),
    'docComment' => '/**
 * Throw the given exception if the given condition is true.
 *
 * @template TValue
 * @template TException of \\Throwable
 *
 * @param  TValue  $condition
 * @param  TException|class-string<TException>|string  $exception
 * @param  mixed  ...$parameters
 * @return ($condition is true ? never : ($condition is non-empty-mixed ? never : TValue))
 *
 * @throws TException
 */',
    'startLine' => 419,
    'endLine' => 430,
    'startColumn' => 5,
    'endColumn' => 5,
    'couldThrow' => false,
    'isClosure' => false,
    'isGenerator' => false,
    'isVariadic' => true,
    'isStatic' => false,
    'namespace' => NULL,
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'throw_if',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php',
      ),
    ),
  ),
));