# Vanso SMS API

An Api to Call the Vanso SMS [Gateway](https://www.interswitchgroup.com/)

Vanso is an Interswitch company

This code snippet assumes you are sending sms to Nigeria only.

This code snippet has no unit test embedded, it is highly important to test well.

Check `composer.json` for dependencies.

This should be a full composer package soon.

## Installation

For composer installation, run `composer require camelcasetechs/vansosms`

For Laravel >=5.5 uses Package Auto-Discovery, so you don't need to manually add the ServiceProvider and Facades

## Usage

The classes in the src directory are for you to update and adapt to your need.

-   If you use Laravel, you can copy the content of `src/config/config.php` to `config/services.php` and everything should work fine.
-   Open `src/VansoSMSClient.php` and update the `configure` method based on the instruction on the method docs. If you are not using Laravel, please make a child class of `src/VansoSMSClient.php` and override the `configire` method.

Call the `\CamelCase\VansoSMS\VansoSMSClient::sendSMS` factory;

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
