<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>
<?php $post_id=get_the_ID(); ?>
<div class="single-agenda-main-page">
	<div class="single-inner-page">
		<div class="thumbnail-image">
                    <?php $agenda_img=get_the_post_thumbnail_url(); ?>
                    <span><img src="<?php echo $agenda_img; ?>"/></span>
		</div>
		<div class="agenda-title">
		<h2><?php the_title(); ?></h2>
		</div>
		<div class="main-content">
			<div class="content-agenda">
                            <span class="dis-agenda">
                                <?php 
                                if ( have_posts() ) : while ( have_posts() ) : the_post();
                                    the_content();
                            endwhile;
                            else:
                                echo 'Sorry, no posts matched your criteria';
                            endif;?>
                            </span>
			</div>
			<div class="short-detals">
                            <div class="data_start_on">
                                <div class="label">DATA</div>
                                <div class="value">
                                        <?php
                                            $euroTimestamp=strtotime(get_post_meta(get_the_ID(),'_start_date',true));
                                            echo $start_date= date("d/m/Y", $euroTimestamp)
                                        ?>
                                </div>
                            </div>
                            <div class="data_startAt">
                                 <div class="label">HORÁRIO</div>
                                 <div class="value"><?php echo get_post_meta($post_id,'_start_time',true); ?></div>
                            </div>
                            <div class="agenda_price">
                                 <div class="label">ENTRADA</div>
                                 <div class="value"><?php echo get_post_meta($post_id,'_price',true); ?></div>
                            </div>
                            <div class="classification">
                                 <div class="label">CLASSIFICACÃO INDICATIVA</div>
                                 <div class="value"><?php echo get_post_meta($post_id,'_event_classification',true); ?></div>
                            </div>
                            <div class="language_agendas">
                                 <div class="label">LINGUAGEM</div>
                                 <div class="value"><?php echo get_post_meta($post_id,'_event_language',true); ?></div>
                            </div>
                            <div class="local_space">
                                 <div class="label">LOCAL</div>
                                 <?php $spaceID=get_post_meta($post_id,'_spaceID',true);
                                          $space_Name="http://mapa.cultura.ce.gov.br/api/space/findOne/?&id=EQ(".$spaceID.")&@select=id,singleUrl,name,subTitle,type,shortDescription,terms,project.name,project.singleUrl,endereco,endereco,acessibilidade&@files=(avatar.avatarSmall)";
                                        $result=file_get_contents($space_Name);
                                        $obj_results = json_decode($result);
                                        if(isset($obj_results)){
                                            $vanue_name=$obj_results->name;
                                        } ?>
                                 <div class="value"><?php echo $vanue_name; ?></div>
                            </div>
                            
                        </div>
	</div>
</div>
</div>
<?php get_footer();
