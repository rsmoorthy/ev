## Layout of Application

**Note: This file is written assuming a standard Journo module. The location of
the file will be moved later**

```
<folder>        Purpose                         <mapped within journo>
----------------------------------------------------------------
* Accessed from frontend directly
  - html/         HTML files to frontend          browser/html/<module>/
  - css/          CSS files to frontend           browser/css/<module>/
  - js/           JS files to frontend            browser/js/<module>/
  - images/       IMG files to frontend           browser/images/<module>/

* Accessed from frontend via jForm3
  - forms/        Jform3 Forms                    browser/forms/<module>/
  - templates/    Templates served to backend     browser/templates/<module>/

* All PHP source. Not to be accessed from frontend
  - php/          All php files                   browser/modules/<module>/
  - php/hook/     All hook files                  browser/modules/<module>/hook/

* Misc files. Not to be accessed from frontend
  - api/          New API YAML/JSON files         browser/api/<module>/
  - scripts/      Internal scripts                browser/scripts/<module>/
  - schema/       Schema files                    server/<module>/
  - actions.html  API Actions                     browser/admin/modules/<module>_actions.html
```


## php folder

This folder contains all the PHP sources. All entry to application should come 
via api.php only.

**Note: The actual application entry point is via 

* `root` api.php, which calls the module's api.php
* `root` db2.php, which calls the module's hooks

Let us develop all our code, considering the above point. In the future, we will
put configure apache2, to explicitly not access the modules/<module> aka php/ folder
directly.

### php folder structure

The recommended structure is as follows:

* api.php
  - which contains the entry points to all FrontEnd actions and direct API actions
* access.php
  - specifies the access (authentication and authorization functions). See the example 
  file for details

* <Class>.php - Multiple class files. Each class file is named after the PHP Class
it uses
  - Examples: Program.php, Stay.php, Checkin.php, JoomlaPayment.php etc.
  - Each module should contain a default class file, that reflects the module.
  - For all other functionality, Specify one class file for each functionality


### php/hook/ folder structure

Each file in this is directly named after the hook and contains the functions
as directed in the Hooks.md specification. See a sample hook for more details.

