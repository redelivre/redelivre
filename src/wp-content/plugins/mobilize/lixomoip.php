<?php

/**
 * [tiracento description]
 * @param  [type] $texto [description]
 * @return [type]        [description]
 */
public static function tiracento($texto)
{
    $trocarIsso = array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ü','ú','ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ü','Ú','Ÿ',);
    $porIsso    = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','Y',);
    $titletext  = str_replace($trocarIsso, $porIsso, $texto);

    return $titletext;
}

if (is_plugin_active('Mobilize_moip/Mobilize_moip.php')) {
    Mobilize_moip::save_mobilize_moip_settings();
}

function hex_color_type($type){
    switch ($type) {
        case '1':
            return Mobilize_moip::getOption('mm_color_institucional');
            break;
        case '2':
            return Mobilize_moip::getOption('mm_color_projeto');
            break;
        case '3':
            return Mobilize_moip::getOption('mm_color_outros');
            break;
    }
}

function contribuicao_type($type) {
    switch ($type) {
        case '1':
            return ' - Institucional - ';
            break;
        case '2':
            return ' - Projeto - ';
            break;
        case '3':
            return ' - Outro - ';
            break;
        default:
            return ' - ';
            break;
    }
}

function mount_desc($type, $descricao)
{
    return 'Contribuicao'.contribuicao_type($type).Mobilize::tiracento($descricao);
}

if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') == 'true') {
    $color1 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa1')).';"';
}

if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') == 'true') {
    $color2 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa2')).';"';
}

if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') == 'true') {
    $color3 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa3')).';"';
}

?>


<!-- Lixo Template -->
<?php if(Mobilize_moip::getOption('mm_checkbox_status') == 'true'){ ?>
<section class="mobilize-widget clearfix">
        <h6>Contribuição</h6>
        <p><?php if(trim(Mobilize_moip::getOption('mm_descricao')) == '') { echo Mobilize_moip::TEXTO_DESCRITIVO_PADRAO_MOIP; } else { echo Mobilize_moip::getOption('mm_descricao'); } ?></p>
        
        <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') == 'true') { ?>
        <div class="contribution-wrapper" style="padding: 0;">
            <form target="_blank" class="form-moip1" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                <!-- input data -->
                <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa1')); ?>">
                <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa1'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa1')); ?>">
                <!-- /input data -->

                <div class="contribution">
                    <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa1'); ?></p>

                    <h3 class="price" <?php echo $color1; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa1')); ?></h3>

                    <a href="#" class="link-moip1 link-contribua">Contribuir</a>
                </div>
            </form>
        </div>
        <?php } ?>

        <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') == 'true') { ?>
        <div class="contribution-wrapper">
            <form target="_blank" class="form-moip2" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                <div class="contribution">
                    <!-- input data -->
                    <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                    <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa2')); ?>">
                    <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa2'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa2')); ?>">
                    <!-- /input data -->

                    <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa2'); ?></p>

                    <h3 class="price" <?php echo $color2; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa2')); ?></h3>

                    <a href="#" class="link-moip2 link-contribua">Contribuir</a>
                </div>
            </form>
        </div>
        <?php } ?>

        <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') == 'true') { ?>
        <div class="contribution-wrapper">
            <form target="_blank" class="form-moip3" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                <div class="contribution">
                    <!-- input data -->
                    <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                    <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa3')); ?>">
                    <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa3'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa3')); ?>">
                    <!-- /input data -->

                    <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa3'); ?></p>

                    <h3 class="price" <?php echo $color3; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa3')); ?></h3>

                    <a href="#" class="link-moip3 link-contribua">Contribuir</a>
                </div>
            </form>
        </div>
        <?php } ?>
        
        <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaolivre') == 'true') { ?>
        <div class="contribution-wrapper">
            <form target="_blank" class="form-moip4" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                <div class="contribution">
                    <!-- input data -->
                    <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                    <input class="valor-livre-output" type="hidden" name="valor">
                    <input type="hidden" name="nome" value="<?php echo mount_desc('', Mobilize_moip::getOption('mm_descricao_contribuicaolivre')); ?>">
                    <!-- /input data -->

                    <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaolivre'); ?></p>

                    <div class="price-livre">
                        <input class="valor-livre-input" type="text" placeholder="Digite seu valor" style="border: 1px solid #DDD;">
                    </div>

                    <a href="#" class="link-moip4 link-contribua">Contribuir</a>
                </div>
            </form>
        </div>
        <?php } ?>

        <div class="clear"></div>
</section>
<?php } ?>