jQuery(document).ready(function() {
    // deixa selecionado por padrão a primeira entrada da taxonomia se só houver uma
    // quando o usuário está criando um novo objeto
    if (jQuery('#object_type input[type="checkbox"]').length == 1) {
        jQuery('#object_type input[type="checkbox"]').attr('checked', 'checked');
    }
});