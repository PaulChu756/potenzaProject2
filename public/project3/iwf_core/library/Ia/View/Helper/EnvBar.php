<?php

class Ia_View_Helper_EnvBar extends Zend_View_Helper_Abstract
{
    /**
     * @param  string  $message
     * @param  string $type (default=info)
     * @param  string  $heading
     * @return string
     */
    public function envBar()
    {
	if(APPLICATION_ENV=='production')
	    return '';
        $cache = Zend_Registry::get('cache');
        if(!$xhtml = $cache->load('envBar')) {
            $corePath = realpath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'..');
            $modulesPath = realpath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'modules');
            try{
                $coreGit = Ia_GitBranch::createFromGitRootDir($corePath)->getName();
                exec('cd '.$corePath.' ; git status '.$corePath, $coreOutput);
            } catch (\Exception $e) {
                $coreGit = false;
            }
            try{
                $moduleGit = Ia_GitBranch::createFromGitRootDir($modulesPath)->getName();
                exec('cd '.$modulesPath.' ; git status '.$modulesPath, $moduleOutput);
            } catch (\Exception $e) {
                $moduleGit = false;
            }        

            $modalXhtml = '';
            $xhtml = '<div class="env">Host: '.gethostname().' | Session: '.ini_get('session.gc_maxlifetime').' | Env: '.APPLICATION_ENV.' | Core: ';
            if($coreGit){
                if(strpos($coreOutput[max(array_keys($coreOutput))], 'working directory clean')!==false){
                    $coreStatus = 'glyphicon glyphicon-thumbs-up';
                } else {
                    $coreStatus = 'glyphicon glyphicon-warning-sign';
                }
                $xhtml .= '<a style="color:#fff;" data-toggle="modal" href="#core" data-target="#core">'.$coreGit.' <i class="'.$coreStatus.'"></i></a>';
                $modalXhtml .= $this->_modal('core','core git status','<pre>'.implode(chr(10),$coreOutput));
            } else {
                $xhtml .= 'NO REPO!';
            }
            $xhtml .= ' | Modules: ';
            if($moduleGit){
                if(strpos($moduleOutput[max(array_keys($moduleOutput))], 'working directory clean')!==false){
                    $moduleStatus = 'glyphicon glyphicon-thumbs-up';
                } else {
                    $moduleStatus = 'glyphicon glyphicon-warning-sign';
                }
                $xhtml .= '<a style="color:#fff;" data-toggle="modal" href="#module" data-target="#module">'.$moduleGit.' <i class="'.$moduleStatus.'"></i></a>';
                $modalXhtml .= $this->_modal('module','modules git status','<pre>'.implode(chr(10),$moduleOutput));
            } else {
                $xhtml .= 'NO REPO!';
            }        
            $xhtml .= '</div>';
            $xhtml .= $modalXhtml;  
            $xhtml = '<style>
            .env {
                position: fixed;
                bottom: 0px;
                right: 0px;
                background-color: red;
                color: white;
                padding: 5px;
                z-index:100;
            }
            </style>'.$xhtml;
            $cache->save($xhtml,'envBar');            
        }
        return $xhtml;
    }

    protected function _modal($id,$title,$body)
    {
        return '
        <!-- Modal -->
        <div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog" aria-labelledby="'.$id.'Label" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">'.$title.'</h4>
              </div>
              <div class="modal-body">
                '.$body.'
              </div>
            </div>
          </div>
        </div>';
    }

}
