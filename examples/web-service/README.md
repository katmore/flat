This document contains instructions for deploying a website or RESTful API service.

The flat framework. https://github.com/katmore/flat
Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.

The flat framework is copyrighted free software.
You can redistribute it and/or modify it under either the terms and conditions of the
"The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
of the "GPL v3 License" (see the file GPL-LICENSE.txt).

To install flat for a public website and/or RESTful API service

1. Complete the steps in flat/INSTALL.md

2. If deploying a website:

   2.1. Copy flat/examples/web-service/html-route.php to your public web root.
   
   2.2. Open the copied html-route.php in an editor: 
      change the value of $flat_deploy_dir to the full system path of flat/deploy.
 
3. If deploying a RESTful API service:

   3.1. Copy flat/examples/web-service/api-route.php to your public web root.
   
   3.2. Open the copied api-route.php in an editor: 
      change the value of $flat_deploy_dir to the full system path of flat/deploy.