# Remote Files Cache

A WP library for caching remote files. It caches the hash of the remote file in WP options table, avoiding multiple remote requests on the same file if the local file is up to date, saving bandwidth. It is done through a tiny HEAD request.

## Installation

```
composer require otgs/remote-files-cache
```

## Usage

Checking if the cached file is up to date:

```php
$remote_file_cache = new OTGS_Remote_File_Cache( 'http://my-awesome.url' );

if ( $remote_file_cache->is_up_to_date() ) {
    //do not do the remote request
}
```

Updating cache:

```php
$remote_file_cache = new OTGS_Remote_File_Cache( 'http://my-awesome.url' );

//Do your stuff: request the file, etc.

$remote_file_cache->update();
```
