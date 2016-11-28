var disableLoading = false;

var sortOption = function(op1,op2){
    return op1.innerHTML.toUpperCase().localeCompare(op2.innerHTML.toUpperCase());
}

var numericSortOption = function(op1,op2){
    return (parseInt(op1.innerHTML) > parseInt(op2.innerHTML)) ? 1 : -1;
}

function startLoading()
{
    $('body').append('<div id="loading-modal" class="modal-backdrop"><p class="loading"><img src="/img/loading.gif" alt="Loading..." />Please wait...</p></div>');
}

function stopLoading()
{
    $('#loading-modal').remove();
}

/* Stop Youtube Videos When Closing Modal */
/* Thanks! http://stackoverflow.com/a/24767841/421726 */
$('.modal').on('hidden.bs.modal', function () {
    if($(this).find('iframe')){
        $(this).find('iframe').attr("src", $(this).find('iframe').attr("src"));
    }
});


/* Last Tab */

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    //save the latest tab; use cookies if you like 'em better:
    if(!$(e.target).data('persist')=="false")
        localStorage.setItem('lastTab', $(e.target).attr('href'));
});

//go to the latest tab, if it exists:
var lastTab = localStorage.getItem('lastTab');
if (lastTab && $('[href="' + lastTab + '"]')){
    $('[href="' + lastTab + '"]').tab('show');
}


/*
 * http://stackoverflow.com/questions/1184624/convert-form-data-to-js-object-with-jquery
 */
$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    var k = [];
    $.each(a, function() {
            var hasBrackets = this.name.match(/\[(.*?)\]/);
            if(hasBrackets){
                var inputName = this.name.replace(hasBrackets[0],'');
                if (o[inputName] == undefined) {
                    o[inputName] = {};
                    k[inputName] = 0;
                } else {
                    k[inputName]++;
                }
                var inputNameKey = hasBrackets[1] || k[inputName];
                o[inputName][inputNameKey] = this.value || '';
            } else {
                o[this.name] = this.value || '';
            }
        
    });
    return o;
};

var IaAlertView = Backbone.View.extend({
    el: $("div.alerts"),
    template: _.template($('#ia-alerts').html()),
    render: function() {
        $test = $(this.template(this.model.toJSON()));
        if(this.model.get('type')=='success')
            $test.delay(2500).fadeOut(400);
        this.$el.append($test);
        return this;
    },          
});

var IaAlert = Backbone.Model.extend();

function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function commafy( num ) {
    var str = num.toString().split('.');
    if (str[0].length >= 4) {
        str[0] = str[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,');
    }
    if (str[1] && str[1].length >= 4) {
        str[1] = str[1].replace(/(\d{3})/g, '$1 ');
    }
    return str.join('.');
}

/*function sticky_relocate() {
    var window_top = $(window).scrollTop();
    $('.sticky-anchor').each(function(){
        var $stickyAnchor = $(this);
        var $stickies = $(this).siblings('.sticky');
        $stickies.each(function(){
            var $sticky = $(this);
            var div_top = $stickyAnchor.offset().top;
            if (window_top > div_top) {
                $sticky.addClass('stick');
            } else {
                $sticky.removeClass('stick');
            }
        })
    });
}*/

$(function(){

    /*$(window).scroll(sticky_relocate);
    sticky_relocate();*/

    if($('form.accordionize').length > 0)
        $('form.accordionize').accordionize();

    //@thanks http://stackoverflow.com/a/10524697/421726
    $('a[data-toggle="tab"]').on('shown', function (e) {
        //save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', $(e.target).attr('href'));
    });
    //go to the latest tab, if it exists:
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
      $('a[href="'+lastTab+'"]').tab('show');
    }
  
    $loading = $('<p class="loading"><img align="left" style="margin-right:5px;" src="/img/loading.gif" alt="Loading..." />Please wait...</p>');
    $loading.hide();
    /*$('body').after();
    $('button[type="submit"],input[name="submit"]').live('click',function(){
        if(!disableLoading)
            $(this).hide().after($loading.show());
    });*/
    
    $('input.datepicker').datepicker();

    /* Select all checkboxes in a table */
    $('input.selectAll[type="checkbox"]').change(function(){
        if($(this).data('table-selector')){
            $tbody = $($(this).data('table-selector')).find('tbody').eq(0);
        } else {
            $tbody = $(this).parents('table').eq(0).find('tbody').eq(0); /* assuming a well formed table */
        }
        $checkboxes = $tbody.find('input[type="checkbox"]');
        var $control = $(this);
        $checkboxes.each(function(){
            $(this).prop('checked',$control.is(':checked'));
        });
    });

    if($('.bulkActions').length>0){
        $('.bulkActions a').each(function(){
            if($(this).attr('onclick')){
                var onclick = $(this)[0].onclick;
                $(this).attr('onclick',false);
                $(this).data('onclick',onclick);
            }
        });
        $('.bulkActions a').click(function(){

            if($(this).data('onclick')){
                var onclick = $(this).data('onclick');
            } else {
                var onclick = function() { return true; };
            }

            if(onclick()){
                var container = $(this).parent('.bulkAction');
                var form = container.find('form');
                var href = $(this).attr('href');
                var ids = new Array;
                try{
                    $('td input[type="checkbox"]:checked').each(function(){
                        ids.push($(this).val());
                    });
                    var joinedIds = ids.join('+');
                    href = href.replace('{ids}',joinedIds);
                    if(form.length > 0){
                        $.ajax({
                            type: 'post',
                            url : href,
                            data: form.serialize(),
                            success: function(response) {
                                var error = false;
                                for(i in response){
                                    if(response[i].type!='success'){
                                        error = true;
                                        var thisAlert = new IaAlertView;
                                        thisAlert.model = new IaAlert(response[i]);
                                        thisAlert.render();
                                    }
                                }
                                if(!error)
                                    location.reload();
                            }
                        });
                    } else {
                        container.find('input,select').each(function(){
                            href += '/' + $(this).attr('name') + '/' + $(this).val();
                        });
                        window.location.href = href;                    
                    }
                }catch(e){
                    console.log(e);
                }
            }
            return false;
        });
        $('input[type="checkbox"]').change(function(){
            showHideBulkActions();
        });
        function showHideBulkActions(){
            var num = ($('input[type="checkbox"]:checked').length) - ($('input.selectAll[type="checkbox"]:checked').length);
            $('.bulkActions .numSelected').html(num);
            if(num>0){
                $('.bulkActions').show();
            } else {
                $('.bulkActions').hide();
            }
        }
        showHideBulkActions();
    }

    

    $('.bulkActions').hide();
    
    $('a[rel="popover"]').popover();

    $('[data-toggle="tooltip"]').tooltip()
    
    $('a .external').on('click',function(){
        window.open($(this).attr('href'), '_blank');
        return false;
    });
    
    $("div.alerts div.alert-success").delay(2500).fadeOut(400);

});