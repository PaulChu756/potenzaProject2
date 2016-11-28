<?php

class Ia_View_Helper_GearmanUserJobs extends Zend_View_Helper_Abstract
{
    /**
     * @param  string  $message
     * @param  string $type (default=info)
     * @param  string  $heading
     * @return string
     */
    public function gearmanUserJobs()
    {
        $xhtml = '';
        if(\Zend_Registry::isRegistered('auth')){
            $user = \Zend_Registry::get('auth');
            $tasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getAllByRequestUser($user);
            if(count($tasks)>0) {
                $xhtml = '<div class="user_jobs">
				<span class="dock-tab-btn"><i class="fa fa-chevron-down fa-fw" aria-hidden="true"></i></span>
				<div class="dock-container">
                <table class="table table-striped table-condensed">
                    <thead class="taskHeadings">
                    </thead>
                    <tbody class="taskData">
                    </tbody>
                </table></div></div>';
                $xhtml = '<style>
                .user_jobs {
                    padding: 5px 10px;
                    position: fixed;
                    bottom: 0px;
                    left: 0px;
                    z-index:100;
					border-top-right-radius: 20px;
					border-top-left-radius: 20px;
					background-color: rgba(0, 0, 0, .8) !important;
                }
                .user_jobs table {
                    margin-bottom: 0px;
                }
				.user_jobs tr{
					background-color: rgba(0, 0, 0, 0) !important;
					color:#FFF;
				}
				.user_jobs td{
					border:0px !important;
					font-size:11px;
				}
				.user_jobs td .removeTask i{
					color:#FFF !important;
				}
                .user_jobs .id {
                    width: 8%;
                } 
                .user_jobs .job_name{
                    width: 15%;
                }
                .user_jobs .percent_complete{
                    width: 27%;
                }
                .user_jobs .state{
                    width: 10%;
                    text-transform: uppercase;
                }
                .user_jobs .status{
                    width: 35%;
                }
                .user_jobs .control{
                    width: 5%;
                    text-align: right;
                }
                .user_jobs .status_message {
                    margin:0px;
                    padding:0px;
                    font-style: italic;
                    font-size: 0.8em;
                }              
				.user_jobs .progress {
					height: 15px;
					margin-top:2px;
					border-radius: 6px;
                    width: 100%;
                    margin-bottom: 0px;
				}
				.user_jobs .progress-bar{
					position:relative;
					border-radius: 6px;
					line-height: 16px;
				}
				.user_jobs .progress-bar:last-child:before{
					background: none;
				}
				
				.user_jobs .dock-tab-btn{
					position: absolute;
					display: inline-block;
					background: rgba(0, 0, 0, .8) !important;
					padding: 4px 9px;
					left: 20px;
					color: #FFF;
					cursor:pointer;
					display:none;
				}
				.user_jobs .dock-tab-btn{
					top: -28px;
				}
				.user_jobs .tab-icon{
					transform: rotate(180deg);
					-ms-transform: rotate(180deg); /* IE 9 */
					-webkit-transform: rotate(180deg); /* Chrome, Safari, Opera */
				}
                /* Sizing */
                @media (min-width:1600px) {
                    .user_jobs {
                        width: 60%;
                    }
				}
                @media (max-width:1599px) {
                    .user_jobs {
                        width: 75%;
                    }
                    .user_jobs .id {
                        width: 10%;
                    } 
                    .user_jobs .percent_complete{
                        width: 25%;
                    }
				}
                @media (max-width:1023px) {
                    .user_jobs {
                        width: 100%;
                    }
				}
                </style>'.$xhtml;
                $this->view->headScript()->captureStart(); ?>
                $(document).ready(function(){
                    var fields = ['id','job_name','state','status','percent_complete'];
                    function update_table(){
                        $.getJSON("<?=$this->view->url(array('module'=>'default','controller'=>'cron','action'=>'gearman-monitor','format'=>'json','user_id'=>$user->id));?>", 
                            function( data ) {
                                $('.user_jobs tbody.taskData').empty();
                                $('.user_jobs thead.taskHeadings').empty();
                                var first = true;
                                $.each( data, function( rowNumber, rowData ) {
                                    var $row = $('<tr />');
                                    if(rowData['percent_complete'] < 100)
                                        $removeTask = $('<td class="control"><a class="pointer removeTask cancel" data-task-id="' + rowData['id'] + '"><i class="glyphicon glyphicon-ban-circle"></i></a></td>');
                                    else
                                        $removeTask = $('<td class="control"><a class="pointer removeTask" data-task-id="' + rowData['id'] + '"><i class="glyphicon glyphicon-remove"></i></a></td>');                                 
                                    $.each( rowData, function( key, value ) {
                                        if(fields.indexOf(key)>(-1)){
                                            if(!value)
                                                value = '';
                                            switch(key){
												case 'id':
                                                    $row.append('<td class="' + key + '">Job #' + value + '</td>');
                                                    break;
												case 'job_name':
                                                    if(rowData['job_url']){
                                                        $row.append('<td class="' + key + '"><a href="' + rowData['job_url'] + 
                                                            '">' + value + '</a></td>');
                                                    } else {
                                                        $row.append('<td class="' + key + '">' + value + '</td>');
                                                    }
                                                    break;
												case 'status':
                                                    $row.append('<td class="' + key + '">' + value + '</td>');
                                                    break;
                                                case 'percent_complete':
                                                    if(rowData['status_message'])
                                                        var status = '<p class="status_message">' + rowData['status_message'] + '</p>';
                                                    else
                                                        var status = '';
                                                    var striped_class = 'progress-bar-striped ';
                                        
                                                    if(value >= 100){
                                                        value = 100;
                                                        striped_class = '';
                                                        status = '';
                                                    }
                                                    $row.append('<td class="' + key + '"><div class="progress"><div class="progress-bar ' + striped_class + 'active" role="progressbar" aria-valuenow="' + value + '" aria-valuemin="0" aria-valuemax="100" style="width:' + value + '%">' + value + '%</div></div>' + status + '</td>');
                                                    break;
                                                default:
                                                    $row.append('<td class="' + key + '">' + value + '</td>');
                                                    break;
                                            }
                                        }
                                    });
									$row.append($removeTask);
                                    $('.user_jobs tbody.taskData').append($row);
                                    first = false;
                                });
                                /**
                                 * remove me
                                 */
                                //clearInterval(refreshIntervalId);

                                if(first){
                                    if(refreshIntervalId){
                                        clearInterval(refreshIntervalId);
                                    }
                                    $('.user_jobs').hide();
                                } else {
                                    $('.user_jobs').show();
                                }

                            }
                        );
                    }
                    var refreshIntervalId = setInterval(update_table, 5000);
                    update_table();
                    $('.user_jobs').on('click', '.removeTask', function() {
                        var $row = $(this).parents('tr').eq(0);
                        var task_id = $(this).data('task-id');
                        if(!$(this).hasClass('cancel') || confirm('Are you sure you wish to cancel task #' + task_id)){
                            $.getJSON("/cron/gearman-task-delete/format/json/id/" + task_id, 
                                function(data) {
                                    for(i in data){
                                        if(data[i]['type']=='success'){
                                            $row.remove();
                                            if($('.user_jobs tbody.taskData tr').length==0){
                                                $('.user_jobs').remove();
                                                if(refreshIntervalId)
                                                    clearInterval(refreshIntervalId);
                                            }
                                        }
                                    }
                                }
                            );
                        }
                    });
                    var show = localStorage.getItem('show');
                    $('.dock-tab-btn').delay(2000).show();
                    if(show === 'true'){
                        $('.dock-container').hide();
                        $('.dock-tab-btn .fa').toggleClass('tab-icon');
                    }else{
                        $('.dock-container').show();
                    }

                    $('.dock-tab-btn').click(function(e){
                        $('.dock-container').slideToggle(500);	
                        $('.dock-tab-btn .fa').toggleClass('tab-icon');
                        e.preventDefault();
                        if(show == 'true')
                            localStorage.setItem('show', 'false');
                        else   
                            localStorage.setItem('show', 'true');
                    });
                });
                <?php $this->view->headScript()->captureEnd();
            }
        }
        return $xhtml;
    }
    
    protected $_em = null;

    protected $_dc = null;

    public function getEntityManager()
    {
        if($this->_dc === null){
            $this->_dc = \Zend_Registry::get('doctrine');
        }
        if($this->_em == null){
            $this->_em = $this->_dc->getEntityManager();
        }
        if(!$this->_em->isOpen()){
            $this->_em = $this->_dc->resetEntityManager();
        }
        return $this->_em;
    }

    

}
