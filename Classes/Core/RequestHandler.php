<?php
namespace Kitsunet\FlowFastRoute\Core;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Http\Component;
use TYPO3\Flow\Http\HttpRequestHandlerInterface;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;

/**
 * A request handler that works with fast routes.
 *
 * @Flow\Scope("singleton")
 * @Flow\Proxy(false)
 */
class RequestHandler implements HttpRequestHandlerInterface {

	/**
	 * @var Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @var Component\ComponentChain
	 */
	protected $baseComponentChain;

	/**
	 * The "http" settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * @var mixed
	 */
	protected $handler;

	/**
	 * @var array
	 */
	protected $handlerArguments;

	/**
	 * Make exit() a closure so it can be manipulated during tests
	 *
	 * @var \Closure
	 */
	public $exit;

	/**
	 * @param Bootstrap $bootstrap
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
		$this->exit = function () { exit(); };
	}

	/**
	 * This request handler can handle any web request.
	 *
	 * @return boolean If the request is a web request, TRUE otherwise FALSE
	 * @api
	 */
	public function canHandleRequest() {
		if (PHP_SAPI === 'cli') {
			return FALSE;
		}

		$this->request = Request::createFromEnvironment();

		$fastRoutesFile = FLOW_PATH_CONFIGURATION . 'FastRoutes.php';
		if (is_file($fastRoutesFile)) {
			/** @var \FastRoute\Dispatcher $dispatcher */
			$dispatcher = include_once($fastRoutesFile);
			$routeInfo = $dispatcher->dispatch($this->request->getMethod(), $this->request->getUri()->getPath());
			switch ($routeInfo[0]) {
				case \FastRoute\Dispatcher::FOUND:
					$this->handler = $routeInfo[1];
					$this->handlerArguments = $routeInfo[2];
					return TRUE;
					break;
				case \FastRoute\Dispatcher::NOT_FOUND:
					return FALSE;
					break;
				case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
					return FALSE;
					break;
			}
		}

		return FALSE;
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler.
	 * @api
	 */
	public function getPriority() {
		return 200;
	}

	/**
	 * Handles a HTTP request
	 *
	 * @return void
	 */
	public function handleRequest() {
		$this->response = new Response();

		if (is_callable($this->handler)) {
			call_user_func($this->handler, $this->handlerArguments, $this->bootstrap, $this->response, $this->request);
		}

		$this->response->send();
		$this->exit->__invoke();
	}

	/**
	 * Returns the currently handled HTTP request
	 *
	 * @return Request
	 * @api
	 */
	public function getHttpRequest() {
		return $this->request;
	}

	/**
	 * Returns the HTTP response corresponding to the currently handled request
	 *
	 * @return Response
	 * @api
	 */
	public function getHttpResponse() {
		return $this->response;
	}

	/**
	 * Boots up Flow to runtime
	 *
	 * @return void
	 */
	protected function boot() {
		$sequence = $this->bootstrap->buildRuntimeSequence();
		$sequence->invoke($this->bootstrap);
	}

	/**
	 * Resolves a few dependencies of this request handler which can't be resolved
	 * automatically due to the early stage of the boot process this request handler
	 * is invoked at.
	 *
	 * @return void
	 */
	protected function resolveDependencies() {
		$objectManager = $this->bootstrap->getObjectManager();
		$this->baseComponentChain = $objectManager->get('TYPO3\Flow\Http\Component\ComponentChain');

		$configurationManager = $objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->settings = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Flow');
	}
}