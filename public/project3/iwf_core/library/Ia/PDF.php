<?php

class Ia_PDF {

    protected $_error = null;
    
    protected $_merger = null;

    protected $_filename = null;

    protected $_filecontents = null;

    protected $_objects = null;

    protected $_pagemap = null;

	public function __construct($filename=null)
	{
        $this->_filename = $filename;
        $lib_path = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR;
		require_once($lib_path.'fpdf/fpdf.php');
		require_once($lib_path.'fpdi/fpdi.php');
		require_once($lib_path.'fpdi/pdf_parser.php');
	}

    public function getObjects()
    {
        if($this->_objects===null){
            preg_match_all("#obj(.*)endobj#ismU", $this->getFileContents(), $objects);
            $this->_objects = @$objects[1];
        }
        return $this->_objects;
    }

    public function getPageNumberFromObject($object)
    {
        $pageNumber = 0;
        foreach($this->getPageMap() as $internalId){
            $pageNumber++;
            if(strpos($object, "/P ".$internalId)!==false){
                return $pageNumber;
            }
        }
        return false;
    }

    public function getPageMap()
    {
        if($this->_pagemap===null){
            foreach($this->getObjects() as $object){
                preg_match("/\/Count (.*) \/Kids \[ (.*) \] \/Type \/Pages/s", $object, $matches);
                if($matches[1]){     
                    $count = $matches[1];
                    $parts = explode(' ',$matches[2]);
                    $j=0;
                    for($i=0;$i<$count;$i++){
                        $this->_pagemap[] = $parts[$j];
                        $j += 3;
                    }
                    return $this->_pagemap;
                }       
            }
        }
        return $this->_pagemap;
    }

    public function getPageCount()
    {
        return sizeof($this->getPageMap());
    }

    public function getObjectByName($name)
    {
        foreach($this->getObjects() as $object){
            if(strpos($object,'('.$name.')')!==false){
                return $object;
            }
        }
        return false;
    }

    /**
     * param string $objecty
     */
    public function getCoordinatesFromObject($object)
    {
        preg_match("/Rect \[ (.*) \]/s", $object, $matches);
        if($matches[1]){
            $coords = explode(' ',$matches[1]);
            if(sizeof($coords)!=4)
                return false;
            return array(
                    'x1' => $coords[0],
                    'y1' => $coords[1],
                    'x2' => $coords[2],
                    'y2' => $coords[3]
                );
        }
        return false;
    }

    public function getFileContents()
    {
        if($this->_filename===null)
            throw new \Exception('No filename provided');
        else
            $this->_filecontents = file_get_contents($this->_filename);
        return $this->_filecontents;
    }
    
    public function getMerger()
    {
        if($this->_merger===null)
        {
            $this->_merger = new Ia_PDF_Merger;      
            $this->_merger->setIaPdf($this);
        }
        return $this->_merger;
    }
    
    public function validate($filename=null)
    {
        if($filename===null)
            $filename = $this->_filename;
        try{
            $pdfParser = new pdf_parser($filename);
        } catch(Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }
        return true;
    }
    
    public function repair($filename=null)
    {
        if($filename===null)
            $filename = $this->_filename;
        $repaired = str_replace('.pdf','-repaired.pdf',$filename);
        //repair just in case
        $cmd = '/usr/bin/gs -o "'.$repaired.'" -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress "'.$filename.'"';
        $shellOutput = shell_exec($cmd);  
        unlink($filename);
        return $repaired;
    }
    
    


}