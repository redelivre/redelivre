<?php 
    global $cwebPluginName;
    global $PluginTextDomain;
    if(isset($_POST['submit_changes'])){ 
        update_option('_email_address',$_POST['_email_address']);
        update_option('listing_layout',$_POST['listing_layout']);
        update_option('future_from_date',$_POST['from']);
        update_option('future_to_date',$_POST['to']);
        
        foreceRedirect(admin_url('admin.php?page=settings&tab=_agenda_settings'));
        set_error_message('Settings has been updated succesfully...!','0');
        exit;
    }?>
<div class="wrap">
    <?php show_error_message();?>
        <div class="form_bit_coin">
            <form method="POST">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="email_address">Email do reponsável : </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" id="email_address" name="_email_address" value="<?php echo get_option('_email_address');?>">
                             
                            </td>                
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="_catalog_news"> Configurações de layout de listagem : </label>
                            </th>
                            <td>
                                <?php $listing_layout=get_option('listing_layout');?>
                                <select style="width:25em" id="listing_layout" name="listing_layout">
                                    <option <?php echo ($listing_layout=='two_colums') ? 'selected="selected"':''; ?> value="two_colums">Two Colums</option>
                                    <option <?php echo ($listing_layout=='three_colums') ? 'selected="selected"':''; ?> value="three_colums">Three Columns</option>
                                </select>
                                <br><p class="description"> Select Columns</p>
                            </td>                
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="_catalog_news">Definir data futura : </label>
                            </th>
                            <td>
                                <div id="daterange_two" class="selectbox pull-right">
                                  <i class="fa fa-calendar"></i>
                                  <span class='date_reange_changer'><?=date('F d, Y')?> - <?=date('F d, Y')?></span> <b class="caret"></b>
                                </div>
                            </td>    
                        </tr>
                         <tr>
                            <th scope="row">Periodo selecionado : </th>
                            <td>
                             <input readonly type='text' style="width:170px;" name='from' value="<?php echo get_option('future_from_date');?>" id='from_date_picker'/>
                             <input readonly type='text' style="width:170px;" name='to' value="<?php echo get_option('future_to_date');?>" id='to_date_picker'/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Caminho do arquivo de script: </th>
                            <td>
                             <!--<input disable type="text" class="regular-text" id="_script_path" name="_script_path" value="<?php echo get_option('_script_path');?>">-->
                             <?php  echo  WP_PLUGIN_DIR.'/fortaleza-events/get-events.php'; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="_catalog_news">Shortcode</label>
                            </th>
                            <td>
                                <input readonly="" type="text" style="width:400px;" name="from" value='[highlight_events category-name="Enter Category Name"]'>
                                <br><p class="description">This shortcode is used to display events in any page.</p>
                            </td>                
                        </tr>
                    </tbody>
                </table>
                <div class="submit_btn">
                    <p class="submit">
                     <input id="filter_res" type="submit" name="submit_changes" id="submit" class="button button-primary" value="<?php _e('Save Changes');?>">
                    </p>
                </div>
            </form>
        </div>    
        <div class="clear"></div>
</div>