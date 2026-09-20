<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../laravel/framework/src/Illuminate/Database/Schema/SQLiteBuilder.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Database\Schema\SQLiteBuilder
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-757ab279aa533cd65ad64cf871f9bdbba9b8dc4eb5306f963f99b84975154312-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../laravel/framework/src/Illuminate/Database/Schema/SQLiteBuilder.php',
      ),
    ),
    'namespace' => 'Illuminate\\Database\\Schema',
    'name' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
    'shortName' => 'SQLiteBuilder',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 175,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Schema\\Builder',
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
    ),
    'immediateMethods' => 
    array (
      'createDatabase' => 
      array (
        'name' => 'createDatabase',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 16,
            'endLine' => 16,
            'startColumn' => 36,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a database in the schema.
 *
 * @param  string  $name
 * @return bool
 */',
        'startLine' => 16,
        'endLine' => 19,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'dropDatabaseIfExists' => 
      array (
        'name' => 'dropDatabaseIfExists',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 27,
            'endLine' => 27,
            'startColumn' => 42,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Drop a database from the schema if the database exists.
 *
 * @param  string  $name
 * @return bool
 */',
        'startLine' => 27,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'hasTable' => 
      array (
        'name' => 'hasTable',
        'parameters' => 
        array (
          'table' => 
          array (
            'name' => 'table',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 40,
            'endLine' => 40,
            'startColumn' => 30,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the given table exists.
 *
 * @param  string  $table
 * @return bool
 */',
        'startLine' => 40,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'getTables' => 
      array (
        'name' => 'getTables',
        'parameters' => 
        array (
          'withSize' => 
          array (
            'name' => 'withSize',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 55,
                'endLine' => 55,
                'startTokenPos' => 162,
                'startFilePos' => 1162,
                'endTokenPos' => 162,
                'endFilePos' => 1165,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 55,
            'endLine' => 55,
            'startColumn' => 31,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the tables for the database.
 *
 * @param  bool  $withSize
 * @return array
 */',
        'startLine' => 55,
        'endLine' => 68,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'getColumns' => 
      array (
        'name' => 'getColumns',
        'parameters' => 
        array (
          'table' => 
          array (
            'name' => 'table',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 76,
            'endLine' => 76,
            'startColumn' => 32,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the columns for a given table.
 *
 * @param  string  $table
 * @return array
 */',
        'startLine' => 76,
        'endLine' => 84,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'dropAllTables' => 
      array (
        'name' => 'dropAllTables',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Drop all tables from the database.
 *
 * @return void
 */',
        'startLine' => 91,
        'endLine' => 109,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'dropAllViews' => 
      array (
        'name' => 'dropAllViews',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Drop all views from the database.
 *
 * @return void
 */',
        'startLine' => 116,
        'endLine' => 125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'setBusyTimeout' => 
      array (
        'name' => 'setBusyTimeout',
        'parameters' => 
        array (
          'milliseconds' => 
          array (
            'name' => 'milliseconds',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 133,
            'endLine' => 133,
            'startColumn' => 36,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the busy timeout.
 *
 * @param  int  $milliseconds
 * @return bool
 */',
        'startLine' => 133,
        'endLine' => 138,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'setJournalMode' => 
      array (
        'name' => 'setJournalMode',
        'parameters' => 
        array (
          'mode' => 
          array (
            'name' => 'mode',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 146,
            'endLine' => 146,
            'startColumn' => 36,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the journal mode.
 *
 * @param  string  $mode
 * @return bool
 */',
        'startLine' => 146,
        'endLine' => 151,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'setSynchronous' => 
      array (
        'name' => 'setSynchronous',
        'parameters' => 
        array (
          'mode' => 
          array (
            'name' => 'mode',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 159,
            'endLine' => 159,
            'startColumn' => 36,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the synchronous mode.
 *
 * @param  int  $mode
 * @return bool
 */',
        'startLine' => 159,
        'endLine' => 164,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'aliasName' => NULL,
      ),
      'refreshDatabaseFile' => 
      array (
        'name' => 'refreshDatabaseFile',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Empty the database file.
 *
 * @return void
 */',
        'startLine' => 171,
        'endLine' => 174,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Schema',
        'declaringClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'implementingClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
        'currentClassName' => 'Illuminate\\Database\\Schema\\SQLiteBuilder',
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