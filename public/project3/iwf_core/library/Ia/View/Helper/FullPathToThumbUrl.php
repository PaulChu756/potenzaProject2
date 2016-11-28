<?php

class Ia_View_Helper_FullPathToThumbUrl extends Zend_View_Helper_Abstract
{
    
    public function fullPathToThumbUrl($fullPath, $relativePath = true, $thumbnail_width = 250)
    {
        $phpThumb = Zend_Registry::get('phpThumb');
        $originalFile = $this->_getOriginalFilePath($fullPath);
        $parts1 = explode('/',$originalFile);
        $filename = array_pop($parts1);
        $parts2 = explode('.',$filename);
        $parts2[max(array_keys($parts2))-1] .= '-THUMBx'.$thumbnail_width;
        $parts1[] = implode('.',$parts2);
        $outputFile = implode('/',$parts1);
        if(!file_exists($outputFile)){
            $phpThumb->setSourceData(file_get_contents($originalFile));
            $phpThumb->setParameter('w', $thumbnail_width);
            $phpThumb->setParameter('bg','ffffff');
            if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
                if ($phpThumb->RenderToFile($outputFile)) {
                    // do something on success
                } else {
                    /*
                    echo '<h1>Could Not Write Thumbnail</h1>';
                    var_dump($phpThumb->debugmessages);
                    error_log($phpThumb->debugmessages);
                    $phpThumb->purgeTempFiles();
                    exit;
                    */
                }
                $phpThumb->purgeTempFiles();
            } else {
                /*echo '<h1>Could Not Generate Thumbnail</h1>';
                echo '<em>'.$originalFile.'</em>';
                echo file_get_contents($originalFile);
                var_dump($phpThumb->debugmessages);
                error_log($phpThumb->debugmessages);
                $phpThumb->purgeTempFiles();
                exit;
                */
            }
            $phpThumb->resetObject();
        }
        if($relativePath && strpos($outputFile, 'application/modules')!==false){
            $parts = explode('application/modules',$outputFile);
            $parts2 = explode('/',$parts[1]);
            $null = array_shift($parts2);
            $module = array_shift($parts2);
            $filename = array_pop($parts2);
            return '/asset/load/mod/'.$module.'/file/'.$filename;
        }
        return ($relativePath) ? str_replace(PUBLIC_PATH,'',$outputFile) : $outputFile;
    }

    protected function _getOriginalFilePath($fullPath)
    {
        $parts = explode('.',$fullPath);
        $ext = strtolower(array_pop($parts));
        switch($ext){
            case 'pdf':
                return PUBLIC_PATH."/img/adobe.png";
                break;
            case 'jpg':
            case 'png':
            case 'gif':
                return $fullPath;
                break;
            default:
                return PUBLIC_PATH."/img/logo.gif";
                break;
        }

    }

}