# INCOMPATIBLE CHANGES

This log documents changes made to SunlightPHP that break previous installations.

## 2011

* **2011-02-10** Changed $params["url"] from array to string.
* **2011-02-10** Changed app/webroot/.htaccess to make Apache pass URL to $_GET["sunlightphp_url"] instead of $_GET["url"].
* **2011-02-09** Renamed $params["pass"] to $params["passed"].
* **2011-01-15** Removed SessionComponent->read() and ->write(), and added ->data[].

## 2010

* **2010-12-13** Controller->Auth->allow() now requires arguments to be named like the controller's methods, not like the action (e.g. "load_more" instead of "load-more").
* **2010-12-02** Removed caching from HtmlHelper and FormHelper.
* **2010-12-02** Removed HtmlHelper->element() and FormHelper->element().
* **2010-11-26** Removed Inflector.
* **2010-10-13** Removed parameter $option from Element->toString().
* **2010-10-10** Added parameter $ttl to HtmlHelper->script() which reverses the default caching behavior from "Don't cache" to "Do cache".
* **2010-10-04** Removed LogComponent. Introduced Log as static core class.
* **2010-09-05** Removed parameters $method and $jsonDecodeResponse from Model->getView().
* **2010-09-05** Changed return values of Model->getView(). Instead of returning array($headers, $data), it now returns only $data["rows"].
* **2010-09-05** Removed parameter $method from Model->getDocument().
* **2010-09-05** Changed return values of Model->getDocument(), Model->storeDocument(), Model->updateDocument() and Model->deleteDocument(). Instead of returning array($headers, $data), they now return only $data.
* **2010-09-05** Renamed Model->validate to Model->validationRules. Changed structure of validation rules.
* **2010-09-04** Added parameter $revision to Model->updateDocument() which changed the order of parameters.