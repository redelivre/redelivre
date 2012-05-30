<?php

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');

$smallFlyer = new SmallFlyer;
$url = site_url() . '/materialgrafico';

if (isset($_POST['save'])) {
    check_admin_referer('graphic_material');
    
    try {
        $smallFlyer->save();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}

?>

<div>
    <h1>Geração de material gráfico</h1>
    <div id="graphic_material_content" style="width: 60%; float: left;">
        <h3>1. Selecione uma foto ou envie uma nova:</h3>
        <?php echo '<img src="../wp-content/themes/campanha_padrao/img/delme/mahatma-gandhi.jpg"><br><br>'; ?>
        
        <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanha_preview_flyer'>
            <input type='hidden' name='page' value='graphic_material'>
            <div id="graphic_material_wizard">
                <h3>2. Escolha uma forma:</h3>
                <?php
                $shapes = SmallFlyer::getShapes();
                
                foreach ($shapes as $shape) {
                    echo "<input type='radio' name='shapeName' value='{$shape->name}'><img src='{$shape->url}'>";
                }
                ?>
                
                <p>Cor 1: <input type="color" name="shapeColor1" value="#ff0000" data-text="hidden" style="height:20px;width:20px;" /></p>
                <p>Cor 2: <input type="color" name="shapeColor2" value="#00ff00" data-text="hidden" style="height:20px;width:20px;" /></p>
                <br>
                
                <h3>3. Textos:</h3>
                <label for="candidateName">Nome:</label> <input type="text" name="candidateName" value="" /><br>
                <label for="candidateColor">Cor:</label> <input type="color" name="candidateColor" value="#000000" data-text="hidden" style="height:20px;width:20px;" /><br>
                <label for="candidateSize">Tamanho:</label>
                <select name="candidateSize">
                    <option value=""></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        echo "<option value='$number'>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                
                <label for="slogan">Slogan:</label> <input type="text" name="slogan" value="" /><br>
                <label for="sloganColor">Cor:</label> <input type="color" name="sloganColor" value="#000000" data-text="hidden" style="height:20px;width:20px;" /><br>
                <label for="sloganSize">Tamanho:</label>
                <select name="sloganSize">
                    <option value=""></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        echo "<option value='$number'>$number</option>";
                    }
                    ?>
                </select>
                
                <h3>4. Envie o link para a gráfica:</h3>
                <p>Utilize o link <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a> para compartilhar o material gráfico gerado. O checkbox abaixo precisa estar selecionado para que o conteúdo do link seja público.</p>
                <input type='checkbox' name='graphic_material_public' <?php if ($smallFlyer->isPublic()) echo ' checked="checked" '; ?>> Link público?
            </div>
            
            <input type="submit" name="save" value="Salvar">
        </form>
    </div>
    
    <div id="graphic_material_preview" style="min-height: 400px;">
        <h3>Pré-visualização</h3>
    </div>
    <div id="graphic_material_saved">
        <?php
        if ($smallFlyer->hasImage()) {
            echo '<h3>Imagem salva</h3>';
            // add random number as parameter to skip browser cache
            $rand = rand();
            echo "<img src='{$smallFlyer->getImage('png')}?rand=$rand'>";
        }
        ?>
    </div>
</div>
    
