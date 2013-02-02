<?php

require_once("classes/Error.php");

class Transaction
{
    ########################################################
	#### Member Variables ##################################
	########################################################
    private $db = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################
    // DESC: The constructor sets the database and begins the transaction
    // PARAMETER: $database is database
    // RETURNS: void
    public function __construct($database)
    {
        $this->db = $database;
        $this->db->query("BEGIN WORK");
    }
    
    // DESC: Makes a particular query and returns an array of returned rows
    // PARAMETER: $query is the query string
    // PARAMETER: $errorCode is a number corresponding to an error in the error
    //            defines file
    // RETURNS: boolean false if the query fails, "none" if no array is returned,
    //          and an array of rows if the query returns rows
    public function query($query, $errorCode = null)
    {
        $select = strtolower(substr($query,0,6))=="select";

        $rowArray = array();
        
        $result = $this->db->query($query);

        if($result)
        {
            while($row = $result->fetch(PDO::FETCH_ASSOC))
            {
                array_push($rowArray,$row);
            }
        
            if(count($rowArray)==0)
            {
                $rowArray = "none";
            }
        }
        else
        {
            $rowArray = false;
        }

        if($select)
        {
            if(($rowArray==="none"||$rowArray===false)&&!is_null($errorCode))
            {
                $this->rollback($errorCode,$query);
            }
            else if($rowArray===false)
            {
                $this->rollback(0, $query);
            }
        }
        else
        {
            if($rowArray===false&&!is_null($errorCode))
            {
                 $this->rollback($errorCode,$query);
            }
            else if($rowArray===false)
            {
                $this->rollback(0,$query);
            }
        }
        
        
        
        return $rowArray;
    }
    
    // DESC: Rolls all queries in the transaction back, dies if a non-null
    //       argument is passed to the function
    // PARAMETER: $fatalError is the string to be output when the program dies.
    // RETURNS: void
    public function rollback($errorCode,$query=null)
    {
        $this->db->query("ROLLBACK");
        if(!is_null($errorCode))
        {
            Error::generateError($errorCode,"Query: ".$query);
        }
    }
    
    // DESC: Commits all queries in the transaction
    // RETURNS: void
    public function commit()
    {
        $this->db->query("COMMIT");
    }
}

?>
