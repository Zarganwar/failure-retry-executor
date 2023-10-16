<?php


namespace Zarganwar\FailureRetryExecutor;


use Throwable;
use function is_bool;
use function is_callable;

class FailureRetryExecutor
{

	public const defaultMaxAttempts = 3;

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
		int $maxAttempts = self::defaultMaxAttempts,
		null|callable|bool $onFailure = null,
		null|callable $onSuccess = null,
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

				if ($onFailure === null || is_bool($onFailure) && $onFailure) {
					throw $e;
				}

				if (is_callable($onFailure)) {
					$onFailure($e);
				}
			}
		}

		throw new RetryLimitExceededException("Max attempts '{$maxAttempts}' reached without success detected");
	}

}
