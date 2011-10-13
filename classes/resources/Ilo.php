<?php

require_once("classes/resources/Material.php");

Class Ilo extends Material
{
	########################################################
    #### Member Variables ##################################
    ########################################################

	protected $content = null;
	protected $type = null;
	protected $typeName = null;
	protected $typeId = null;

	########################################################
    #### Constructor ################# #####################
    ########################################################
	public function __construct($id = null, $content = null, $typeName = null)
	{
		# Set ID	
		if (!empty($id))
		{	
			# Set ID
			$this->id = $id;

			# Load from ID
			$this->loadById($this->id);
		}
		
		# Set content 
		if (!empty($content))
		{
			$this->content = $content;
		}

		# Set typename
		if (!empty($typeName))
		{
			$this->typeName = $typeName;
		}
	}

	########################################################
	#### Helper functions for loading ######################
	########################################################
	
	public function loadById($id)
	{
		$this->id = $id;
		$query = sprintf("SELECT * FROM ilo WHERE id = %s", pg_escape_string($this->id));
		$result = $GLOBALS['transaction']->query($query);
        if($result=="none")
        {
            $query = sprintf("SELECT * FROM ilo_history WHERE id = %s", pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
        }
        
		if ($result!=="none")
		{
			$row = $result[0];
			$this->typeId = $row['ilo_type_id'];
			$this->content = json_decode($row['content']);
			if(!empty($this->typeId))
            {
                $this->setTypeNameById($this->typeId);
            }
			return true;
		}
        
		return false;
	}

	########################################################
	#### Database interface functions ######################
	########################################################

	# Saves ILO to DB (always creates a new one, moving old to history table if necessary)
	public function save($userId,$newIloIds)
	{
        if(!in_array($this->id,$newIloIds))
        {
            return;
        }

		# Sanity Check
		if (!empty($this->id) && !empty($this->typeName) && !empty($this->content))
		{
			# Set Type ID if we only have name
			if (empty($this->typeId))
			{
				$this->setTypeIdByName($this->typeName);
			}
            
            $query = sprintf("SELECT count(*) AS count FROM ilo WHERE id = %s",pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            
            $row = $result[0];

            # ILO is not in ilo table
            if($row['count'] == 0)
            {
                $query = sprintf("SELECT count(*) AS count FROM ilo_history WHERE id = %s",pg_escape_string($this->id));
                $result = $GLOBALS['transaction']->query($query);

                $row = $result[0];
                
                # ILO is not in ilo_history table either
                if($row['count']==0)
                {
                    $query = sprintf("INSERT INTO ilo (id,ilo_type_id,content,user_id) VALUES (%s,%s,'%s','%s')",
                                      pg_escape_string($this->id),
                                      pg_escape_string($this->typeId),
                                      pg_escape_string($this->content),
                                      pg_escape_string($userId));

                    $GLOBALS['transaction']->query($query,25);

                    return true;
                }
                # ILO is in ilo_history table
                else
                {
                    Ilo::resurrectIlo($this->id);
                }
            }
            
            return false;
		}
        
		return false;
	}
    
    public static function killIlo($iloId)
    {
        $query = sprintf("SELECT * FROM ilo WHERE id=%s",pg_escape_string($iloId));
        $tmp = $GLOBALS['transaction']->query($query);
        if($tmp!=="none")
        {
            $deadILOData = $tmp[0];
            $query = sprintf("DELETE FROM ilo WHERE id=%s",pg_escape_string($iloId));
            $GLOBALS['transaction']->query($query);
            $query = sprintf("INSERT INTO ilo_history (id,ilo_type_id,content,user_id) VALUES (%s,%s,'%s','%s')",
                                      pg_escape_string($deadILOData['id']),
                                      pg_escape_string($deadILOData['ilo_type_id']),
                                      pg_escape_string($deadILOData['content']),
                                      pg_escape_string($deadILOData['user_id']));
            $GLOBALS['transaction']->query($query);
        }
        else
        {
            $query = sprintf("SELECT * FROM ilo_history WHERE id=%s",pg_escape_string($iloId));
            $GLOBALS['transaction']->query($query,26);
        }
    }
    
    public static function resurrectIlo($iloId)
    {
        $query = sprintf("SELECT * FROM ilo_history WHERE id=%s",pg_escape_string($iloId));
        $tmp = $GLOBALS['transaction']->query($query);
        if($tmp!=="none")
        {
            $deadILOData = $tmp[0];
            $query = sprintf("DELETE FROM ilo_history WHERE id=%s",pg_escape_string($iloId));
            $GLOBALS['transaction']->query($query);
            $query = sprintf("INSERT INTO ilo (id,ilo_type_id,content,user_id) VALUES (%s,%s,'%s','%s')",
                                      pg_escape_string($deadILOData['id']),
                                      pg_escape_string($deadILOData['ilo_type_id']),
                                      pg_escape_string($deadILOData['content']),
                                      pg_escape_string($deadILOData['user_id']));
            $GLOBALS['transaction']->query($query);
        }
        else
        {
            $query = sprintf("SELECT * FROM ilo WHERE id=%s",pg_escape_string($iloId));
            $GLOBALS['transaction']->query($query,27);
        }
    }

	public function delete()
	{
		if (!empty($this->id) && is_int($this->id))
		{
			$query = sprintf("DELETE FROM ilo WHERE id = %s", pg_escape_string($this->id));
			$result =$GLOBALS['transaction']->query($query,95);
			if ($result)
			{
				return true;
			}
		}
		return false;
	}

	########################################################
	#### Getters and Setters ######## ######################
	########################################################

	# Get ID
	public function getId()
	{
		return $this->id;
	}

	# Get Type Id
	public function getTypeId()
	{
		return $this->typeId;	
	}

	# Get Type Name
	public function getTypeName()
	{
		return $this->type;
	}

	# Get Content
	public function getContent()
	{
		return $this->content;
	}

	# Set Content
	public function setContent($content)
	{
		$this->content = $content;
	}

	# Set type id and type name from type name
	public function setTypeIdByName($typeName)
	{
		$this->typeName = $typeName;
		$query = sprintf("SELECT id FROM ilo_type WHERE name = '%s'", pg_escape_string(strtolower($this->typeName)));
		$result = $GLOBALS['transaction']->query($query,28);

        $this->typeId = $result[0]['id'];
        return true;
	}

	# Set type id and type name from type id
	public function setTypeNameById($typeId)
	{
		$this->typeId = $typeId;
		$query = sprintf("SELECT name FROM ilo_type WHERE id = %s", pg_escape_string(strtolower($this->typeId)));
		$result = $GLOBALS['transaction']->query($query,29);

        $this->typeName = $result[0]['name'];
        return true;
	}
}
