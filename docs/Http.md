# PHP-HTTP

Hypertext Transfer Protocol (HTTP) utilities for PHP

## Usage

### Response headers

 * Retrieving a header (with optional value prefix)

   ```php
   $header = \Rini\Core\Http\ResponseHeader::get('Content-Type');
   // or
   $header = \Rini\Core\Http\ResponseHeader::get('Content-Type', 'text/');
   ```

 * Setting a header (overwriting other headers with the same name)

   ```php
   \Rini\Core\Http\ResponseHeader::set('X-Frame-Options', 'sameorigin');
   ```

 * Adding a header (preserving other headers with the same name)

   ```php
   \Rini\Core\Http\ResponseHeader::add('Vary', 'User-Agent');
   ```

 * Removing a header (with optional value prefix)

   ```php
   $success = \Rini\Core\Http\ResponseHeader::remove('X-Powered-By');
   // or
   $success = \Rini\Core\Http\ResponseHeader::remove('X-Powered-By', 'PHP');
   ```

 * Retrieving and removing a header at once (with optional value prefix)

   ```php
   $header = \Rini\Core\Http\ResponseHeader::take('Set-Cookie');
   // or
   $header = \Rini\Core\Http\ResponseHeader::take('Set-Cookie', 'mysession=');
   ```

## License

This project is licensed under the terms of the [MIT License](https://opensource.org/licenses/MIT).
