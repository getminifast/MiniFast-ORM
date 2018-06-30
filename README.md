# MiniFast ORM
MiniFast ORM is a very little Object-Relational Mapping system. It will be usefull for little web project where you don't want to spend all your time writting SQL queries.

### Task list - Installer
- [X] Translate XML database into PHP array
- [X] Translate PHP array into SQL script
- [X] Writing PHP classes from PHP array
- [X] Writting autoload.php file

### Task list - ORM
- [X] Base.php for inserting data into the database
- [X] BaseQuery.php for querying the database

## Documentation

### First of all
You need to create a `.xml` file containing your databse scheme. All supported types and the syntax will be available soon.
Example:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<database name="main">
    <table name="user">
        <column name="id" type="int" primaryKey="true" autoIncrement="true"/>
        <column name="pseudo" type="varchar" size="50"/>
        <column name="email" type="varchar" size="139"/>
        <column name="password" type="varchar" size="128"/>
        <column name="admin" type="boolean" default="0"/>
        <column name="image" type="text"/>
        <column name="newsletter" type="boolean" default="true"/>
        <column name="email_public" type="boolean" default="true"/>
    </table>
    <table name="categorie">
        <column name="id" type="int" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="varchar" size="20"/>
    </table>
    <table name="topic">
        <column name="id" type="int" primaryKey="true" autoIncrement="true"/>
        <column name="categorie" type="int" required="true"/>
        <foreign-key foreign-table="categorie">
            <reference local="categorie" foreign="id"/>
        </foreign-key>
    </table>
</database>
```

### Install
Place the `installer.php` and the `class` directory in your website root directory and then execute it:
```bash
$ php installer.php init /path/to/schema.xml
```
There will be no input if there is no error.

### How to use
Set up the MySQL host, user and password (defaults are `localhost`, `root` and `root`) in `/class/Base.php` and `/class/BaseQuery.php` `__construct()` methods.

An `autoload.php` file has been created and you need to include it in order to use MiniFast. Assuming you have the same `schema.xml` than the one above, you will find some examples below:

#### INSERT
```php
<?php
$user = new User();
$user
    ->setPseudo('iTechCydia')
    ->setEmail('email@server.com')
    ->setPassword(hash('whirlpool', 'theUserSecretPassword'))
    ->setDate(time())
    ->save();
```
This will create the SQL query and execute it.

#### SELECT
```php
<?php
// 10 of all users but begins after the third
$user = new UserQuery::create()
    ->limit(10)
    ->offset(3)
    ->find();

// Only the user 23
$user = new UserQuery::create()
    ->findPK(23);

// Users 23, 24 and 25
$user = new UserQuery::create()
    ->findPKs([23, 24, 25]);

// User where pseudo is iTechCydia
$user = new UserQuery::create()
    ->findByPseudo('iTechCydia')
    ->find();
```

#### UPDATE
```php
<?php
$user = UserQuery::create()
    ->filterById(23)
    ->setNewsletter(true)
    ->setEmailPublic(false)
    ->setEmail('email2@server.com')
    ->save();
```

#### DELETE
```php
<?php
// Delete all from user table (you need to set the first parameter to true to avoid any mistake)
$user = UserQuery::create()
    ->delete(true);

// Delete specific users using filters
$user = UserQuery::create()
    ->filterByNewsletter(false) // bad users :p
    ->delete();
```