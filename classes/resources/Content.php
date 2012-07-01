<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/RevisionHistory.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/Citation.php");

abstract class Content extends Material
{
    ########################################################
	#### Member Variables ##################################
	########################################################

    protected $parentId = null;
    protected $name = null;
	protected $content = null;
	protected $ilos = array();
    protected $citations = array();
	protected $ilosIntact = null;
    protected $json = null;
    protected $notes = null;
    protected $userId = null;
    protected $type = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct($type)
	{
		# ILO's are not present by default
		$this->ilosIntact = false;
        $this->type = $type;
	}
    
    ########################################################
	#### Helper functions for loading object ###############
	########################################################
    
    # Load from path
	public function loadFromUri($uri,$dieOnFail=true)
	{
		if (!empty($uri))
		{
            $this->id = parent::URIToId($uri,$this->type);
            $this->path = $uri;

            if($this->loadFromId($this->id,$dieOnFail))
            {
                return true;
            }
		}
		return false;
	}
    
    # Load object vars from payload
	public function loadFromPayload($payload,$path)
	{
		try
		{
            if(isset($payload->content))
            {
                $content = html_entity_decode($payload->content);
                require_once("includes/htmlpurifier.php");
                $content = $purifier->purify($content);
            }
            else
            {
                $content = "<section></section>";
            }
            
            if(preg_match('/<script/',$content)>0)
            {
                Error::generateError(125,'Content: $content');
            }

            $content = preg_replace('/~~~~/',"{$payload->username} ".date('F jS, Y h:i:s A'), $content);
            
			$this->content = $content;
            $this->userId = User::usernameToId($payload->username);
            $this->path=$path;
			$this->loadILOsFromArray(json_decode($payload->ilos));
            $this->loadCitationsFromArray(json_decode($payload->citations));
			$this->ilosIntact = true;
			return true;
		}
		catch (Exception $e)
		{
            Error::generateError(15);
			return false;
		}
	}
    
    abstract public function loadFromId($id,$dieOnFail=true);
    abstract public function buildJSON();
    
    ########################################################
	#### Database interface functions ######################
	########################################################

	# Save discussion (creates one if no id is set)
	abstract public function save($userId,$notes=null);
    abstract public function delete($userId=null);
    
    
    # Marks discussion inactive
	public function disable()
	{
		$this->active = false;
		$this->save();
	}
    
    # Save's ilo's to DB
    public function saveIlos($userId,$newILOIds,$oldILOIds)
	{
        $deadILOs = array_diff($oldILOIds,$newILOIds);
        
        foreach($deadILOs as $ilo)
        {
            Ilo::killIlo($ilo);
        }
        
		foreach($this->ilos as $ilo)
		{
			$ilo->save($userId,$newILOIds);
		}
	}
    
    ########################################################
	#### Functions for working with ILO's ##################
	########################################################
    
    public function setILOs($ilos)
	{
		# Kill old ilos
		unset($this->ilos);

		# Setup pattern for type extraction
		foreach ($ilos as $id => $ilo)
		{
            $type= $ilo->type;
            $id = substr($id, 3);
            $content = json_encode($ilo);
            $this->ilos[$id] = new Ilo($id, $content, $type);
		}
		return true;
	}
    
    # Load ILOs from Array
    public function loadILOsFromArray($ArrayOfILOs)
    {
		if(sizeof($ArrayOfILOs)>0)
		{
        	foreach ($ArrayOfILOs as $ndx => $ilo)
			{
				$tmp[$ndx] = $ilo;
			}
       		return $this->setILOs($tmp);
		}
		
		return;
    }
    
    public function loadIlos()
	{
		$this->ilos = array();
        $this->content = preg_replace('/&nbsp;/'," ",$this->content);
		$contentXML = new SimpleXMLElement("<parent>".$this->content."</parent>");
		$iloArray = $contentXML->xpath('//*[@data-ilotype]');
		foreach($iloArray as $index => $iloElement)
		{
			foreach($iloElement->attributes() as $name=>$value)
			{
				if($name=="id")
				{
					$id = preg_replace('/ilo/',"",$value);
					$this->ilos[$id] = new Ilo($id, null, null);
				}
			}	
		}

		if(!empty($this->ilos))
		{
			return true;
		}

		return false;
	}
    
    public static function getILOIds($html)
    {
        require_once('includes/html5lib/Parser.php');
        $dom = HTML5_Parser::parse(html_entity_decode(stripslashes("<html>".$html."</html>")));
        $xmlContent = new SimpleXMLElement($dom->saveXml());
        $iloPlaceHolderArray = $xmlContent->xpath("//*[starts-with(@id,'ilo')]");
        $iloIds = array();

        foreach($iloPlaceHolderArray as $placeholder)
        {
            array_push($iloIds, preg_replace("/ilo/","",(string)$placeholder->attributes()->id));
        }

        return $iloIds;
    }
    
    # Ensures that ILOs in HTML exist as submitted JSON
    public static function checkILOsExist($submittedILOs,$newILOIds)
    {
        $jsonILOIds = array();
        foreach($submittedILOs as $iloId=>$iloContent)
        {
            array_push($jsonILOIds,$iloId);
        }
        
        $missingILOs = array_diff($newILOIds,$jsonILOIds);
        
        foreach($missingILOs as $iloId)
        {
            $ilo = new Ilo();
            if(!$ilo->loadById($iloId))
            {
                Error::generateError(107);
            }
        }
    }
    
    ########################################################
	#### Functions for working with Citations ##############
	########################################################
    public function loadCitationsFromArray($ArrayOfCitations)
    {
        if(sizeof($ArrayOfCitations)>0)
		{
        	foreach ($ArrayOfCitations as $ndx => $ilo)
			{
				$tmp[$ndx] = $ilo;
			}
       		return $this->setCitations($tmp);
		}
		
		return;
    }
    
    public function setCitations($citations)
    {
        # Kill old ilos
		unset($this->citations);

		# Setup pattern for type extraction
		foreach ($citations as $id => $citation)
		{
            $id = substr($id, 8);
            $this->citations[$id] = new Citation();
            $payload = array("user_id"=>$this->userId,"course_id"=>Material::URIToId($this->path,"course"),"citation"=>$citation,"id"=>$id);
            $this->citations[$id]->loadFromPayload($payload);
		}
		return true;
    }
    
    public function saveCitations()
    {
        foreach($this->citations as $citation)
		{
			$citation->save();
		}
    }
    
    ########################################################
	### Getters and Setters ################################
	########################################################

	# Set content 
	public function setContent($content)
	{
		$this->content = $content;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}
    
	public function getName()
	{
		return $this->name;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getILOs()
	{
		return $this->ilos;
	}
    
    public function getJSON()
    {
        return $this->json;
    }

	public function getXML()
	{
		return $this->xml;
	}
        
	public function getPath()
	{
		return $this->path;
	}
    
    public function getLessonOrder()
    {
        return $this->order;
    }
}