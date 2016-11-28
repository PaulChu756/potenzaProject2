<?php
$paths = array(
    realpath(APPLICATION_PATH . '/../library')
);

if($modules = scandir(APPLICATION_PATH . '/modules/')){
    foreach($modules as $module){
        if(strpos($module,'.')===false && file_exists(APPLICATION_PATH . '/modules/' . $module . '/' . ucwords($module))){
            $paths[] = realpath(APPLICATION_PATH . '/modules/'.$module);
        }
    }
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, 
    array_merge(
        $paths,
        array(get_include_path())
    )
));
