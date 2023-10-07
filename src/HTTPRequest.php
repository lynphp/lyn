<?php
	
	namespace Lynphp\Lyn;
	
	class HTTPRequest
	{
		
		/**
		 * @var string
		 */
		public $accept;
		/**
		 * @var mixed|string
		 */
		public string $sessionCookie;
		
		/**
		 * @var string
		 */
		private $uri;
		
		
		/**
		 * @var string
		 */
		private $method;
		
		private array $roles = [];
		
		public function __construct()
		{
			$this->roles[]='guest';
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->uri = $_SERVER['REQUEST_URI'];
			$this->accept = $_SERVER['HTTP_ACCEPT'];
			$this->sessionCookie = $_COOKIE['sessionCookie']??'';
		}
		
		final public function getMethod():string{
			return $this->method;
		}
		final public function getSessionCookie():string{
			return $this->sessionCookie;
		}
		final public function fromActiveSession():bool{
			return empty($this->getSessionCookie())===false;
		}
		final public function hasRole(string $role):bool{
			foreach($this->roles as $key=>$value){
				if($value === $role){
					return true;
				}
			}
			return false;
		}
		final public function getURI():string{
			return $this->uri;
		}
		final public function getAccept():string{
			return $this->accept;
		}
		
		/**
		 * @return HTMLResponse
		 */
		final public function getHTMLResponse():HTMLResponse
		{
			return new HTMLResponse($this);
			
		}
		final public function getJSONResponse():JSONResponse
		{
			return new JSONResponse();
			
		}
		final public function requestMimeType():string {
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
		
		public function addSlug(mixed $segment):void
		{
		}
		
		public function getParam(string $key)
		{
			return $_GET[$key];
		}
	}