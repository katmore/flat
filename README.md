# flat: an experimental framework for php
*Flat* is an experimental framework for php.

The purpose in creating *flat* is to have a framework which conceptualizes the organization of an application project as set of hierarchical resources.

Architecturally, it facilitates a broad swathe of design patterns using a client-server process flow.

## Features
 * HTML templating: convenience classes facilitating sophisticated templating
 * Front-End Routing: flexible dynamic routing for the HTML views
 * Back-End Webservice: convienence classes to faciliate actual RESTful APIs
 * Command line scripts: convenience classes to faciliate creation of recurring (cron), deamons, and ad-hoc scripts

## Installation
*Flat* can be the basis of a **new project** or added to an **existing project**

### New Projects
The easiest way to get going with **flat** is to copy the [*flat boilerplate webapp*](https://github.com/katmore/flat-webapp).

**Step 1. Composer 'create-project'** (*flat boilerplate webapp*)...

```bash
composer create-project katmore/flat-webapp my_project_dir
```
*(copies this repo and configures php dependencies)*

 **Step 2. Bower update**
```bash
cd my_project_dir
bower update
```
*(installs static dependencies)*

### Existing Projects
**Step 1. Composer install** (*flat boilerplate webapp*)...
```bash
cd my_project_dir
composer require katmore/flat
```
*(copies this repo and configures php dependencies)*

## Architecture
The components of *flat* architecture facilitate a *client-server* process flow...

  * **resources**: An abstract hierarchical tree of the applications components.
  * **app objects**: A group of instantiable php objects.
  * **app routes**: A group of of *routes* that an application controller can access to resolve a *client* request.
  * **app controllers**: A group of application "entry-points"; i.e. the "server" which processes a *request* from the "client".
  * **requests**: A *request* consists of a client inquiry which provides a *resource* and any associated input data to an *app controller*.
  * **route maps**: A *route map* consists of multiple routes that each must be *resolved* by an *app controller*
  * **resolvers**: A *resolver* instantiates *app objects* when given a *resource*.

Admittedly, the component organization of the *flat* architecture is rigidly attached to the *client-server* model. Despite this, the programming design constraints of a *flat* based project are actually very minimal. For this reason, *flat* can be argued to be a *lightweight framework*. Any programming design pattern can be used within a *flat* project.

However... there is an idealized programming design use case neatly bundled within the architecture.

Groan...yes, it is yet another design pattern.

The **Resource-Route-Controller** is a novel design pattern invented in tandem with *flat*.
It is, though, intended to be a very lightweight design pattern that should not keep other design patterns (MVC, MVP, MVVM) from being incorporated. It is described further in a section below.

The hierarchical organization of a *flat* project is abstracted into multiple abstract levels of programming design (not just the path names of scripts and dependency file structure).

The following sections contain further details regarding concepts and terminology encountered in *flat*.

### Resource:
   A "resource" is a string value comprised of one or more "segments", 
   each "segment" being delineated by either a backslash "\" or forward-slash "/".
   
   For example, given the following URL:
   
```
https:/example.com/my_app/my_app_frontend/my_frontend_view
```

   An *app controller* might extract the URL path as the *resource* `/my_app/my_app_frontend/my_frontend_view`
   and then check if a php class exists with the name `\my_app\my_app_frontend\my_frontend_view`, and instantiate
   the class, which becomes an *app object*...
   
```php
$check_class = $_SERVER ['PATH_INFO'];
$check_class = str_replace('/','\\',$check_class);
if (class_exists($check_class)) {
   new $check_class;
}
```
   
   *Note:*
   *flat* has convenience classes that facilitate more complex routing than the trivial example above.
   These facilitate more complex aspects, such as dealing with associated request input data.
   
   *See*
    * **Route Rules** definition below
    * **Route Factory** definition below
    * **Resolver Class** https://github.com/katmore/flat/blob/master/src/flat/core/resolver.php


### Route Factory:
   A "route factory" facilitates creating a "route map" for resolving a "resource".

### Route Map:
   A "route map" consists of "route rules" which determine how a "resource" 
   corresponds to php classnames within a php sub-namespace.
   
### Entry Point Controller:
   An entry-point controller script uses a "resolver" to instantiate "app objects". based
   on the "route map" logic created in this  
   
   An example of an entry-point script can be seen in the 'flat-webapp' github repo:
      https://github.com/katmore/flat-webapp/blob/master/web/api.php

## The "Resource-Route-Controller" Design Pattern
  * **resource**
   * **input** data associated
  * **controller**
   * **request** Determines the *resource* (and any *input*) to provide the *controller*
  
## "Resource-Route-Controller" 
  Ideal process flow incorporating *Resource-Route-Controller* design using 'client-server' model
  1. An application controller is provided a "resource" along with any "input"
  2. A controller "resolves" the "resource" into one or more objects which 
     contain the application logic.

## Legal
### Copyright
The Flat Framework. https://github.com/katmore/flat
Copyright (c) 2012-2016 Doug Bird. All Rights Reserved.

### License
The Flat Framework is copyrighted free software.
You may redistribute and modify it under either the terms and conditions of the
"The MIT License (MIT)"; or the terms and conditions of the "GPL v3 License".
See [LICENSE](https://github.com/katmore/flat/blob/master/LICENSE) and [GPLv3](https://github.com/katmore/flat/blob/master/GPLv3).
