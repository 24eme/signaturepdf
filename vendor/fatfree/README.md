# fatfree-core
Fat-Free Framework core library

### Usage:

First make sure to add a proper url rewrite configuration to your server, see https://fatfreeframework.com/3.6/routing-engine#DynamicWebSites

**without composer:**

```php
$f3 = require('lib/base.php');
```

**with composer:**

```
composer require bcosca/fatfree-core
```

```php
require("vendor/autoload.php");
$f3 = \Base::instance();
```

---
For the main repository (demo package), see https://github.com/bcosca/fatfree  
For the test bench and unit tests, see https://github.com/f3-factory/fatfree-dev  
For the user guide, see https://fatfreeframework.com/user-guide  
For the documentation, see https://fatfreeframework.com/api-reference
