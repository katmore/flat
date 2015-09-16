# Developers Guide
## a guide to developing applications with the flat framework

### Folders/Files and Namespaces

 * /flat/app
  * Application configuration; the way an application is defined in flat.

 * /flat/asset
   * Static assets required by an application.

 * /flat/design
  * Templating files.

 * /flat/deploy
  * Deployment configuration. 

 * /flat/src/flat
  * Packages comprising the flat framework.

 * /flat/src/flat/core
  * Core packages comprise the common basis for the flat framework.
Mostly comprised of commonly shared functionality like controllers and meta-class types, 
these packages are roughly analogous to symfony's "Common" namespace.

### Flat development background
The following rants and aphorisms explain my mindset when developing flat.
A few may be mutually exclusive, some make little sense or may be disturbing.

* I really like 4 letter words.

* I really like RESTful verbiage and stateless APIs.

* I like having clear separation between business logic and application logic.

* I like expressing and relying on maps of relationships and hierarchies.

* I am disappointed when designers are frequently confused about "where things go" in a project. 

* I am very disappointed in myself when a sysadmin cannot easily determine how to deploy a project I developed.

* I hate globals.

* I like using the latest stable release of PHP.

* I dislike using different terminology between two things if they accomplish the same thing.

* I think CodeIgniter and Symfony are nice frameworks.

* I do not mind being practical and pragmatic when there are time pressures to accomplish tasks.

* I like Memcache, MongoDB, and MariaDB 

* I am disappointed in myself when any of my choices make it difficult to achieve scalability or availability goals.

* I am annoyed when I repeat myself.

* I hate having to work really hard to manage, maintain, and determine dependencies anywhere in a project.

* I tend to like shorter rather than longer words.

### Copyright Notice
The flat framework. 
Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
https://github.com/katmore/flat

The flat framework is copyrighted free software.
You can redistribute it and/or modify it under either the terms and conditions of the
"The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
of the "GPL v3 License" (see the file GPL-LICENSE.txt).
