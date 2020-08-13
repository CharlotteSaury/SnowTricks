# SnowTricks
Project 6 of OpenClassrooms "PHP/Symfony app developper" course.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/4ae380e294b249b8acd8647b8926452c)](https://www.codacy.com/manual/CharlotteSaury/SnowTricks?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=CharlotteSaury/SnowTricks&amp;utm_campaign=Badge_Grade)

# Description

<a href="#">SnowTricks</a> is a community website for snowboarders.
- Trick list and description are visible for all visitors
- Registered users are allowed to comment tricks and add/edit their own tricks, and edit their profile
- Moderators and admin are allowed to administrate all tricks and comments
- Admin are allowed to administrate users and especially user roles

# Symfony 5.1 / Bootstrap 4 project

# Installation

<p><strong>1 - Git clone the project</strong></p>
<pre>
    <code>https://github.com/CharlotteSaury/SnowTricks.git</code>
</pre>

<p><strong>2 - Install libraries</strong></p>
<pre>
    <code>php bin/console composer install</code>
</pre>

<p><strong>3 - Create database</strong></p>
<ul>
    <li>a) Update DATABASE_URL .env file with your database configuration.
        <pre>
            <code>DATABASE_URL=mysql://username:password@127.0.0.1:3306/snowtricks_dev?serverVersion=mariadb-10.4.10</code>
        </pre>
    </li>
    <li>b) Create database: 
        <pre>
            <code>php bin/console doctrine:database:create</code>
        </pre>
    </li>
    <li>c) Create database structure:
        <pre>
            <code>php bin/console make:migration</code>
        </pre>
    </li>
    <li>d) Insert fictive data (optional)
        <pre>
            <code>php bin/console doctrine:fixtures:load</code>
        </pre>
    </li>
</ul>

<p><strong>4 - Configure MAILER_DSN of Symfony mailer in .env file</strong></p>

# Usage

For admin access : 
    - if you used fictive data (see 3-d)), you can login with following accounts :
        - user account :
            username : User
            password : User1*
        - moderator account :
            username : Moderator
            password : Moderator1*
        - admin account :
            username : Admin   
            password : Admin1*
        
    - if you did not use fictive data:
        - create a user account with sign up form
        - activate your account by following the activation link
        - to have admin role, go to your database and replace ["ROLE_USER"] with ["ROLE_ADMIN"]



