Simple cURL wrapper for PHP
===========================

Usage
-----

```php
require_once 'vendor/autoload.php';
$http = new \rekcuFniarB\HTTP\HTTP();
$response = $http->query('https://example.com');
if $http->error {
    echo $http->error['message'];
}
else {
    echo $response;
}
```

Optional parameters accepted by `query` method:

* `$data` array of GET/POST params
* `$method` "GET" or "POST"

There are also some other methods, I'm lazy to describe them here. See source code.
