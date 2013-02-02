<?php

Class Request
{
	## Member Variables ##

	private $headers = null;
	private $contentType = null;
	private $acceptType = null;
	private $remoteIP = null;
	private $host = null;
	private $uri = null;
	private $method = null;
	private $stdinHandle = null;
	private $payload = null;
	private $handler = null;

	## Member Functions ##

	# Constructor
	public function __construct()
	{
		# Get all headers
		$this->headers = apache_request_headers();

		# Set Accept Type (Defaults to XML)
		if (empty($this->headers['Accept-Type']))
		{
			//$this->acceptType = "Text/XML";
			$this->acceptType = "text/xml";
		}
		else
		{
			$this->acceptType = $this->headers['Accept-Type'];
		}

		# Get other important request information
		$this->remoteIP = $_SERVER['REMOTE_ADDR'];	
		$this->host = $_SERVER['HTTP_HOST'];
		
		# Not all servers has SCRIPT_URL
		if (!empty($_SERVER['SCRIPT_URL']))   
			$this->uri = $_SERVER['SCRIPT_URL'];
		elseif (!empty($_SERVER['REDIRECT_URL'])) 
			$this->uri = $_SERVER['REDIRECT_URL'];
		elseif (!empty($_SERVER['REQUEST_URI'])) {
			$p = parse_url($_SERVER['REQUEST_URI']);
			$this->uri = $p['path'];
		}
		# Remove additional path
		$dir_pattern = '/^'.preg_quote(dirname($_SERVER['SCRIPT_NAME']), '/').'/i';
		$this->uri = preg_replace($dir_pattern, '', $this->uri);
		
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->handler = $_SERVER['SCRIPT_FILENAME'];
		switch (strtolower($this->method))
		{
			case 'get':
                $this->payload = $this->uri;
				break;
			case 'delete':
			case 'post':
			case 'put':
				$this->stdinHandle = fopen("php://input", "r");
				while ($tmp = fread($this->stdinHandle, 1024))
				{
					$this->payload .= $tmp;
				}
				break;
		}
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getURI()
	{
		return $this->uri;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function getContentType()
	{
		return $this->contentType;
	}

	public function getAcceptType()
	{
		return $this->acceptType;
	}
}
?>