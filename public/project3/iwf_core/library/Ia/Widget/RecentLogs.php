<?php
class Ia_Widget_RecentLogs extends Ia_Widget_Abstract implements Ia_Widget_Interface {
    
    public function getName(){
        return 'Recent Logs';
    }
    
    public function getOutput(){
        $dql = "SELECT e FROM Ia\Entity\Log e ORDER BY e.id DESC";
        $query = $this->em->createQuery($dql);
        $query->setMaxResults(5);
        $results = $query->getArrayResult(); 
        if(sizeof($results)>0){
            $xhtml = '<table class="table table-striped table-condensed">';
            $xhtml .= '<thead><th>Date</th><th>Message</th></thead>';
            foreach($results as $result){
                $date = $result['created'];
                $url = $this->view->url(array('module'=>'default','controller'=>'log','action'=>'view','id'=>$result['id']));
                $xhtml .= '<tr><td>'.$date->format('Y-m-d').'</td><td><a href="'.$url.'">'.$result['message'].'</a></td></tr>';
            }
            $xhtml .= '</table>';
            return $xhtml;
        } else {
            return '<p>No Recent Logs Found</p>';
        }
    }


}