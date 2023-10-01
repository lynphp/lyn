<?php
	
	namespace Lynphp\Lyn;
	
	class HTTPRequest
	{
		
		/**
		 * @var string
		 */
		public $accept;
		
		/**
		 * @var string
		 */
		private $uri;
		
		
		/**
		 * @var string
		 */
		private $method;
		
		public function __construct()
		{
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->uri = $_SERVER['REQUEST_URI'];
			$this->accept = $_SERVER['HTTP_ACCEPT'];
		}
		
		final public function getMethod():string{
			return $this->method;
		}
		
		final public function getURI():string{
			return $this->uri;
		}
		final public function getAccept(){
			return $this->accept;
		}
		
		final public function getHTMLResponse():HTTPResponse
		{
			return new HTTPResponse();
			
		}
		final public function getJSONResponse():JSONResponse
		{
			return new JSONResponse();
			
		}
		private function requestMimeType():string {
			// Parse the accept header
			$types = explode(",", $this->accept);
			foreach ($types as $type) {
				// Split the type and the quality factor
				$parts = explode(";", $type);
				// Assign a default quality factor of 1 if none is given
				$quality = isset($parts[1]) ? (float) substr($parts[1], 2) : 1;
				// Trim the whitespace and store the type and quality in an associative array
				$mime_types[trim($parts[0])] = $quality;
			}
			// Sort the array by quality factor in descending order
			arsort($mime_types);
			// Return the first element of the sorted array which has the highest quality factor
			return key($mime_types);
		}
	}