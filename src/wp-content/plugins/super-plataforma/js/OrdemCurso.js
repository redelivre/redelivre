        $(document).ready(function () {
             $( "#tabela" ).sortable({
                axis: 'y',
                update: function (event, ui) {
                    var data = $(this).sortable( "serialize" );
                    var caminho = $('#caminho').val();
                        $.ajax({
                        data: data,
                        type: 'POST',
                        url: caminho+'/wp-content/plugins/super-plataforma/ordenarCurso.php',
                        success:function(data){
                        }   
                    });
                }
            }).disableSelection();
        });