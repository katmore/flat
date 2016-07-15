## installation instructions for "The flat framework".
1. Copy the flat package somewhere, preferably NOT within a publically accessible web root.

2. Use PHP composer (composer.phar) to install dependancies and class autoloader...

   Run composer install command from the flat/deploy directory...
   
      `#$ cd flat/deploy`
      
      `#$ composer.phar install`

3. Copy flat/deploy/config-sample.php to flat/deploy/config.php...

4. If deploying a website or RESTful api service...

   4.1. Copy (or link) the contents of flat/asset to the publically accessible web root.
   
   4.2. Create an entry point based on instructions in [flat/examples/web-service/README.txt](examples/web-service/README.txt)
   
   4.3. See the files in [flat/examples/web-service/config-samples](examples/web-service/config-samples) for [apache .htaccess](examples/web-service/config-samples/apache2-htaccess.txt) and nginx [location rules](examples/web-service/config-samples/nginx-location.txt) and [server rules](examples/web-service/config-samples/nginx-server.txt).
   
5. See flat/examples/cli for executing/deploying command line scripts.

### Copyright Notice
The flat framework. 
Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
https://github.com/katmore/flat

The flat framework is copyrighted free software.
You can redistribute it and/or modify it under either the terms and conditions of the
"The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
of the "GPL v3 License" (see the file GPL-LICENSE.txt).
