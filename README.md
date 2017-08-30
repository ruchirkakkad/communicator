[![Latest Stable Version](https://poser.pugx.org/picahoo/communicator/v/stable)](https://packagist.org/packages/picahoo/communicator)
[![Total Downloads](https://poser.pugx.org/picahoo/communicator/downloads)](https://packagist.org/packages/picahoo/communicator)
[![Latest Unstable Version](https://poser.pugx.org/picahoo/communicator/v/unstable)](https://packagist.org/packages/picahoo/communicator)
[![License](https://poser.pugx.org/picahoo/communicator/license)](https://packagist.org/packages/picahoo/communicator)
[![Monthly Downloads](https://poser.pugx.org/picahoo/communicator/d/monthly)](https://packagist.org/packages/picahoo/communicator)
[![Daily Downloads](https://poser.pugx.org/picahoo/communicator/d/daily)](https://packagist.org/packages/picahoo/communicator)

# Picahoo Communicator

Send SMS, Email, Whatsapp messages

# Requirements

* Php
* Curl

# Installation
Require this package with composer
```
composer require guzzlehttp/guzzles
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
# Uses
load Communicator class in your file
 ```
use Communicator;
   ```
   or
   ```
  "use Picahoo\Communicator\Facades\Communicator;"
  ```

# Generate Token
```
$token = Communicator::getToken();
```
# send email
```
try{
    $response = Communicator::sendEmail('Email address', 'Message..', 'Subject here..');   
}catch(\Exception $e){
    // exception handle here...
}

```
# Sms
```
try{
    $token = Communicator::getToken();
    
    $isSend = Communicator::sendSms([
        'contact_id' => $contact_id,
        'message'    => $message,
        'token'      => $token
    ]);
        
    $isSend = Communicator::sendMail([
        'contact_id' => $contact_id,
        'message'    => $message,
        'token'      => $token
    ]);
    
    
}catch(\Exception $e){
    // exception handle here...
}
```
# Whatsapp sms
```
try{
    $token = Communicator::getToken();
    
    $isSend = Communicator::sendSms([
        'contact_id' => $contact_id,
        'message'    => $message,
        'token'      => $token
    ]);
        
    $isSend = Communicator::sendMail([
        'contact_id' => $contact_id,
        'message'    => $message,
        'token'      => $token
    ]);
    
    
}catch(\Exception $e){
    // exception handle here...
}
```

