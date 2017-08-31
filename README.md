# Picahoo Communicator

Send SMS, Email, Whatsapp messages

# Requirements

* Php
* Curl

# Installation
Require this package with composer
```
composer require picahoo/communicator
```

Add service provider to your app/config.php providers array
```php
Picahoo\Communicator\CommunicatorServiceProvider::class,
```

Add Alias to your aliases array in your app/config.php
```php
'Communicator' => Picahoo\Communicator\Facades\Communicator::class,
```
Publish config with
```
php artisan vendor:publish --provider="Picahoo\Communicator\CommunicatorServiceProvider"
```
# How to use 
load Communicator class in your file
 ```
use Communicator;
   ```
   or
   ```
  "use Picahoo\Communicator\Facades\Communicator;"
  ```
  
# Send email 
```
    $response = Communicator::sendEmail('Email address', 'Message..', 'Subject here..');    
    // return true and false
```

