# CRUD Exception Handlers

## Overview
CRUD Exception Handlers provide a centralized and structured way to handle exceptions that occur during CRUD operations. This allows for consistent error responses and better separation of concerns.

## Structure

### CRUDExceptionHandler (Abstract Class)
The base abstract class that defines the contract for all exception handlers.

**Location:** `src/CRUDExceptionHandlers/CRUDExceptionHandler.php`

**Methods:**
- `abstract public static function handle(Exception $exception)` - Handle the exception and return a JSON response or null
- `abstract public static function canHandle(Exception $exception): bool` - Check if the handler can handle the given exception

### DeletingExceptionHandler
A concrete implementation that handles exceptions specific to deleting operations.

**Location:** `src/CRUDExceptionHandlers/DeletingExceptionHandler.php`

**Purpose:** 
- Catches `QueryException` instances that occur during delete operations
- Identifies foreign key constraint violations
- Returns a user-friendly error message with appropriate HTTP status code

**Features:**
- Detects MySQL foreign key constraint violations (error codes: 23000, 1451, 1452)
- Checks both **current exception** and **previous exception** (exception chaining support)
- Returns JSON response with error message: "This record is not available for deletion."
- Uses HTTP status code 422 (Unprocessable Entity)
- Utilizes PixelResponseExtender via `Response::error()` facade

## How It Works

When a delete operation fails due to database constraints:

1. `DeletingService` catches the exception in its `delete()` method
2. The exception is passed to `DeletingExceptionHandler::handle()`
3. The handler checks **both current and previous exceptions**:
   - Calls `isCurrentExceptionDeletingFailingException()` to check the current exception
   - Calls `isPreviousExceptionDeletingFailingException()` to check if there's a previous exception wrapped inside
4. If either is a deleting error (foreign key constraint violation), it renders a JSON error response
5. The response is returned to the client with:
   - Message: "This record is not available for deletion."
   - Status Code: 422

### Why Check Previous Exception?

Exception chaining occurs when:
- Laravel wraps a `QueryException` inside a generic `Exception`
- Your code catches and re-throws exceptions while preserving the original
- Middleware or event listeners wrap exceptions

Example scenario:
```php
try {
    $model->forceDelete(); // Throws QueryException
} catch (QueryException $e) {
    throw new RuntimeException("Delete failed", 500, $e); // $e is now "previous"
}
```

The handler checks the previous exception to ensure we don't miss the original `QueryException` that contains the foreign key constraint information.

## Integration with DeletingService

The `DeletingService` has been updated to use the `DeletingExceptionHandler`:

```php
public function delete(bool $forcedDeleting = true) : JsonResponse
{
    try {
        $this->setForcedDeletingStatus($forcedDeleting);
        $this->DeleteConveniently();
        $this->doBeforeSuccessResponding();
        return Response::success($this->getSuccessResponseData() , [$this->getModelDeletingSuccessMessage()]);
    } catch (Exception $exception) { 
        // Try to handle the exception with DeletingExceptionHandler first
        if ($handledResponse = DeletingExceptionHandler::handle($exception)) {
            return $handledResponse;
        }

        // If not handled by DeletingExceptionHandler, use default error handling
        $this->doBeforeErrorResponding($exception);
        return $this->errorRespondingHandling($exception , $this->getNotDeletedArray());
    }
}
```

## Key Methods in DeletingExceptionHandler

### `handle(Exception $exception)`
Main entry point that checks both current and previous exceptions for deleting errors.

### `isCurrentExceptionDeletingFailingException(Throwable $exception)`
Checks if the current exception is a deleting error.

### `isPreviousExceptionDeletingFailingException(Throwable $exception)`
Retrieves the previous exception (if exists) and checks if it's a deleting error.

### `isItDeletingFailingException(Throwable $exception)`
Core logic that determines if an exception is a foreign key constraint violation by checking:
- Exception type is `QueryException`
- Error code is `23000`
- Error message contains specific keywords like:
  - `SQLSTATE[23000]`
  - `Integrity constraint violation`
  - `foreign key constraint fails`
  - `Cannot delete or update a parent row`

### `isItQueryException(Throwable $exception)`
Helper method to check if exception is a `QueryException`.

### `renderDeletingErrorResponse()`
Returns the standardized JSON error response.

## Creating Custom Exception Handlers

To create a new exception handler:

1. Create a new class that extends `CRUDExceptionHandler`
2. Implement the `handle()` method to process the exception
3. Implement the `canHandle()` method to determine if your handler should process the exception
4. Return a `JsonResponse` from `handle()` if the exception is handled, or `null` to pass to the next handler

**Example:**

```php
class CustomExceptionHandler extends CRUDExceptionHandler
{
    public static function handle(Exception $exception)
    {
        if (!static::canHandle($exception)) {
            return null;
        }
        
        // Your custom handling logic
        return Response::error('Custom error message', 400);
    }
    
    public static function canHandle(Exception $exception): bool
    {
        return $exception instanceof YourCustomException;
    }
}
```

## Benefits

1. **Centralized Error Handling:** All deletion-related exceptions are handled in one place
2. **Consistent Response Format:** All error responses use the same format via PixelResponseExtender
3. **Easy to Extend:** New exception handlers can be added by extending the base class
4. **Separation of Concerns:** Exception handling logic is separated from business logic
5. **User-Friendly Messages:** Technical database errors are converted to user-friendly messages

