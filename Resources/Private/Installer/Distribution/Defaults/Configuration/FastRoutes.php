<?php

/** Add your fast routes here. See https://github.com/nikic/FastRoute for more information
 * /* Currently the registered handler must be something callable (a Closure eg.) and will be given the following arguments:
 *
 * - Array of (named) arguments extracted from the route
 * - \TYPO3\Flow\Core\Bootstrap instance to run more boot steps or get objects.
 * - \TYPO3\Flow\Http\Response instance, you can fill this and it will be send afterwards
 * - \TYPO3\Flow\Http\Request instance, to use further.
 *
 * The Flow boot sequence will not have been run at this point, only package management and class loading are available.
 *
 */
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {


});

// This is necessary to give control to the RequestHandler.
return $dispatcher;