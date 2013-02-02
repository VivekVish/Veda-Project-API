<?php

require_once("classes/resources/Material.php");

Class Field extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

	########################################################
	#### Constructor and process function ##################
	########################################################

	# Constructor
	public function __construct()
	{

	}

	########################################################
	#### Helper functions for loading object ###############
	########################################################

	public function loadFromPayload($payload)
	{
		if (!empty($payload))
		{
			try
			{
				$payloadObj = new SimpleXMLElement($payload);
				if ((int)$payloadObj->id)
				{
					$this->id = (int)$payloadObj->id;
				}
				$this->name = (string)$payloadObj->name;
				$this->path = (string)$payloadObj->path;
				$this->description = (string)$payloadObj->description;
				$this->active = ((string)$payloadObj->active == "true") ? true : false;
				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}

	public function loadFromUri($uri)
	{
        $this->path = $uri;
        
        $this->id = parent::URIToId($uri);
		$query = sprintf("SELECT * FROM field WHERE id = '%s'", $this->id);
		$result = $GLOBALS['transaction']->query($query,23);
        
        $row = $result[0];
        $this->name = str_replace("_", " ", $row['name']);
        $this->description = $row['description'];
        $this->active = ($row['active']) ? true : false;
	}
    
    public function loadChildData()
	{
		if (!empty($this->id))
		{
			$field_uri_name = str_replace(" ", "_", $this->name);
			$query = sprintf("SELECT * FROM subject WHERE field_id = %s AND active IS TRUE", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query);
            
            foreach($result as $row)
            {
                $path = "/data/material/$field_uri_name/{$row['name']}/";
                $name = str_replace("_", " ", $row['name']);
                $this->childData[] = array("id" => $row['id'], "name" => $name, "description" => $row['description'], "path" => $path);
            }	
            return true;
		}	
		return false;
	}

	########################################################
	#### Display Functions #################################
	########################################################
    
    public function buildJSON()
    {
        $this->loadChildData();
        $jsonArray =array("id"=>$this->id,"name"=>$this->name,"description"=>$this->description,"path"=>$this->path,"active"=>$this->active,"children"=>$this->childData);
        $this->json = json_encode($jsonArray);
    }

	########################################################
	#### Database interface functions ######################
	########################################################

	# Save field (creates one if no id is set)
	public function save()
	{
		if (!empty($this->id) || !empty($this->name))
		{
			# Field Exists
			if (!empty($this->id))
			{
				$query = sprintf("UPDATE field SET name = '%s', description = '%s', active = '%s' WHERE id = %s", pg_escape_string(str_replace(" ", "_", $this->name)), pg_escape_string($this->description), pg_escape_string($this->active), pg_escape_string($this->id));
			}
			# New field
			else
			{
				$query = sprintf("INSERT INTO field (name, description, active) VALUES ('%s', '%s', '%s')", pg_escape_string(str_replace(" ", "_", $this->name)), pg_escape_string($this->description), pg_escape_string($this->active));
			}

			# Run query
			$result = $GLOBALS['transaction']->query($query);
		}
		return false;
	} 

	# Removes field from database
	public function delete()
	{
		if (!empty($this->id))
		{
			$query = sprintf("DELETE FROM field WHERE id = %s", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']-query($query);
			return true;
		}
		return false;
	}


	########################################################
	### Getters and Setters ################################
	########################################################

	public function setName($name)
	{
		$this->name = name;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getId()
	{
		return $this->id;
	}
}
