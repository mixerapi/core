<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER['PHP_SELF'] = '/';

define('IS_TEST', true);

Configure::write('App.fullBaseUrl', 'http://localhost');
putenv('DB=sqlite');

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');
/*
 * Set test database and load schema
 * @link https://book.cakephp.org/4/en/development/testing.html#creating-test-database-schema
 */
putenv('DB_DSN=sqlite:///:memory:');
ConnectionManager::setConfig('test', ['url' => getenv('DB_DSN')]);
ConnectionManager::setConfig('test_custom_i18n_datasource', ['url' => getenv('DB_DSN')]);
(new SchemaLoader())->loadInternalFile(__DIR__ . DS . 'schema.php');
