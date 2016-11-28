<?php

class Ia_View_Helper_FormFileUploader extends Ia_View_Helper_FormFileMultiple
{
    public function formFileUploader($name, $attribs = null)
    {
        try{
            $once = !(Zend_Registry::isRegistered('once_'.$name));
            if($once){

                Zend_Registry::set('once_'.$name, true);

                $info = $this->_getInfo($name, null, $attribs);
                extract($info); // name, id, value, attribs, options, listsep, disable

                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/vendor/jquery.ui.widget.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.iframe-transport.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-process.js');
                $this->view->headScript()->appendFile('//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js');
                $this->view->headScript()->appendFile('//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
                $this->view->headScript()->appendFile('//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');
                $this->view->headScript()->appendFile('//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js');            
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-image.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-audio.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-video.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-validate.js');
                $this->view->headScript()->appendFile('/vendor/jquery-file-upload/js/jquery.fileupload-ui.js');
                $this->view->headLink()->appendStylesheet('/vendor/jquery-file-upload/css/jquery.fileupload.css');
            
                $this->view->headScript()->captureStart();
                ?>
                    
                    $(function () {
                        'use strict';

                        var $form = $('#<?=$this->view->escape($id);?>').parents('form').eq(0);

                        // Initialize the jQuery File Upload widget:
                        $form.fileupload({
                            // Uncomment the following to send cross-domain cookies:
                            //xhrFields: {withCredentials: true},
                            url: '<?=$this->view->url(array('module'=>'default','controller'=>'asset','action'=>'upload','accepts'=>$attribs['accepts']));?>',
                            autoUpload : true,
                            paramName : 'files'
                            <?php if(isset($attribs['maxNumberOfFiles'])): ?>,maxNumberOfFiles : <?=$attribs['maxNumberOfFiles'];?><?php endif; ?>
                        });

                        // Load existing files:
                        $form.addClass('fileupload-processing');
                        $.ajax({
                            // Uncomment the following to send cross-domain cookies:
                            //xhrFields: {withCredentials: true},
                            url: $form.fileupload('option', 'url'),
                            dataType: 'json',
                            context: $form[0]
                        }).always(function () {
                            $(this).removeClass('fileupload-processing');
                        }).done(function (result) {
                            $(this).fileupload('option', 'done')
                                .call(this, $.Event('done'), {result: result});
                        });
                        
                    });

                <?php
                $this->view->headScript()->captureEnd();

                // is it disabled?
                $disabled = '';
                if ($disable) {
                    $disabled = ' disabled="disabled"';
                }

                $xhtml = '<div class="row fileupload-buttonbar">
                    <div class="col-md-12">
                        <div class="well">
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="btn btn-success fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>Add files...</span>
                                ' . $this->formFileMultiple($name, $attribs) .
                            '</span><span class="text-center text-muted">  (Or drag and drop files here.)</span>
                            <!-- The global file processing state -->
                            <span class="fileupload-process"></span>                                        
                        </div>
                    </div>
                    <!-- The global progress state -->
                    <div class="col-md-12 fileupload-progress fade hide">
                        <!-- The global progress bar -->
                        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                        </div>
                        <!-- The extended global progress state -->
                        <div class="progress-extended">&nbsp;</div>
                    </div>
                </div>
                <!-- The table listing the files available for upload/download -->
                <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
                <!-- The template to display files available for upload -->
                <script id="template-upload" type="text/x-tmpl">
                {% for (var i=0, file; file=o.files[i]; i++) { %}
                    <tr class="template-upload fade">
                        <td colspan="1">
                            <p class="name">{%=file.name%}</p>
                            <strong class="error text-danger"></strong>
                        </td>
                        <td colspan="2">
                            <p class="size">Processing...</p>
                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
                        </td>
                        <td>
                            <button class="btn btn-warning cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel</span>
                            </button>
                        </td>
                    </tr>
                {% } %}
                </script>
                <!-- The template to display files available for download -->
                <script id="template-download" type="text/x-tmpl">
                {% for (var i=0, file; file=o.files[i]; i++) { %}
                    <tr class="template-download fade">
                        <td>
                            <span class="preview">
                                {% if (file.thumbnailUrl) { %}
                                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}"><img src="{%=file.thumbnailUrl%}"></a>
                                {% } %}
                            </span>
                        </td>
                        <td>
                            <p class="name">
                                {% if (file.url) { %}
                                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}">{%=file.name%}</a>
                                {% } else { %}
                                    <span>{%=file.name%}</span>
                                {% } %}
                            </p>
                            {% if (file.error) { %}
                                <div><span class="label label-danger">Error:</span> {%=file.error%}</div>
                            {% } %}
                        </td>
                        <td>
                            <span class="size">{%=o.formatFileSize(file.size)%}</span>
                        </td>
                        <td>
                            {% if (file.deleteUrl) { %}
                                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields=\'{"withCredentials":true}\'{% } %}>
                                    <i class="glyphicon glyphicon-trash"></i>
                                    <span>Delete</span>
                                </button>
                            {% } else { %}
                                <button class="btn btn-warning cancel">
                                    <i class="glyphicon glyphicon-ban-circle"></i>
                                    <span>Cancel</span>
                                </button>
                            {% } %}
                        </td>
                    </tr>
                {% } %}
                </script>';

                return $xhtml;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

}