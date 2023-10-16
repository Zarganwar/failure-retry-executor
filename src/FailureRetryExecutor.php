<?php


namespace Zarganwar\FailureRetryExecutor;


use Throwable;
use Zarganwar\FailureRetryExecutor\Exceptions\RetryLimitExceededException;
use function is_bool;
use function is_callable;

class FailureRetryExecutor
{

	/**
	 * @param callable $command
	 * @param int $maxAttempts
	 * @param callable|bool|null $onFailure
	 * @param callable|null $onSuccess
	 * @return void
	 * @throws RetryLimitExceededException
	 * @throws Throwable
	 */
	public static function execute(
		callable $command,
		null|callable|bool $onFailure = null,
		null|callable $onSuccess = null,
		int $maxAttempts = 3,
	): void
	{
		while ($maxAttempts > 0) {
			$maxAttempts--;

			try {
				$result = $command();

				if (is_callable($onSuccess)) {
					$onSuccess($result);
				} else {
					if (is_bool($result) && !$result) {
						continue;
					}
				}

				return;
			} catch (Throwable $e) {

				if ($onFailure === null) {
					continue;
				}

				if (is_bool($onFailure) && $onFailure) {
					throw $e;
				}

				if (is_callable($onFailure)) {
					$onFailure($e);
				}
			}
		}

		throw new RetryLimitExceededException("Maximum number of attempts exceeded");
	}

}
