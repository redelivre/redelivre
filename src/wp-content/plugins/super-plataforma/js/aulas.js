$(document).ready(function () {
   $( "#tabela" ).sortable({
    axis: 'y',
    update: function (event, ui) {
        var data = $(this).sortable( "serialize" );
        var caminho = $('#caminho').val();
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: caminho+'/wp-content/plugins/super-plataforma/putClassInOrder.php',
                    success:function(data){
                    }   
                });
            }
        }).disableSelection();
});

function excluir(aula, modulo, curso){
    var caminho = $('#caminho').val();
    swal({
        title: 'Deletar Aula?',
        text: 'A aula será excluida permanentemente!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, Deletar!',
        cancelButtonText: 'Não',
        confirmButtonClass: "btn btn-success",
        cancelButtonClass: "btn btn-danger",
        buttonsStyling: false
    }).then(function() {

        if(modulo != ''){
            location.href=  caminho+'/aulas-modulo?id_curso='+curso+'&id_modulo='+modulo+'&del='+aula;
        }else{
            location.href=  caminho+'/aulas?id_curso='+curso+'&del='+aula;
        }
        
    }, function(dismiss) {
                // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
                if (dismiss === 'cancel') {
                    swal({
                        title: 'Cancelado',
                        text: 'Nenhum dado foi apagado :)',
                    type: 'error',
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                }).catch(swal.noop)
                }
            })
}