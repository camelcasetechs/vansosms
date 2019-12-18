# Vanso SMS API

An Api to Call the Vanso SMS [Gateway](https://www.interswitchgroup.com/)

Vanso is an Interswitch company

This library assumes you are sending sms to Nigeria only.

This library has no unit test embedded yet, it is highly important you test well.

Check `composer.json` for dependencies.

## Installation

For composer installation, run `composer require camelcasetechs/vansosms`

### Laravel

-   For Laravel >=5.5 uses Package Auto-Discovery, so you don't need to manually add the ServiceProvider and Facades
-   Run `php-artisan vendor:publish --tag=vansosms-config` to copy sample config file to `config/vanso-sms.php`

## Usage

The classes in the src directory are for you to update and adapt to your need.

-   If you use Laravel, and publish the config file as explained in installation, update config/vanso-sms.php and everything should work fine.
-   If you are not using Laravel, please make a child class of `\CamelCase\VansoSMS\VansoSMSClient` and override the `configure` method.

Call the `\CamelCase\VansoSMS\VansoSMSClient::sendSMS` factory or the derived class:

```php
\CamelCase\VansoSMS\VansoSMSClient::sendSMS( string $phone, string $message );
```

`$phone` is the last 10 digit of the Nigeria phone number e.g 9087263512
\$message

`$message` is the sms message string to send minding the 160 character limit per page

Provided you get an object response with ticketId, you have done your own part.

Below is a sample response payload. Please note that this response was `json_encode()`

```json

  "@attributes": {
    "type": "submit"
  },
  "submitResponse": {
    "error": {
      "@attributes": {
        "code": "0",
        "message": "OK"
      }
    },
    "ticketId": "01220112345130545709853"
  }
}
```

Have fun
