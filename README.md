# Kitsunet.FlowFastRoute
An adoption of the [FastRoute package](https://github.com/nikic/FastRoute/) for Flow use.

**This is alpha software, might change a lot in the future.**

Currently this is meant to work together with the default Flow routing system, so if a Request didn't match the FastRoutes or even if the method was not allowed, the Flow routing kicks in.
This should become configurable in the future.

Also handlers given to the FastRoute routes NEED to be callables currently, so ideally a closure. The callable is given the following arguments:

* Array of (named) arguments extracted from the route
* \TYPO3\Flow\Core\Bootstrap instance to run more boot steps or get objects.
* \TYPO3\Flow\Http\Response instance, you can fill this and it will be send afterwards
* \TYPO3\Flow\Http\Request instance, to use further.

