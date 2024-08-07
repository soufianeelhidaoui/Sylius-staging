<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
	public function onKernelException(ExceptionEvent $event)
	{
		\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
			$scope->setTag('brand', $_ENV['BRAND']);
		});
	}
}
