# MiniFast ORM
MiniFast ORM is a very little Object-Relational Mapping system. It will be usefull for little web projects where you don't want to spend all your time writting SQL queries.

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
    <table name="category">
        <column name="id" type="int" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="varchar" size="20"/>
    </table>
    <table name="topic">
        <column name="id" type="int" primaryKey="true" autoIncrement="true"/>
        <column name="category" type="int" required="true"/>
        <foreign-key foreign-table="category">
            <reference local="category" foreign="id"/>
        </foreign-key>
    </table>
</database>
```

### Install
Install MiniFast with [Composer](https://getcomposer.org/) by adding it to the `composer.json` file:
```json
{
    "require": {
        "itechcydia/minifast-orm": "^1"
    }
}
```
There is an installer included in MiniFast that will create classes for you based on your xml schema. Assuming you are in your website root directory, execute it like this:
```bash
$ php vendor/minifast/minifast-orm/init.php /path/to/schema.xml
```
There will be no input if there is no error.

### How to use
After running the installer, an autoloader has been created.
Set up the MySQL host, user and password (defaults are `localhost`, `root` and `root`) in `vendor/itechcydia/minifast-orm/src/minifast/Base.php` and `vendor/itechcydia/minifast-orm/src/minifast/BaseQuery.php` `__construct()` methods.

An `autoload.php` file has been created by Composer and you need to include it in order to use MiniFast. Assuming you have the same `schema.xml` than the one above, you will find some examples below:

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

Select 10 users starting after the third.
```php
<?php
$user = new UserQuery::create()
    ->limit(10)
    ->offset(3)
    ->findAll();
```

Only the user 23.
```php
<?php
$user = new UserQuery::create()
    ->findPK(23);
```

Users 23, 24 and 25.
```php
<?php
$user = new UserQuery::create()
    ->findPKs([23, 24, 25]);
```

User where pseudo is iTechCydia.
```php
<?php
$user = new UserQuery::create()
    ->findByPseudo('iTechCydia')
    ->find();
```

#### UPDATE

Update user 23 and set `newsletter` to `true`, `email_public` to `false` and `email` to `email2@server.com`.
```php
<?php
$user = UserQuery::create()
    ->filterById(23)
    ->setNewsletter(true)
    ->setEmailPublic(false)
    ->setEmail('email2@server.com')
    ->save(); // Don't forget to save!
```

#### DELETE

Delete all from user table (you need to set the first parameter to true to avoid any mistake)
```php
<?php
$user = UserQuery::create()
    ->delete(true);
```

Delete specific users using filters
```php
$user = UserQuery::create()
    ->filterByNewsletter(false) // bad users :p
    ->delete();
```
