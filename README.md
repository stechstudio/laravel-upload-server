# Laravel Upload Server

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stechstudio/laravel-upload-server.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-upload-server)

A robust backend for handling file uploads, batteries included. Supports large chunked uploads. It is currently built for [FilePond](https://pqina.nl/filepond/) with planned support for [Uppy](https://uppy.io/). 

## Installation

You know the drill:

```bash
composer require stechstudio/laravel-upload-server
```

## Quickstart

### 1. Add the route

In your routes file add:

```php
UploadServer::route();
```

Note that this Route can be put inside a `Route::group()`, and you can also chain additional route details. 

```php
UploadServer::route()->middle('my-middleware');
```

You will now have a route setup at `/upload-server` using your default backend. Point your client-side upload integration to this endpoint.

Any file uploads sent to this endpoint will be handled for you and saved at the configured path. 

### 2. Retrieve the saved files

Now when your form is submitted, take the UUIDs and retrieve the saved files:

```php
public function handleFormSubmission(Request $request)
{
    // 'attachments' is the name of the client-side uploader
    $files = UploadServer::retrieve($request->input('attachments'));
}
```

You will receive back an array of `UploadedFile` objects if there were multiple files submitted, otherwise you will have a single `UploadedFile` instance.

### 3. Wrap up

You can now simply move the uploaded files to a permanent location, store details in a database, etc.

```php
$files = UploadServer::retrieve($request->input('attachments'));

foreach($files AS $file) {
    $file->store('attachments', 's3');
}
```

That's it, seriously. 

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
