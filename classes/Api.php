<?php

Class API 
{

    ########################################################
    #### Member Variables     ##############################
    ########################################################
    
	private $request = null;
	private $response = null;
	private $resourceHandler = null;
	private $baseResource = null;
	private $resourceDepth = null;
	private $status = null;

    ########################################################
    #### Constructor          ##############################
    ########################################################

	# Constructor
	public function __construct()
	{
		# Get the request object
		$this->request = new Request();

		# Get the response object
		$this->response = new Response();

        $resourcePath = new ResourcePath($this->request->getURI());
        $this->resourceHandler = $resourcePath->getResourceHandler();
	}

	########################################################
    #### Main Function        ##############################
    ########################################################

	# Process request
	public function process()
	{
			if (!empty($this->resourceHandler))
			{
				# Inject resource handler code
				require_once("htdocs/resources/{$this->resourceHandler}");
				
				if(count($GLOBALS['ERROR'])==0)
				{
					# Handler ran successfully 
					if ($this->status === true)
					{
                        $GLOBALS['transaction']->commit();
                        
						# Convert data to type specified in header
                        $formattedPayload = $this->formatData($this->response->getContentType(), $this->request->getAcceptType(), $this->response->getPayload());
                        
                        # Reset payload
                        $this->response->setPayload($formattedPayload);

                        # Send response
                        $this->sendResponse();
					}
					else
					{
						array_push($GLOBALS['ERROR'],"Status has not been set to true");
						$this->sendResponse();
					}
				}
				else
				{
					$this->response->setPayload($GLOBALS['ERROR']);
					$this->sendResponse();
				}
			}
			else
			{
				$this->send404();
			}
	}

	########################################################
    #### Getters and Setters         #######################
    ########################################################
	
	# Status
	public function setStatus($status)
	{
		$this->status = $status;
	}

	########################################################
    #### Data Operations         ###########################
    ########################################################

	# Convert response to specified format
	public function formatData($from, $to, $data)
	{
		if ($from != $to)
		{
			if (strtolower($from) == "text/xml" && strtolower($to) == "application/json")	
			{
				return $this->XMLtoJSON($data);
			}
			elseif ($from == strtolower("application/json") && $to == strtolower("text/xml"))
			{
				return $this->JSONtoXML($data);
			}	
		}
		return $data;
	}

	# XML to JSON
	public function XMLtoJSON($xml)
	{
		# Convert to Simple XML Object
		try
		{
			$xmlObj = new SimpleXMLElement($xml); 

			# Sanity Check
    		if ($xmlObj != null) 
			{
    			# Convert xmlObj to phpArray 
    			$xmlArr = xml2json::convertSimpleXmlElementObjectIntoArray($xmlObj);

				# Sanity Check
    			if (($xmlArr != null) && (sizeof($xmlArr) > 0)) 
				{ 
        			# Create a new instance of Services_JSON
        			$jsonObj = new Services_JSON();

        			# Let us now convert it to JSON formatted data.
        			$jsonOutput = $jsonObj->encode($xmlArr);
    			} 

				# Return 
    			return($jsonOutput); 
    		}
			# Failure
			return false;
		}
		catch (Exception $e)
		{
			# Fail
			return false;
		}
	}
			

	# JSON to XML
	public function JSONtoXML($json)
	{
		$serializer = new XML_Serializer();
    	$obj = json_decode($json);
    	if ($serializer->serialize($obj)) 
		{
        	return $serializer->getSerializedData();
    	}
    	else 
		{
        	return false;
    	}
	}
	
	########################################################
    #### Client Interface Functions  #######################
    ########################################################

	public function send200()
	{
		header("HTTP/1.0 200 Success");
	}

	public function send404()
	{
		header("HTTP/1.0 404 Not Found");
		echo "Not Found";
	}

	public function send400()
	{
	}

	public function sendContentTypeHeader()
	{
		$contentTypeString = "Content-Type: {$this->request->getAcceptType()}";
		header($contentTypeString);
	}

	public function sendResponse()
	{
		$this->send200();
		$this->sendContentTypeHeader();
		print_r($this->response->getPayload());
	}
}
?>