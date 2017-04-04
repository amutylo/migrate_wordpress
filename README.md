`# Migrate-WordPress
MySQL sources to Migrate from WordPress to Drupal 8
Do not forget to add access to wordpress db on your mysql server
by adding below lines to your settings.php file
below of the lines for drupal db access 
$databases['default']['default'] = array (...);


$databases['migrate']['default'] = array (
  'database' => 'wordpress_database_name',
  'username' => 'db_access_username',
  'password' => 'db_access_password',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

Enable modules: 	
- Migrate; 
- Migrate Drupal;
- Migrate Drupal UI

then enable modules:
- Migrate Plus;
- Migrate Tools;

then enable
- Migrate Wordpress; 

then see available migrations
drush --no-halt-on-error ms

then run migration
drush --no-halt-on-error mi --group=wp

