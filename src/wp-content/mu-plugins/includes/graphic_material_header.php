<?php
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/GraphicMaterialFactory.php');

$header = GraphicMaterialFactory::build('Header');

if (isset($_POST['save']) || isset($_POST['use-header']) ) {
    check_admin_referer('graphic_material');

    try {
        $header->save();
        if (isset($_POST['use-header']))
            $header->setAsWordPressHeader();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}
?>

<div id="graphic-material-header">
    <h1>Criar cabeçalho</h1>
    <div id="graphic_material_content" style="width: 60%; float: left;">
        <h3>1. Envie uma foto:</h3>
        <a href="#graphic_material_preview">Pré-visualização</a>
        <?php $header->candidatePhoto->printHtml(); ?>

        <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanhaPreviewFlyer'>
            <input type='hidden' name='type' value='header'>
            <div id="graphic_material_wizard">
                <h3>2. Escolha uma forma:</h3>
                <a href="#graphic_material_preview">Pré-visualização</a>
                <p>
                    Cores da forma: 
                    <input type="color" name="data[shapeColor1]" value="<?php echo (isset($header->data->shapeColor1) && !empty($header->data->shapeColor1)) ? $header->data->shapeColor1 : '#ff0000'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                    <input type="color" name="data[shapeColor2]" value="<?php echo (isset($header->data->shapeColor2) && !empty($header->data->shapeColor2)) ? $header->data->shapeColor2 : '#00ff00'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                </p>
                <div id="listShapes" class="clearfix">
                    <?php
                    $shapes = Header::getShapes();

                    foreach ($shapes as $shape) {
                        $checked = (isset($header->data->shapeName) && $shape->name == $header->data->shapeName) ? ' checked ' : '';
                        $active = $checked ? "class='active'" : "";
                        echo "<div class='shapeItem'><label $active><input type='radio' name='data[shapeName]' value='{$shape->name}' $checked><img src='{$shape->url}'></label></div>";
                    }
                    ?>
                </div>




                <h3>3. Textos:</h3>
                <a href="#graphic_material_preview">Pré-visualização</a>
                <h4>Nome do cantidato:</h4>
                
                <input type="text" name="data[candidateName]" value="<?php echo (isset($header->data->candidateName)) ? $header->data->candidateName : ''; ?>" />
                Cor: <input type="color" name="data[candidateColor]" value="<?php echo (isset($header->data->candidateColor) && !empty($header->data->candidateColor)) ? $header->data->candidateColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br /><br/>

                Tamanho da fonte:<br/>
                <select name="data[candidateSize]" id="candidateSize">
                    <option value="" selected="selected">Valor padrão</option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($header->data->candidateSize) && $header->data->candidateSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>


                <h4>Slogan:</h4>
                <input type="text" name="data[slogan]" value="<?php echo (isset($header->data->slogan)) ? $header->data->slogan : ''; ?>" />
                Cor: <input type="color" name="data[sloganColor]" value="<?php echo (isset($header->data->sloganColor) && !empty($header->data->sloganColor)) ? $header->data->sloganColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br/><br/>
                Tamanho da fonte:<br/>
                <select name="data[sloganSize]">
                    <option value="" selected="selected">Valor Padrão</option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($header->data->sloganSize) && $header->data->sloganSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>

                <h4>Cor do número do candidato: </h4>
                <input type="color" name="data[numberColor]" value="<?php echo (isset($header->data->numberColor) && !empty($header->data->numberColor)) ? $header->data->numberColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                <!--
                Tamanho:
                <select name="data[numberSize]">
                    <option value="" selected="selected"></option>
                <?php
                foreach (range(140, 220) as $number) {
                    $selected = (isset($header->data->numberSize) && $header->data->numberSize == $number) ? ' selected="selected" ' : '';
                    echo "<option value='$number' $selected>$number</option>";
                }
                ?>
                </select>
                <br><br>
                -->

                <h4>Cor do cargo:</h4>
                <input type="color" name="data[roleColor]" value="<?php echo (isset($header->data->roleColor) && !empty($header->data->roleColor)) ? $header->data->roleColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br />

                <!--
                Tamanho:
                <select name="data[roleSize]">
                    <option value="" selected="selected">Valor Padrão</option>
                <?php
                foreach (range(70, 140) as $number) {
                    $selected = (isset($header->data->roleSize) && $header->data->roleSize == $number) ? ' selected="selected" ' : '';
                    echo "<option value='$number' $selected>$number</option>";
                }
                ?>
                </select>
                <br><br>
                -->


            </div>


            <div id="graphic_material_visualization">

                <div id="graphic_material_preview" >
                    <h3>Pré-visualização</h3>
                </div>

                

                <p>
                    <!--
                    <input type="submit" class="button-primary" name="save" value="Salvar">
                    <input type="button" value="Cancelar Edição" class="button-secondary" onClick="document.location = document.location.toString();" />
                    -->
                    <input type="submit" class="button-primary" name="use-header" id="use-header" value="Usar essa imagem para meu cabeçalho!">
                </p>

            </div>        
        </form>
    </div>


</div>

