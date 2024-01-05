# failure-retry-executor

# Usage

Simply run function `Zarganwar\FailureRetryExecutor\FailureRetryExecutor::execute()` with your command as first argument

```php
Zarganwar\FailureRetryExecutor\FailureRetryExecutor::execute(fn() => someFunction());
```

Your function will be executed and if it fails (throws an Exception or returns false), it will be executed again.
Max. default retries is 3. Of course, you can change it by passing `maxAttempts: int` argument.

```php
Zarganwar\FailureRetryExecutor\FailureRetryExecutor::execute(
    command: fn() => someFunction(),
    maxAttempts: 99,
);
```

You can also respond to the command result or failure by passing `onSuccess: callable` or/and `onFailure: callable` arguments.

```php
Zarganwar\FailureRetryExecutor\FailureRetryExecutor::execute(
    command: fn() => someFunction(),
    onSuccess: fn($result /* Your callable command result */) => doSomething($result),
    onFailure: fn(Throwable $throwable) => log($throwable),
);
```

Example of usage:

```php
// Some HTTP client
$client = new Client();
$logger = new Logger();

Zarganwar\FailureRetryExecutor\FailureRetryExecutor::execute(
    command: fn() => $client->get('https://example.com'),
    onSuccess: function(ResponseInterface $response): void 
    {       
        if ($response->getStatusCode() !== 200) {
            throw new Exception("Server responded with status code {$response->getStatusCode()} instead of 200");
        }
    },
    onFailure: fn(Throwable $throwable) => $logger->log($throwable),
    maxAttempts: 5,
);
```