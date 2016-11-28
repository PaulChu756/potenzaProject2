<?php 

class SpyFuApiRequestBuilder {

	protected $HTTP_HEADER_AUTHENTICATION = "Authentication";
	protected $HTTP_HEADER_TIMESTAMP = "Timestamp";
	
	function BuildRequest($httpMethod, $url, $queryString, $postData, $userId, $secretKey) {
	
		//get request object, without any auth stuff yet
		$urlWithQuery = $this->CombineUrlWithQueryString($url, $queryString);
		$request = new HTTP_Request2();
		
		//figure out all the authentication goodness
		$timestamp = gmdate("l, F d, Y g:i:s A");
		//$timestamp = gmdate("D, j M Y G:i:s ")."GMT";
		$message = $this->BuildMessage($httpMethod, $url, $queryString, $postData, $timestamp);
		$signature = $this->ComputeHash($secretKey, $message);
		
		//build request
		if ($httpMethod == "GET") {
			$httpMethod = HTTP_Request2::METHOD_GET;
		} else if ($httpMethod == "POST") {
			$httpMethod = HTTP_Request2::METHOD_POST;
		}
		
		//$request->setMethod($httpMethod);
		$request->setUrl($urlWithQuery);
		//$request->setBody($message);
		$request->setHeader($this->HTTP_HEADER_TIMESTAMP, $timestamp);
		$request->setHeader($this->HTTP_HEADER_AUTHENTICATION, $userId.":".$signature);
		
		return $request;
	
	}
	
	function CombineUrlWithQueryString($url, $queryString) {
	
		$sb = $url;
		
		if ($queryString != null) {
			$sb .= "?";
			$queryKeys = array_keys($queryString);
			$queryValues = array_values($queryString);
			for ($i = 0; $i < count($queryString); $i++) {
				$name = $queryKeys[$i];
				$value = $queryValues[$i];
				$sb .= $name;
				$sb .= "=";
				$sb .= $value;
				if ($i < count($queryString) - 1) {
					$sb .= "&";
				}
			}
		}
		
		return $sb;
	
	}
	
	function ComputeHash($secretKey, $message) {
	
		$key = array();
		$secretKey = strtoupper($secretKey);
		for ($i = 0; $i < strlen($secretKey); $i++) {
			$key[] = ord($secretKey[$i]);
		}
		$key = implode("", $key);
		$key = utf8_encode($secretKey);
		$msg = array();
		for ($j = 0; $j < strlen($message); $j++) {
			$msg[] = ord($message[$j]);
		}
		$msg = implode("", $msg);
		$msg = utf8_encode($message);
		$hmacKey = hash_hmac("sha256", $msg, $key, true);
		$hashString = base64_encode($hmacKey);
	
		return $hashString;
		
	}
	
	function BuildMessage($httpMethod, $url, $queryString, $postData, $timestamp) {
	
		if ($httpMethod == null) {
			throw new Exception("httpMethod must be GET or POST");
		}
		
		$methodUp = strtoupper($httpMethod);
		if ($methodUp != "GET" && $methodUp != "POST") {
			throw new Exception("httpMethod must be GET or POST");
		}
		
		if ($url == "" || $url == null) {
			throw new Eception();
		}
		
		try {
		
			//make sure we just have the path, no domain or protocol.
			if (!(substr($url,0, 1) == "/")) {
				if (substr($url, 0, 7) == "http://") {
					$url = substr($url, 7);
				} else if (substr($url, 0, 8) == "https://") {
					$url = substr($url, 8);
				}
				
				$indexOfSlash = strpos($url, "/");
				$url = $indexOfSlash == -1 ? $url : substr($url, $indexOfSlash);
			}
			
			$parameterMessage = $this->BuildParameterMessage($queryString, $postData);			
			$message = implode("\n", array($httpMethod, $timestamp, $url, $parameterMessage));
			
			//echo $message;
			return $message;
		}
		catch (Exception $e) {
			echo "Error", $e->getMessage(), "\n";
		}
		
	}
	
	function BuildParameterMessage($queryString, $postData) {
	
		$parameterCollection = $this->BuildParameterCollection($queryString, $postData);
		if ($parameterCollection == null) {
			return "";
		}
		
		$keyValueStrings = array();

		for ($i = 0; $i < count($parameterCollection); $i++) {
			$key = array_values($parameterCollection[$i])[0];
			$value = array_values($parameterCollection[$i])[1];
			
			$keyValueStrings[] = $key . "=" . $value;
		}
		
		$test = implode("&", $keyValueStrings);
		//echo $test;
		return $test;
	
	}
	
	function BuildParameterCollection($queryString, $postData) {
	
		$parameterCollection = array();
		
		
		$qstr = $this->AddNameValuesToCollection($parameterCollection, $queryString);
		$post = $this->AddNameValuesToCollection($parameterCollection, $postData);
		
		for ($i = 0; $i < count($qstr); $i++) {
			$parameterCollection[] = $qstr[$i];
		}
		
		for ($j = 0; $j < count($post); $j++) {
			$parameterCollection[] = $post[$j];
		}		
		
		sort($parameterCollection);
		return $parameterCollection;
		
	}
	
	function AddNameValuesToCollection($parameterCollection, $nameValueCollection) {
	
		if ($nameValueCollection == null) {
			return;
		}
		
		foreach ($nameValueCollection as $key => $value) {
			$pair = array($key, $value);
			
			$parameterCollection[] = $pair;
		}
		
		return $parameterCollection;
	
	}
	
}
