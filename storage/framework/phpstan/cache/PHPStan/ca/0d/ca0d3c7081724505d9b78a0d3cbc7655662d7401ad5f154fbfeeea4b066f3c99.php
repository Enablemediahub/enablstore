<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Listeners/UpdateSyncedResource.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Stancl\Tenancy\Listeners\UpdateSyncedResource
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-20e8b62b8caf73fcaa0c3b7a2056c8d20f3b6a5bee2086454b6db8d57c3da88c-8.2.12-6.70.0.6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'filename' => 'C:/xampp/htdocs/enablstore/vendor/composer/../stancl/tenancy/src/Listeners/UpdateSyncedResource.php',
      ),
    ),
    'namespace' => 'Stancl\\Tenancy\\Listeners',
    'name' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
    'shortName' => 'UpdateSyncedResource',
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
    'endLine' => 123,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Stancl\\Tenancy\\Listeners\\QueueableListener',
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
      'shouldQueue' => 
      array (
        'declaringClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'implementingClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'name' => 'shouldQueue',
        'modifiers' => 17,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 16,
            'startTokenPos' => 63,
            'startFilePos' => 462,
            'endTokenPos' => 63,
            'endFilePos' => 466,
          ),
        ),
        'docComment' => NULL,
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
    ),
    'immediateMethods' => 
    array (
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
          'event' => 
          array (
            'name' => 'event',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Stancl\\Tenancy\\Events\\SyncedResourceSaved',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 28,
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
        'docComment' => NULL,
        'startLine' => 18,
        'endLine' => 30,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Stancl\\Tenancy\\Listeners',
        'declaringClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'implementingClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'currentClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'aliasName' => NULL,
      ),
      'getTenantsForCentralModel' => 
      array (
        'name' => 'getTenantsForCentralModel',
        'parameters' => 
        array (
          'centralModel' => 
          array (
            'name' => 'centralModel',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 32,
            'endLine' => 32,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 32,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Stancl\\Tenancy\\Listeners',
        'declaringClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'implementingClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'currentClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'aliasName' => NULL,
      ),
      'updateResourceInCentralDatabaseAndGetTenants' => 
      array (
        'name' => 'updateResourceInCentralDatabaseAndGetTenants',
        'parameters' => 
        array (
          'event' => 
          array (
            'name' => 'event',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 48,
            'endLine' => 48,
            'startColumn' => 69,
            'endColumn' => 74,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'syncedAttributes' => 
          array (
            'name' => 'syncedAttributes',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 48,
            'endLine' => 48,
            'startColumn' => 77,
            'endColumn' => 93,
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
        'startLine' => 48,
        'endLine' => 87,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Stancl\\Tenancy\\Listeners',
        'declaringClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'implementingClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'currentClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'aliasName' => NULL,
      ),
      'updateResourceInTenantDatabases' => 
      array (
        'name' => 'updateResourceInTenantDatabases',
        'parameters' => 
        array (
          'tenants' => 
          array (
            'name' => 'tenants',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 89,
            'endLine' => 89,
            'startColumn' => 56,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'event' => 
          array (
            'name' => 'event',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 89,
            'endLine' => 89,
            'startColumn' => 66,
            'endColumn' => 71,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'syncedAttributes' => 
          array (
            'name' => 'syncedAttributes',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 89,
            'endLine' => 89,
            'startColumn' => 74,
            'endColumn' => 90,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 89,
        'endLine' => 122,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Stancl\\Tenancy\\Listeners',
        'declaringClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'implementingClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
        'currentClassName' => 'Stancl\\Tenancy\\Listeners\\UpdateSyncedResource',
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