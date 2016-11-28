$.fn.extend({

    accordionize : function(){

        return this.each(function(){

            $accordionWrapper = $('<div class="accordionWrapper"></div>');
            $form = $(this);
            $accordion = $('<div class="panel-group" id="accordion"></div>');
            var num_fieldsets = $form.find('fieldset').length;

            for(i=0;i<num_fieldsets;i++){
                var $fieldset = $form.find('fieldset').eq(0);

                if(i==(num_fieldsets-1)){
                    $submit = $fieldset.remove();
                    $accordion.find('div.panel-body').last().append($submit);
                    //$('div#collapse' + type_id + ' .panel-body').append($submit);
                } else {
                    if(i<(num_fieldsets-2)){
                        var continueButton = '<div><a class="btn btn-primary nextPanel">Continue</a></div>';
                    } else {
                        var continueButton = '';
                    }
                    if($fieldset.find('legend').length>0){
                        $legend = $fieldset.find('legend');
                        var title = $legend.text();
                        $legend.remove();
                    } else {
                        var title = 'N/A';
                    }
                    $fieldset.remove();
                    var $panel = $(['<div class="panel panel-default">',
                            '<div class="panel-heading">',
                              '<h4 class="panel-title">', 
                                '<a data-toggle="collapse" data-parent="#accordion" href="#collapse' + i + '">',
                                  title,
                                '</a>',
                              '</h4>',
                            '</div>',
                            '<div id="collapse' + i + '" class="panel-collapse collapse' + ((i==0) ? ' in' : '') + '">',
                              '<div class="panel-body">',
                                 continueButton,
                              '</div>',
                            '</div>',
                        '</div>'].join(''));
                    $panel.find('div.panel-body').prepend($fieldset);
                    $accordion.append($panel);
                }
            }

            $accordionWrapper.prepend($accordion);
            $form.append($accordionWrapper);

            $('a.nextPanel').click(function(){
                $thisPanel = $(this).parents('.panel').eq(0);
                $nextPanel = $thisPanel.next('.panel');
                $nextPanel.find('a[data-toggle="collapse"]').click();
            });

            $errorPanel = $form.find('.errors').eq(0).parents('.panel-collapse');
            if($errorPanel.length > 0 && !$errorPanel.hasClass('in')){
                $errorPanel.siblings('.panel-heading').find('a').click();
            }

        });

    }

});