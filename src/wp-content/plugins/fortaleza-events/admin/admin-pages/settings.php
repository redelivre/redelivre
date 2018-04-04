<?php
/** @wordpress-plugin
 * Author:            CwebConsultants
 * Author URI:        http://www.cwebconsultants.com/
 */

    global $cwebPluginName;
    global $PluginTextDomain;
    if(isset($_POST['submit_changes'])){ 
	 $from=$_POST['from'];
	 $to=$_POST['to'];
         $space_id=$_POST['space_id'];

        //$url="http://mapa.cultura.ce.gov.br/api/event/findByLocation/?@files=%28header.header,avatar.avatarBig%29:url&@from=2017-08-03&@select=id,singleUrl,name,subTitle,type,shortDescription,longDescription,terms,classificacaoEtaria,traducaoLibras,descricaoSonora,owner.name,owner.singleUrl,project.name,project.singleUrl,endereco,occurrences&@to=2017-09-03&@version=1&classificacaoEtaria=IN%2810+anos,12+anos,14+anos,16+anos,18+anos,Livre%29&space:geoMesorregiao=IN%28SUL+CEARENSE,SERT%C3%95ES+CEARENSES,NORTE+CEARENSE,NOROESTE+CEARENSE,METROPOLITANA+DE+FORTALEZA,JAGUARIBE,CENTRO-SUL+CEARENSE%29&space:geoMicrorregiao=IN%28FORTALEZA%29&space:geoMunicipio=IN%28FORTALEZA%29&term:linguagem=IN%28Artes+Circenses,Artes+Integradas,Artes+Visuais,Audiovisual,Cinema,Cultura+Digital,Cultura+Ind%C3%ADgena,Cultura+Tradicional,Curso+ou+Oficina,Dan%C3%A7a,Exposi%C3%A7%C3%A3o,Hip+Hop,Livro+e+Literatura,M%C3%BAsica+Erudita,M%C3%BAsica+Popular,Outros,Palestra,+Debate+ou+Encontro,R%C3%A1dio,Teatro%29";
        
        $url="http://mapa.cultura.ce.gov.br/api/event/findBySpace/?&@from=".$from."&@to=".$to."&@select=id,singleUrl,name,subTitle,type,shortDescription,longDescription,owner.name,terms,project.name,project.singleUrl,occurrences,endereco,classificacaoEtaria&@order=name%20ASC&spaceId=".$space_id."&@files=(avatar.avatarBig):url";
        
        $result=file_get_contents($url);
        $obj_result = json_decode($result);
    }
    
    if(isset($_POST['save_all'])):
        foreach($_POST['entries'] as $all_post){
            $event_image=$all_post['event_image'];
            $event_name=$all_post['event_name'];
            $event_language=$all_post['event_language'];
            $age_rating=$all_post['age_rating'];
            $event_author=$all_post['event_author'];
            $event_content=$all_post['event_content'];
            $start_date=$all_post['start_date'];
            $start_time=$all_post['start_time'];
            $end_time=$all_post['end_time'];
            $price=$all_post['price'];
            $address=$all_post['address'];
            $space_Id=$all_post['spaceID'];
            
            // create attchment by image url
            
            $image_url        = $all_post['event_image']; // Define the image URL here
            $image_name       = 'event-image.jpg';
            $upload_dir       = wp_upload_dir(); // Set upload folder
            $image_data       = file_get_contents($image_url); // Get image data
            $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
            $filename         = basename( $unique_file_name ); // Create image file name 

            // Check folder permission and define file location

            if(wp_mkdir_p( $upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }
            // Create the image  file on the server
            file_put_contents( $file, $image_data );

            // Check image file type
            $wp_filetype = wp_check_filetype( $filename, null );

            // Set attachment data

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name( $filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
 
                //check post already exis or not? and then insert agenda post 
                global $wpdb;
                $return = $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title = '" .$event_name. "' && post_status = 'publish' && post_type = 'agenda' ", 'ARRAY_N' );
                $post_id = $return[0];
            if(empty($post_id)){
                $post_id = wp_insert_post(array (
                    'post_type' => 'agenda',
                    'post_title' => $event_name,
                    'post_content' => $event_content,
                    'post_status' => 'publish',
                    'comment_status' => 'closed',   // if you prefer
                    'ping_status' => 'closed',      // if you prefer
                ));
            }
            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            // Include image.php
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Define attachment metadata
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

            // Assign metadata to attachment
            wp_update_attachment_metadata($attach_id,$attach_data);
            set_post_thumbnail( $post_id, $attach_id );
            
            // save fileds in meta
            
            if(!empty($post_id)){
                add_post_meta($post_id, '_event_language', $event_language);
                add_post_meta($post_id, '_event_classification', $age_rating);
                add_post_meta($post_id, '_event_author', $event_author);
                add_post_meta($post_id, '_start_date', $start_date);
                add_post_meta($post_id, '_start_time', $start_time);
                add_post_meta($post_id, '_end_time', $end_time);
                add_post_meta($post_id, '_price', $price);
                add_post_meta($post_id, '_address', $address);
                add_post_meta($post_id, '_spaceID', $space_Id);
        
                wp_set_object_terms( $post_id, $age_rating, 'classifaction' );
                wp_set_object_terms( $post_id, $start_time, 'start_at' );
             }
         }
         
        set_error_message('All Events Successfully Added...!','0');
        foreceRedirect(admin_url('admin.php?page=settings') );
        die();
    endif;
    
        $gen_tab = $about_co_tab = '';
         if(isset($_REQUEST['tab'])){
            if($_REQUEST['tab']=='_about_co'){
                $about_co_tab = 'nav-tab-active';
            } elseif($_REQUEST['tab']=='_agenda_settings') {
            
               $_agenda_settings_tab = 'nav-tab-active';    
            }
            else{
                $gen_tab = 'nav-tab-active';
            }
        }else{
            $gen_tab = 'nav-tab-active';
        }
?>
<style>
.daterangepicker{
display:none;
}
.daterangepicker.dropdown-menu.opensleft.show-calendar{
    right:338px !important;
}
</style>
<div class="wrap">
        <h2 class="nav-tab-wrapper">
            <a href="<?=admin_url('admin.php?page=settings');?>" title="Gerenciamento" class="nav-tab <?php echo $gen_tab;?>"><?php _e('Gerenciamento');?></a>
            <a href="<?=admin_url('admin.php?page=settings&tab=_agenda_settings');?>" title="Settings" class="nav-tab <?php echo $_agenda_settings_tab;?>"><?php _e('Configuração');?></a>
            <a href="<?=admin_url('admin.php?page=settings&tab=_about_co');?>" title="About" class="nav-tab <?php echo $about_co_tab;?>"><?php _e('Sobre');?></a>
        </h2>
<?php show_error_message();
    if(isset($_REQUEST['tab'])){
        if($_REQUEST['tab']=='_about_co'){
            ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Developed By:');?></label>
                            </th>
                            <td><a href="http://cwebconsultants.com/">cWebCo India</a></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Support Email Address:');?></label>
                            </th>
                            <td><a href="mailto:info@cwebconsultants.com">info@cwebconsultants.com</a>
                            </td>
                        </tr>  
                    </tbody>
                </table>
        <?php    
        }elseif($_REQUEST['tab']=='_agenda_settings'){
            
            include plugin_dir_path( dirname( __FILE__ ) ) . 'admin-pages/agenda-setting.php';
         }
    }
    else{ ?>
         <div class="form_get_event">
            <form method="POST">
                <div class="space_id">
                    <input style="width:100%" type="text" name="space_id" placeholder="<?php _e('ID Espaço');?>" required>
                </div> 
                <div id="daterange" class="selectbox pull-right">
                    <i class="fa fa-calendar"></i>
                    <span class='date_reange_changer'><?=date('F d, Y')?> - <?=date('F d, Y')?></span> <b class="caret"></b>
                    <input type='hidden' name='from' value="<?=(!empty($from)) ? $from : date('Y-m-d')?>" id='from_date_picker'/>
                    <input type='hidden' name='to' value="<?=(!empty($to)) ? $to : date('Y-m-d')?>" id='to_date_picker'/>
                </div>
                <div class="submit_btn">
                    <p class="submit">
                     <input id="filter_res" type="submit" name="submit_changes" id="submit" class="button button-primary" value="<?php _e('Filtrar Eventos');?>">
                     <input type="hidden" id="ajax_url" value="<?php echo admin_url( 'admin-ajax.php' ); ?>">
                     <input type="hidden" value="<?php echo admin_url('admin.php?page=settings');?>" id="redirect_link">
                    </p>
                </div>
            </form>
        </div>    
        <div class="event_all_data">
             <div class="inner_event_all_data">
                <?php if(isset($obj_result)){ ?>
                 
                 <form method='post'>
                    <table class="responstable">
                        <div class="ajax_loader_img" style=""><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) ;?>admin/images/preload.gif"></div>
                        <tr>
                            <th>Agenda</th>
                            <th>Título</th>
                            <th>Horãrio,Data e local</th>
                            <th>Linguagem</th>
                            <th>Censura</th>
                            <th>Publicado por</th>
                            <th>Ação</th>
                        </tr> 
                        <?php 
                        foreach($obj_result as $key_val=>$list){
                            $res=(array) $list;
                            $evnt_small_img=$res['@files:header.header']->url;
                            $evnt_big_img=$res['@files:avatar.avatarBig']->url;
                            if(!empty($evnt_small_img)){
                                $evnt_img=$res['@files:header.header']->url;
                            }elseif($evnt_big_img){
                                $evnt_img=$res['@files:avatar.avatarBig']->url;
                            }else{
                            $evnt_img=''.site_url().'/wp-content/plugins/fortaleza-events/public/assets/img/dummy.jpg';
                            }		
                         ?>
                        <tr>
                            <td><img width="70" height="50" src="<?php echo $evnt_img; ?>"></td>
                            <td style="text-align: center !important"><?php echo $list->name; ?></td>
                            <td style="text-align:left !important">
                            <?php foreach($res['occurrences'] as $details){
                                if(date("Y",strtotime($from))==date("Y",strtotime($details->rule->startsOn))){
                                echo '<span class="date"><strong>Date : </strong></span>'.$details->rule->startsOn;echo '</br>';
                                echo '<span class="date"><strong>Start Time : </strong></span>'.$details->rule->startsAt;echo '</br>';
                                echo '<span class="date"><strong>End Time : </strong></span>'.$details->rule->endsAt;echo '</br>';
                                echo '<span class="date"><strong>Price : </strong></span>'.$details->rule->price;echo '</br>';
                                echo '<span class="date"><strong>Venue ID : </strong></span>'.$details->rule->spaceId;echo '</br>';
                                }
                               } ?></td>
                            <td><?php echo $list->terms->linguagem[0]; ?></td>
                            <td><?php echo $list->classificacaoEtaria; ?></td>
                            <td><?php echo $list->owner->name; ?></td>
                            <td>
                            <?php
                                global $wpdb;
                                $return = $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title = '" . $list->name . "' && post_status = 'publish' && post_type = 'agenda' ", 'ARRAY_N' );
                                $post_id = $return[0];
                                 if(!empty($post_id)){?>
                                <button id="delete_event" rel1="<?php echo $post_id; ?>"><?php _e('Eliminar evento');?></button>
                                <?php }else{ ?>
               <button id="get_event" rel1="<?php echo $evnt_img; ?>" rel2="<?php echo $list->name; ?>" rel3="<?php echo $list->terms->linguagem[0]; ?>" rel4="<?php echo $list->classificacaoEtaria; ?>" rel5="<?php echo $list->owner->name; ?>"rel6="<?php echo $list->shortDescription; ?>" date="<?php echo $details->rule->startsOn; ?>" start_time="<?php echo $details->rule->startsAt ?>" end_time="<?php echo $details->rule->endsAt; ?>" price="<?php echo $details->rule->price; ?>" spaceID="<?php echo $details->rule->spaceId;?>"address="<?php echo $details->rule->description;?>"><?php _e('Añadir evento');?></button>                 
    <input type='hidden' name='entries[<?php echo $key_val?>][event_image]' value="<?php echo $evnt_img; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][event_name]' value="<?php echo $list->name; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][event_language]' value="<?php echo $list->terms->linguagem[0]; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][age_rating]' value="<?php echo $list->classificacaoEtaria; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][event_author]' value="<?php echo $list->owner->name; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][event_content]' value="<?php echo $list->shortDescription; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][start_date]' value="<?php echo $details->rule->startsOn; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][start_time]' value="<?php echo $details->rule->startsAt; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][end_time]' value="<?php echo $details->rule->endsAt; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][price]' value="<?php echo $details->rule->price; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][address]' value="<?php echo $details->rule->description; ?>"/>
    <input type='hidden' name='entries[<?php echo $key_val?>][spaceID]' value="<?php echo $details->rule->spaceId; ?>"/>
            <?php } ?>
                            </td>
                        </tr>    
                        <?php } ?>
                    </table>
                     <div class="save_all_btn">
                        <button type='submit' name="save_all"><?php _e('Salvar todos');?></button> 
                     </div>
                 </form> 
                <?php } ?>
             </div>     
        </div>    
        <div class="clear"></div>
</div>
    <?php } ?>