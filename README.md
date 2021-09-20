# Random Items Block

This Omeka S module adds a page block layout that displays random items. The
number of items is configurable.

## Installation

See [Installing modules](https://omeka.org/s/docs/user-manual/modules/#installing-modules)

## Using the cache

Getting a random list of items can take a significant amount of time on large
databases.
To avoid slowing down the page too much, the list of items is put into a cache
for 60 minutes, so the list of items will change at most once per hour.

The cache used is [APC User Cache](https://www.php.net/manual/fr/book.apcu.php).
If you encounter performance issues, make sure it is enabled.

If you do not want to use the cache you can disable APC User Cache.
Alternatively you can use the view helper directly in your theme:

```php
echo $this->getRandomItems($count = 3, $useCache = false);
```

## Caveats

* To avoid checking user permissions, only public items are displayed, even if
  the logged in user is a Global Administrator
