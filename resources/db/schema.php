<?php
/**
 * Schema creation as used in silex kitchen edition.
 * 
 * @author Саша Стаменковић <umpirsky@gmail.com>
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */

$schema = new \Doctrine\DBAL\Schema\Schema();

/*
 * projects table
 */
$projects = $schema->createTable('packages');
$projects->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$projects->setPrimaryKey(array('id'));
$projects->addColumn('identifier', 'string', array('length' => 255, 'notnull' => true));
$projects->addColumn('description', 'text', array('notnull' => true));
$projects->addColumn('type', 'string', array('length' => 64));
$projects->addColumn('versions', 'text', array('null' => true));
$projects->addColumn('time_updated', 'datetime', array('notnull' => true));

/*
 * users table
 */
$users = $schema->createTable('users');
$users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$users->setPrimaryKey(array('id'));
$users->addColumn('username', 'string', array('length' => 32));
$users->addUniqueIndex(array('username'));
$users->addColumn('avatar_url', 'string', array('length' => 255));

/*
 * ratings
 */
$rates = $schema->createTable('ratings');
$rates->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$rates->setPrimaryKey(array('id'));
$rates->addColumn('package_id', 'integer', array('length' => 10, 'unsigned' => true, 'notnull' => true));
$rates->addForeignKeyConstraint($projects, array('package_id'), array('id'));
$rates->addColumn('user_id', 'integer', array('length' => 10, 'unsigned' => true, 'notnull' => true));
$rates->addForeignKeyConstraint($users, array('user_id'), array('id'));
$rates->addColumn('time_updated', 'datetime', array('notnull' => true   ));
$rates->addColumn('version', 'string', array('length' => 32, 'notnull' => false));
$rates->addColumn('rating', 'integer', array('length' => 1, 'notnull' => true));
$rates->addColumn('title', 'text', array('length' => 255, 'notnull' => true));
$rates->addColumn('comment', 'text');

/*
 * metainfo
 */
$metainfo = $schema->createTable('metainfo');
$metainfo->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$metainfo->setPrimaryKey(array('id'));
$metainfo->addColumn('package_id', 'integer', array('length' => 10, 'unsigned' => true, 'notnull' => true));
$metainfo->addForeignKeyConstraint($projects, array('package_id'), array('id'));
$metainfo->addColumn('user_id', 'integer', array('length' => 10, 'unsigned' => true, 'notnull' => false));
$metainfo->addForeignKeyConstraint($users, array('user_id'), array('id'));
$metainfo->addColumn('time_updated', 'datetime', array('notnull' => true));
$metainfo->addColumn('version', 'string', array('length' => 32, 'notnull' => false));
$metainfo->addColumn('group','string', array('length' => 32, 'notnull' => true));
$metainfo->addColumn('value','text');

return $schema;