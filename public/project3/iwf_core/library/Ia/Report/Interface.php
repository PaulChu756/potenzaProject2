<?php

interface Ia_Report_Interface {

    public function getTitle();

    public function getForm();
    
    public function getResults();
    
    public function renderResults(array $results);

}