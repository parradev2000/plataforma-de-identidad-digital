<?php return array (
  'models' => 
  array (
    'permission' => 'Spatie\\Permission\\Models\\Permission',
    'role' => 'App\\Models\\rol',
  ),
  'table_names' => 
  array (
    'roles' => 'rols',
    'permissions' => 'permissions',
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles' => 'model_has_roles',
    'role_has_permissions' => 'role_has_permissions',
  ),
  'column_names' => 
  array (
    'role_pivot_key' => NULL,
    'permission_pivot_key' => NULL,
    'model_morph_key' => 'model_id',
    'team_foreign_key' => 'team_id',
  ),
  'register_permission_check_method' => true,
  'register_octane_reset_listener' => false,
  'events_enabled' => false,
  'teams' => false,
  'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
  'use_passport_client_credentials' => false,
  'display_permission_in_exception' => false,
  'display_role_in_exception' => false,
  'enable_wildcard_permission' => false,
  'cache' => 
  array (
    'expiration_time' => 
    \DateInterval::__set_state(array(
       'from_string' => true,
       'date_string' => '24 hours',
    )),
    'key' => 'spatie.permission.cache',
    'store' => 'default',
  ),
);