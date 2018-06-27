# MiniFast ORM
MiniFast ORM is a very little Object-Relational Mapping system. It will be usefull for little web project where you don't want to spend all your time writting SQL queries.

### Task list - Installer
- [X] Translate XML database into PHP array
- [X] Translate PHP array into SQL script
- [X] Writing PHP classes from PHP array
- [ ] Writting autoload.php file

### Task list - ORM
- [X] Base.php for inserting data into the database
- [ ] BaseQuery.php for querying the database

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
Place the `installer.php` in your website root directory and then execute it:
```bash
$ php installer.php init /path/to/schema.xml
```

Follow instructions and all should be good.

*To be continued...*