
function autocompletar() {
	var minimo_letras = 0;
	var palabra = $('#input-search').val();
	if (palabra.length >= minimo_letras) {
		$.ajax({
			url: 'mostrar.php',
			type: 'POST',
			data: {palabra:palabra},
			success:function(data){
				$('#lista_id').show();
				$('#lista_id').html(data);
			}
		});
	} else {
		$('#lista_id').hide();
	}
}

function set_item(opciones) {
	$('#input-search').val(opciones);
	$('#lista_id').hide();
}