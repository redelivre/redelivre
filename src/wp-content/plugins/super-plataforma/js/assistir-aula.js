    function myFunction(){
        var url_dom_dir = $('#caminho').val();
        $.ajax({
            url: url_dom_dir+'/wp-content/plugins/super-plataforma/salvar-avaliacao.php',
            method: 'post',
            data: $('#frm_avaliacao').serialize(),
            success: function(data) {
            }
        });
    }

    function liberarResposta(ID){
        $("#resp"+ID).show();
    }

    function excluirComentario(caminho, ID_C, curso, aula ){
        location.href=  caminho+"/assistir-aula?curso="+curso+"&aula_id="+aula+"&del="+ID_C;
    }

    function marcarAssistido(caminho, curso, aula, modulo){
        if (modulo) { 
        location.href= caminho+"/assistir-aula?curso="+curso+"&aula_id="+aula+"&modulo="+modulo+"&a_assistida=V";
    } else {
        location.href= caminho+"/assistir-aula?curso="+curso+"&aula_id="+aula+"&a_assistida=V";
    }
    }

    function marcarNaoAssistido(caminho, curso, aula, modulo){
        location.href= caminho+"/assistir-aula?curso="+curso+"&aula_id="+aula+"&modulo="+modulo+"&a_assistida=F";
    }
