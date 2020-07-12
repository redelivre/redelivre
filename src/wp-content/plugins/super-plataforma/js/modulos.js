$(document).ready(function () {
			$( "#tabela" ).sortable({
				axis: 'y',
				update: function (event, ui) {
					var data = $(this).sortable( "serialize" );
					var caminho = $('#caminho').val();
					$.ajax({
						data: data,
						type: 'POST',
						url: caminho+'/wp-content/plugins/super-plataforma/ordenarModulo.php',
						success:function(data){
						}   
					});
				}
			}).disableSelection();
		});


		function excluir(modulo, curso){
			var caminho = $('#caminho').val();

			swal({
				title: 'Deletar Módulo?',
				text: 'Todas as aulas desse módulo também serão apagadas!',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Sim, Deletar!',
				cancelButtonText: 'Não',
				confirmButtonClass: "btn btn-success",
				cancelButtonClass: "btn btn-danger",
				buttonsStyling: false
			}).then(function() {

				location.href= caminho+'/modulos?del='+modulo+'&id_curso='+curso;

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