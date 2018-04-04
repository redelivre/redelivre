  // date picker
    jQuery(document).ready(function () {
        jQuery("#start_date").datepicker({
            dateFormat:'yy-mm-dd',
            maxDate: new Date(2018, 11, 31),
            minDate: new Date(2017, 0, 1),
        });
            jQuery(function($){
        $.datepicker.regional['pt-BR'] = {
                closeText: 'Fechar',
                prevText: '&#x3c;Anterior',
                nextText: 'Pr&oacute;ximo&#x3e;',
                currentText: 'Hoje',
                monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
                'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
                monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
                'Jul','Ago','Set','Out','Nov','Dez'],
                dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
                dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
                dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
                weekHeader: 'Sm',
                dateFormat: 'dd/mm/yy',
                firstDay: 0,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);
            });
    });
    
// ajax filter

jQuery(document).ready(function() {
    jQuery("#classification" ).on('change',function(){
            search_filter();
    });
    jQuery("#start_time" ).on('change',function(){
           search_filter();
    });
    jQuery("#start_date" ).on('change',function(){
           search_filter();
    });
     jQuery("#post_title" ).keyup(function(){
           search_filter();
    });
    
});

function search_filter(){
     var ajax_url= jQuery("#ajax_url").val();
     var classification=jQuery('#classification').val();
     var start_time=jQuery('#start_time').val();
     var start_date=jQuery('#start_date').val();
     var post_title=jQuery('#post_title').val();
     
                jQuery.ajax({
                    url: ajax_url, //AJAX file path - admin_url('admin-ajax.php')
                    type: "POST",
                    data: {
                        //action name
                        action:'search_agenda_list',
                            '_classification':classification,
                            '_start_time':start_time,
                            '_start_date':start_date,
                            '_post_title':post_title
                        },
                    success:function(html){
                       
                        jQuery(".inner_rows").hide();
                        jQuery("#inner_ajax_rows").html(html);
                     }
            });
        }