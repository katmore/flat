This document contains installation instructions for "The flat framework".

The flat framework. https://github.com/katmore/flat
Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.

The flat framework is copyrighted free software.
You can redistribute it and/or modify it under either the terms and conditions of the
"The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
of the "GPL v3 License" (see the file GPL-LICENSE.txt).

Use the following steps to install the flat framework:

1. Copy the flat package somewhere, preferably NOT within a publically accessible web root.

2. Use PHP composer (composer.phar) to install dependancies and class autoloader.
   2.1. Install composer if needed. (you can do the following as root if using linux or similar system):
      #$ cd /usr/local/sbin
      #$ curl -s https://getcomposer.org/installer | php
      #$ chmod +x composer.phar

   2.2. Run composer install command in flat/deploy:
      #$ cd flat/deploy
      #$ composer.phar install

3. Copy flat/deploy/config-sample.php to flat/deploy/config.php.

4. If deploying a website or RESTful api service:

   4.1. Copy (or link) the contents of flat/asset to the publically accessible web root.
   
   4.2. See flat/examples/web-service/README.txt if deploying a website or RESTful api service.
   
   4.3. See the files in flat/examples/web-service/config-samples for apache and nginx configuration.
   
5. See flat/examples/cli for executing/deploying command line scripts.