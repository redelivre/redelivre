<?php
$root = "../../../";
require_once $root . 'wp-config.php';
require_once $root . 'wp-admin/includes/post.php';
require_once $root . 'wp-admin/includes/file.php';

error_reporting(E_ALL && ~E_STRICT && ~E_DEPRECATED);
//pr($_SERVER);die();
// file url ( /var/www/html/api/wp-content/plugins/fortaleza-events/get-events.php)//
update_option('_script_path',$_SERVER['SCRIPT_FILENAME']);
$from_Date=get_option('future_from_date');
$to_Date=get_option('future_to_date');


if((strtotime(date('Y-m-d')) >= strtotime($from_Date)) && (strtotime(date('Y-m-d')) <= strtotime($to_Date))){
    $url="http://mapa.cultura.ce.gov.br/api/event/findByLocation/?@files=%28header.header,avatar.avatarBig%29:url&@from=".$from_Date."&@select=id,singleUrl,name,subTitle,type,shortDescription,longDescription,terms,classificacaoEtaria,traducaoLibras,descricaoSonora,owner.name,owner.singleUrl,project.name,project.singleUrl,endereco,occurrences&@to=".$to_Date."&@version=1&classificacaoEtaria=IN%2810+anos,12+anos,14+anos,16+anos,18+anos,Livre%29&space:geoMesorregiao=IN%28SUL+CEARENSE,SERT%C3%95ES+CEARENSES,NORTE+CEARENSE,NOROESTE+CEARENSE,METROPOLITANA+DE+FORTALEZA,JAGUARIBE,CENTRO-SUL+CEARENSE%29&space:geoMicrorregiao=IN%28FORTALEZA%29&space:geoMunicipio=IN%28FORTALEZA%29&term:linguagem=IN%28Artes+Circenses,Artes+Integradas,Artes+Visuais,Audiovisual,Cinema,Cultura+Digital,Cultura+Ind%C3%ADgena,Cultura+Tradicional,Curso+ou+Oficina,Dan%C3%A7a,Exposi%C3%A7%C3%A3o,Hip+Hop,Livro+e+Literatura,M%C3%BAsica+Erudita,M%C3%BAsica+Popular,Outros,Palestra,+Debate+ou+Encontro,R%C3%A1dio,Teatro%29";
}

$result=file_get_contents($url);
$obj_result = json_decode($result);
if(isset($obj_result)){
    $counter=0;
    foreach($obj_result as $key_val=>$list){
        //pr($list);
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
            $title=$list->name; 
            $language=$list->terms->linguagem[0]; 
            $classifcation=$list->classificacaoEtaria; 
            $author=$list->owner->name; 
            $content=$list->shortDescription;
            foreach($res['occurrences'] as $details){
                 if(date("Y",strtotime($from_Date))==date("Y",strtotime($details->rule->startsOn))){
                    $start_date=$details->rule->startsOn; 
                    $start_time=$details->rule->startsAt; 
                    $end_time=$details->rule->endsAt; 
                    $price=$details->rule->price; 
                    $address=$details->rule->description; 
                    $space_ID=$details->rule->spaceId;
               }    
            }
        
        // create attchment by image url
            
            $image_url        = $evnt_img; // Define the image URL here
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
            
            global $wpdb;
            
            // check post already exist or not 
            
            $return = $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title = '" . $list->name . "' && post_status = 'publish' && post_type = 'agenda' ", 'ARRAY_N' );
            $post_id = $return[0];
            
            if(empty($post_id)){
                //create post
                $post_id = wp_insert_post(array (
                    'post_type' => 'agenda',
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => 'publish',
                    'comment_status' => 'closed',   // if you prefer
                    'ping_status' => 'closed',      // if you prefer
                ));
                $counter++;
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
                update_post_meta($post_id, '_event_language', $language);
                update_post_meta($post_id, '_event_classification', $classifcation);
                update_post_meta($post_id, '_event_author', $author);
                update_post_meta($post_id, '_start_date', $start_date);
                update_post_meta($post_id, '_start_time', $start_time);
                update_post_meta($post_id, '_end_time', $end_time);
                update_post_meta($post_id, '_price', $price);
                update_post_meta($post_id, '_address', $address);
                update_post_meta($post_id, '_spaceID', $space_ID);
        
                wp_set_object_terms( $post_id, $classifcation, 'classifaction' );
                wp_set_object_terms( $post_id, $start_time, 'start_at' );
             }
         }
    }
    
    //send email after script successfully run
    $to=get_option('_email_address');
    //$to = "rajkumar@cwebconsultants.com";
    $subject = "Agenda Script Information";
    $message="Today's New Agenda Added = ".$counter;
    $headers = "From: AgendaAdmin" . "\r\n" .
    "CC: rajkumar@cwebconsultants.com";
    mail($to,$subject,$message,$headers);
?> 





