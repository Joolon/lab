<?php
/**
 * mongodb 配置文件
 */

$config['mongo_host'] = '127.0.0.1';

// Generally will be 27017 unless you've configured Mongo otherwise
$config['mongo_port'] = '27017';

// The database you want to work from (required)
$config['mongo_db'] = 'test';

// Leave blank if Mongo is not running in auth mode
$config['mongo_user'] = 'root';
$config['mongo_pass'] = 'root';

// Persistant connections
$config['mongo_persist']     = true;
$config['mongo_persist_key'] = 'ci_mongo_persist';


// Get results as an object instead of an array
$config['mongo_return'] = 'array'; // Set to object
// When you run an insert/update/delete how sure do you want to be that the database has received the query?
// safe = the database has receieved and executed the query
// fysnc = as above + the change has been committed to harddisk <- NOTE: will introduce a performance penalty
$config['mongo_query_safety'] = 'w';


// Supers connection error password display
$config['mongo_supers_connect_error'] = true;


// If you are having problems connecting try changing this to TRUE
$config['host_db_flag'] = true;


