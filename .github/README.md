## Things to consider
- What if a specific request has a different place where the pagination data is stored? (I think define an interface)

## New Paginator Features
- You can now overwrite the $maxPages property on the paginator to set an upper maximum to prevent infinite loops
  - Maybe we need maxTime?
- You can add the `HasRequestPagination` on a request and then the following three lines to have pagination on a per-request basis

## Todo

- [x] Asynchronous Pagination
- [x] Per-page logic
- [ ] Tests for the middleware (throw and total)
- [ ] Tests for exception handlers on pools & async requests
- [ ] Loop protection (if subsequent request is exactly the same)
- [x] Tests for checking exact iteration count
- [x] Tests for `items()` method
- [x] Test for `collect()` method without items
- [ ] Mocking/Fixture recording for paginators
- [ ] Iterating through items with async
- [ ] Consider adding default implementations of all types of paginator
- [ ] Test being able to use synchronous pagination with an async paginator (async = false)
- [x] Test logic exception if trying to use non-async pagination on an asynchronous paginator
- [ ] Implement a "max pages" on the paginator, so we don't iterate over many pages
- [ ] Implement a "starting page" option
- [ ] Create a `collectAsync` method
- [ ] Create "generic" paginators with closures that can be defined to overwrite the functionality to make it easier for people to use
- [ ] Add a method on the connector to create the paginator `$connector->paginate($request)`
- [ ] Provide a way for people to map paginated items from connectors/requests `implements MapPaginatedItems` which will be really nice to create DTOs

## Docs

Welcome to Saloon's new pagination. This repo is just a nice place for me to build and test out how I would like the
new pagination to work in Saloon v3.

### Summary of changes

- Paginators are now class based and less opinionated - you can still define paginators in your connector but they are defined via anonymous classes.
- You now need to define `getPageItems` method which tells Saloon where the array of results are on each page. This means `$paginator->collect()` and `$paginator->items()` work out of the box
- Saloon will now throw exceptions if a paginated request fails, even if people don't add `AlwaysThrowOnErrors` trait (when not using asynchronous pagination)
- The `json()` method has been renamed to `items()`
- Asynchronous support is not added by default but can be implemented by a trait
- Inside every paginator, you'll be able to access `$this->page` as well as `$this->totalItems` which is counted automatically
- You can now specify a maximum number of pages to iterate over

## Synchronous Pagination

I will be using Saloon's super-hero pages as examples for each of the different major pagination types, which are paged,
offset and cursor.

### Paged Pagination

To create a paged paginator, create a class near your connector and extend the `PagedPaginator` abstract class.

```php
use Saloon\PaginationPlugin\PagedPaginator;

class SuperheroPaginator extends PagedPaginator
{
    //
}
```

Next, you will be required to implement two methods: `isLastPage` and `getPageItems`. These methods will determine if
the paginator
should get the next page and the array of items in each response respectively.

```php
use Saloon\Contracts\Response;use Saloon\PaginationPlugin\PagedPaginator;

class SuperheroPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }
    
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }
}
```

When using the paginator, you just need to pass in a connector and a request.

```php
$paginator = new SuperheroPaginator($connector, $request);
```

### Offset Pagination

To create an offset paginator, create a class near your connector and extend the `OffsetPaginator` abstract class.

```php
use Saloon\PaginationPlugin\OffsetPaginator;

class SuperheroPaginator extends OffsetPaginator
{
    //
}
```

Next, you will be required to implement two methods: `isLastPage` and `getPageItems`. These methods will determine if
the paginator
should get the next page and the array of items in each response respectively.

```php
use Saloon\Contracts\Response;use Saloon\PaginationPlugin\OffsetPaginator;

class SuperheroPaginator extends OffsetPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return (int)$response->json('total') === $this->getOffset();
    }

    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }
}
```

When using the paginator, you will need to provide an additional argument to define the "per-page limit" of the
paginator
this is so Saloon can calculate the limit/offset accordingly.

```php
$paginator = new SuperheroPaginator($connector, $request, perPageLimit: 100);
```

### Cursor Paginator

To create a cursor paginator, create a class near your connector and extend the `CursorPaginator` abstract class.

```php
use Saloon\PaginationPlugin\CursorPaginator;

class SuperheroPaginator extends CursorPaginator
{
    //
}
```

Next, you will be required to implement three methods: `isLastPage` and `getPageItems` and `getNextCursor`. These
methods will determine if the paginator
should get the next page, the array of items in each response and the next cursor respectively.

```php
use Saloon\Contracts\Response;use Saloon\PaginationPlugin\CursorPaginator;

class SuperheroPaginator extends CursorPaginator
{
    protected function getNextCursor(Response $response): int|string
    {
        $nextPageUrl = $response->json('next_page_url');
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $queryParams);

        return $queryParams['cursor'];
    }

    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }

    protected function getPageItems(Response $response): array
    {
        return $response->json('data');
    }
}
```

When using the paginator, you just need to pass in a connector and a request.

```php
$paginator = new SuperheroPaginator($connector, $request);
```

## Asynchronous Pagination

Asynchronous pagination is not provided by default, however adding it is a breeze. The only requirement to use async
pagination is that you are able to calculate the **total number of pages** on the fist API response. This is because
the paginator does not know when the last response is provided.

First, add the `HasAsyncPagination` trait to your paginator.

```php
use Saloon\Contracts\Response;use Saloon\PaginationPlugin\PagedPaginator;use Saloon\PaginationPlugin\Traits\HasAsyncPagination;

class SuperheroPaginator extends PagedPaginator
{
    use HasAsyncPagination;

    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }
    
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }
}
```

Next, you will be required to define a method `getTotalPages`. This method requires you to return the total
number of pages that Saloon needs to iterate over.

```php
class SuperheroPaginator extends PagedPaginator
{
    use HasAsyncPagination;

    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }

    protected function getTotalPages(Response $response): int
    {
        return (int)ceil($response->json('total') / $response->json('per_page'));
    }
}
```

> **Note**
> You don't need to define the `isLastPage` when using the trait unless you're using synchronous pagination too.

Now, when using the paginator, make sure to set it in asynchronous mode. When you iterate over each item, you will
retrieve a `PromiseInterface` instance and not a response.

```php
$paginator = new SuperheroPaginator($connector, $request);
$paginator->async();
```

## Using Paginators

You can use Saloon's paginators in many different ways. You can use it in a for-loop to get each response but you
can also use the `items()` method to iterate over each item, or even better - use Laravel's collections to map,
filter and change the collection of items.

### Iterating Over

```php
$paginator = new SuperheroPaginator($connector, $request);

foreach($paginator as $response) {
    // $response->json(...)
}
```

### Items Method

The items method will return each item instead of a response, saving you from traversing through multiple arrays

```php
$paginator = new SuperheroPaginator($connector, $request);

foreach($paginator->items() as $superhero) {
    // $superhero['name'] -> Batman
}
```

### Collect Method

The collect method requires `illuminate/collections` to be installed, but allows you to iterate through your
items in a `LazyCollection`. You can specify the `throughItems` argument as `false` if you would like a
collection of responses instead.

```php
$paginator = new SuperheroPaginator($connector, $request);
$collection = $paginator->collect();

// Or collection of responses

$collection = $paginator->collect(throughItems: false);
```

### Pools

Pools require your paginator to have the `HasAsyncPagination` trait, but they work just like typical Saloon pools

```php
$paginator = new SuperheroPaginator($connector, $request);

$paginator->pool(
    concurrentRequests: 5,
    responseHandler: fn () ...
    exceptionHandler: fn () ...
)->send();
```

## Other Options

### Maximum Number Of Pages

You may use the `->setMaxPages()` method on the paginator to set a maximum number of pages that have been iterated
over. This is useful if you only want to get the first 50 pages, pause and then start again.

```php
$paginator = new SuperheroPaginator($connector, $request);
$paginator->setMaxPages(5);

foreach ($paginator as $response) {
    
}
```

### Converting Items Into DTOs

You may customise the paginator items however you like inside the `getPageItems`. You could even convert
each item into a DTO which will passed through the rest of your paginators.

```php
use Saloon\Contracts\Response;use Saloon\PaginationPlugin\PagedPaginator;

class SuperheroPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }
    
    protected function getPageItems(Response $response): array
    {
        $items = $response->json('data') ?? [];
        
        return array_map(fn (array $item): object => MyDataObject::fromArray($item))
    }
}
```

## Customisation

Often times you will also need to configure the query parameters that Saloon sets to apply the pagination. On each
of the paginators, you may overwrite the `applyPagination` method and use any query parameter you prefer.

Here you will find how Saloon will select the current page, as well as the per-page or "limit". The API you are
integrating with may have a different way of applying this, so you can configure this here.

```php
class SuperheroPaginator extends PagedPaginator
{
    protected function applyPagination(Request $request): Request
    {
        $request->query()->add('currentPage', $this->page);
        
        if (isset($this->perPageLimit)) {
            $request->query()->add('limit', $this->perPageLimit);
        }

        return $request;
    }
}
````

## Your own paginators 👀

With Saloon's base paginator class you may create your own paginator based on the type of API that you are dealing
with. This is especially useful if the pagination you are working with isn't paged, offset or cursor pagination.

Just create a class and extend the base `Paginator` class. You will be required to define the following methods:

- `applyPagination`
- `isLastPage`
- `getPageItems`

```php
class CustomPaginator extends Paginator
{
    protected function applyPagination(Request $request): Request
    {
        //
    }

    protected function isLastPage(Response $response): bool
    {
        //
    }

    protected function getPageItems(Response $response): array
    {
        //
    }
}
```
