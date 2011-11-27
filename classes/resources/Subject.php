<?php

require_once("classes/resources/Material.php");

Class Subject extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{
		$this->db = $GLOBALS['db'];
	}

	########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load from uri
	public function loadFromUri($uri)
	{
		if (!empty($uri))
		{
            $this->path = $uri;
			$this->id = parent::URIToId($uri,"subject");
			$query = sprintf("SELECT subject.*, field.name AS field_name FROM subject LEFT JOIN field ON (subject.field_id = field.id) WHERE subject.id='%s' ORDER BY element_order",  pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query,79);

            $row = $result[0];
            $this->parentId = $row['field_id'];
            $this->name = str_replace("_", " ", $row['name']);
            $this->description = $row['description'];
            $this->active = $row['active'];
            return true;
		}
		return false;
	}

	# Load by payload
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
				$this->parentId = (int)$payloadObj->parentId;
				$this->name = (string)$payloadObj->name;
				$this->description = (string)$payloadObj->description;
				$this->active = ((string)$payloadObj->active == "true") ? true : false;
				$query = sprintf("SELECT name FROM field WHERE id = %s", pg_escape_string($this->parentId));
				$result = $GLOBALS['transaction']->query($query,80);
				$row = $result[0];
				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}

	public function loadChildData()
	{
		if (!empty($this->id))
		{
			$query = sprintf("SELECT * FROM course WHERE subject_id = %s ORDER BY element_order", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query);
			if ($result!==false&&$result!=="none")
			{
				foreach($result as $row)
				{
					$path = "{$this->path}{$row['name']}/";
					$name = str_replace("_", " ", $row['name']);
					$this->childData[] = array("id" => $row['id'], "name" => $name, "description" => $row['description'], "path" => $path);
				}
				return true;
			}
		}
		return false;
	}	

	# Load array of children ids
	public function loadChildrenIds()
	{
		if (!empty($this->id))
		{
			$query = sprintf("SELECT id FROM course WHERE subject_id = %s", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query);
			if ($result!==false&&$result!=="none")
			{
				foreach($result as $row)
				{
					$this->childIds[] = $row['id'];
				}
				return true;
			}
		}
		return false;
	}

	########################################################
	#### User interface functions ##########################
	########################################################

	# Builds XML representation of object
	public function buildXML()
	{
		$this->loadChildData();
		$this->xml = "<subject><id>{$this->id}</id><parentId>{$this->parentId}</parentId><name>{$this->name}</name><description>{$this->description}</description><path>{$this->path}</path>";
		$this->xml .= ($this->active) ? "<active>true</active>" : "<active>false</active>";
		if (!empty($this->childData))
		{
			$this->xml .= "<courses>";
			foreach ($this->childData as $child)
			{
				$this->xml .= "<course><id>{$child['id']}</id><name>{$child['name']}</name><description>{$child['description']}</description><path>{$child['path']}</path></course>";
			}
			$this->xml .= "</courses>";
		}
		$this->xml .= "</subject>";
	}


	########################################################
	#### Database interface functions ######################
	########################################################

	# Save subject (creates one if no id is set)
	public function save()
	{
		if (!empty($this->parentId))
		{
			# Creating a new subject	
			if (empty($this->id))
			{
				$query = sprintf("INSERT INTO subject (field_id, name, description, active) VALUES (%s, '%s', '%s', '%s')", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active));
			}
			# Updating a subject
			else
			{
				$query = sprintf("UPDATE subject SET field_id = %s, name = '%s', description = '%s', active = '%s' WHERE id = %s", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active), pg_escape_string($this->id));
			}
			$result = $GLOBALS['transaction']->query($query,81);
            return true;
		}
		return false;
	} 

	# Removes subject from database
	public function delete()
	{
		if (!empty($this->id))
		{
			$query = sprintf("DELETE FROM subject WHERE id = %s", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query,82);
			
            return true;
		}
	}


	########################################################
	### Getters and Setters ################################
	########################################################

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function setActive($active)
	{
		$this->active = $active;
	}

	public function setFieldId($parentId)
	{
		$this->parentId = $parentId;
	}

	public function setName($name)
	{
		$this->name = name;
	}

	public function getFieldId()
	{
		return $this->parentId;
	}

	public function getActive()
	{
		return $this->active;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getXML()
	{
		return $this->xml;
	}
}
