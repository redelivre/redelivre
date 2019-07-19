<?php

include_once('Audima_LifeCycle.php');

class Audima_Plugin extends Audima_LifeCycle
{

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     *
     * @return array of option meta data.
     */

    private $audimaVoices = array(
        ['nome' => 'Ricardo','id' => '00000000000000000000000000000001','lang'=>'pt-BR'],
        ['nome' => 'Vitoria','id' => '00000000000000000000000000000002','lang'=>'pt-BR'],
        ['nome' => 'Joanna','id' => '6C0563863C9211E7848F0ED9069C384E','lang'=>'en-US'],
        ['nome' => 'Ivy','id' => 'A56CE34094E70EBA02C755403E753AFE','lang'=>'en-US'],
        ['nome' => 'Justin','id' => 'B368E4B416CA4BA6AE17044789C86558','lang'=>'en-US'],
        ['nome' => 'Matthew','id' => '7DEF464737FF4EE0693335C6C24C4C3E','lang'=>'en-US'],
        ['nome' => 'Salli','id' => '6C0564F03C9211E7848F0ED9069C384E','lang'=>'en-US'],
        ['nome' => 'Kimberly','id' => '6C05655D3C9211E7848F0ED9069C384E','lang'=>'en-US'],
        ['nome' => 'Kendra','id' => '6C0565A93C9211E7848F0ED9069C384E','lang'=>'en-US'],
        ['nome' => 'Joey','id' => '6C0565F73C9211E7848F0ED9069C384E','lang'=>'en-US'],
        ['nome' => 'Conchita','id' => '6C0566403C9211E7848F0ED9069C384E','lang'=>'es-ES'],
        ['nome' => 'Enrique','id' => '6C0566863C9211E7848F0ED9069C384E','lang'=>'es-ES'],
        ['nome' => 'Inês','id' => '6C0566CB3C9211E7848F0ED9069C384E','lang'=>'pt-PT'],
        ['nome' => 'Cristiano','id' => '6C05670F3C9211E7848F0ED9069C384E','lang'=>'pt-PT'],
        ['nome' => 'Mads','id' => '92683A6F85AB7C19605781061BFC803A','lang'=>'da-DK'],
        ['nome' => 'Naja','id' => 'A045C7F2EC96B698B81867194F30E54F','lang'=>'da-DK'],
        ['nome' => 'Lotte','id' => '9F8CEBC4CB8DB86E37EBAE484F495472','lang'=>'nl-NL'],
        ['nome' => 'Ruben','id' => '3132E6F83A87B5CFFDF284ABBA5CC0C9','lang'=>'nl-NL'],
        ['nome' => 'Nicole','id' => 'B0EDCE61337B6E7EB6F54D16820BBA40','lang'=>'en-AU'],
        ['nome' => 'Russell','id' => '43C67688FB6DD92A04C4C6A07452329A','lang'=>'en-AU'],
        ['nome' => 'Amy','id' => '05B61FE1B6EAE486DE73160DF5F8E0EC','lang'=>'en-GB'],
        ['nome' => 'Brian','id' => '63AF3F04C307CC1F1421DB626FB86E1C','lang'=>'en-GB'],
        ['nome' => 'Emma','id' => 'AD80643781DF7E228B7EFC3EE3E08EE3','lang'=>'en-GB'],
        ['nome' => 'Aditi','id' => '276CDAFDD8C6A530CBD34FF3745BE8BC','lang'=>'en-IN'],
        ['nome' => 'Raveena','id' => '0D878ED8FD9FD8AAA3C469D056B9DE83','lang'=>'en-IN'],
        ['nome' => 'Céline','id' => 'B864C18C3236AAA22B385F9C2EA89193','lang'=>'fr-FR'],
        ['nome' => 'Mathieu','id' => '5E3CD90E3F2DFAFD92828EA6B9844D45','lang'=>'fr-FR'],
        ['nome' => 'Chantal','id' => 'A60C83DEE75D5D22577B3C5C942DAA87','lang'=>'fr-CA'],
        ['nome' => 'Hans','id' => '5630A757186E4A57CDF2AD72B502DFF1','lang'=>'de-DE'],
        ['nome' => 'Marlene','id' => 'EF1F6BCC8C5F44253FF947777A57F0A7','lang'=>'de-DE'],
        ['nome' => 'Vicki','id' => '1F5EB5FBE7EB046605ECC3787F8E8202','lang'=>'de-DE'],
        ['nome' => 'Dóra','id' => '342F9849DDECCD7290161E3C392D5B55','lang'=>'is-IS'],
        ['nome' => 'Karl','id' => '24EA4708502FAC0CDB0F0AFF211093CA','lang'=>'is-IS'],
        ['nome' => 'Carla','id' => '0C9E8A9E81EDFA24D695E5B19A12A433','lang'=>'it-IT'],
        ['nome' => 'Giorgio','id' => 'DC79AC4AE80A9EA790C4116E469DB654','lang'=>'it-IT'],
        ['nome' => 'Mizuki','id' => '05BFA645B8C2F6471651A0B6E9BB0D6B','lang'=>'ja-JP'],
        ['nome' => 'Takumi','id' => '2469018E33498965CF329AB5A512E092','lang'=>'ja-JP'],
        ['nome' => 'Seoyeon','id' => 'F60D6EB3062F2E7EF553EDF3D32A568E','lang'=>'ko-KR'],
        ['nome' => 'Liv','id' => 'DBF80B904B92176543146DFDDB929890','lang'=>'nb-NO'],
        ['nome' => 'Jacek','id' => 'FE2B7F2F982DB8B359EF80D1711465B9','lang'=>'pl-PL'],
        ['nome' => 'Jan','id' => '23A154625DC34171D187B3F5CD60EBD3','lang'=>'pl-PL'],
        ['nome' => 'Ewa','id' => '2ADBB46C4ACC1AA9D772282C6C65D9AA','lang'=>'pl-PL'],
        ['nome' => 'Maja','id' => 'B9D796BD1E99F510B563815F763E2D3A','lang'=>'pl-PL'],
        ['nome' => 'Carmen','id' => '92C4691FA2EFD46D9C5B4B3C24914F2D','lang'=>'ro-RO'],
        ['nome' => 'Maxim','id' => '31FA4FCB1774ABD12F45875CE9C3EF63','lang'=>'ru-RU'],
        ['nome' => 'Tatyana','id' => 'C677F0A1912ECA2F1475ED1EDCA8D90F','lang'=>'ru-RU'],
        ['nome' => 'Miguel','id' => '7971397BC3B962E3B278EECCA62166B7','lang'=>'es-US'],
        ['nome' => 'Penélope','id' => '61E706A2154C15A0A6EA76CEA962134C','lang'=>'es-US'],
        ['nome' => 'Astrid','id' => '48139C622BE50A16E3B7C5BDCB8EB921','lang'=>'sv-SE'],
        ['nome' => 'Filiz','id' => '79086733E678B18EED7EECF50A2ED1A1','lang'=>'tr-TR'],
        ['nome' => 'Gwyneth','id' => '50B22AD1105CA424E9213FE77D9B08CE','lang'=>'cy-GB']
    );

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     *
     * @return array of option meta data.
     */
    private function getAudimaVoicesByLanguage(){
        $lang = get_bloginfo('language');
        $selectVoice = array(__('Select the TTS Voice', 'audima-plugin'),'');
        foreach($this->audimaVoices as $voice){
            $key = array_search($lang, $voice);
            if($key !=''){
                array_push($selectVoice,$voice['nome']);
            }
        }
        if(sizeof($selectVoice)==2){
            array_push($selectVoice,'Sorry, your site language is not supported yet.','Desculpe, o idioma do seu site ainda não é suportado.');
        }
        return $selectVoice;
    }

    /**
     * @return array
     */
    public function getOptionMetaData()
    {
        //  http://plugin.michael-simpson.com/?page_id=31
        $voiceOptions = $this->getAudimaVoicesByLanguage();
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'IdBlog'     => array(__('Blog ID at Audima', 'audima-plugin')),
            'TtsVoice' => $voiceOptions,
            'AutoGenerateTts' => array(
                __('Auto Generate Tts from each post', 'audima-plugin'),
                'true',
                'false'
            ),
            'AdClassResponsive' => array(__('Define the CSS Ad Class/Id', 'audima-plugin')),
            'BehaviorDesktop' => array(
                __('Player Behavior on Desktop', 'audima-plugin'),
                'FIXED',
                'player'
            ),
            'BehaviorMobile' => array(
                __('Player Behavior on Responsive/Mobile', 'audima-plugin'),
                'FIXED',
                'player'
            ),
            'EnableAudimaOnHomepage' => array(
                __('Enable Audima on client homepage', 'audima-plugin'),
                'true',
                'false'
            ),
            'CustomStyle'     => array(__('Define a custom CSS Style to be applied to AudimaWidget', 'audima-plugin')),
            'PlayerSkin' => array(
                __('Chose skin to Audima player. ', 'audima'),

                'default'

            ),
            'CategoryRestriction' => array_merge(
                array(__('Choose the categories that will display the Audima player. ', 'audima')),
                get_categories(array("fields" => "names", 'hide_empty' => FALSE)),
                get_terms(array ( 'taxonomy' => 'category', "fields" => "names", 'orderby' => 'name', 'hide_empty' => false)),
                array(array('multiple'))
            ),
            'AllTypes' => array(
                __('Enable Player in all pages. ', 'audima'),
                'false',
                'true',
            ),
            'Agree' => array(
                __('To use the Audima plugin you must agree to our terms of use. ', 'audima'),
                'false',
                'true',
            ),
            'clearCache' => array(
                __('Clear Audima Cache', 'audima'),
                'false',
                'true',
            ),
            'DropOnUninstall' => array(
                __('Drop this plugin\'s Database table on uninstall', 'audima-plugin'),
                'true',
                'false'
            ),
            'Plans'     => array(
                'Plan' => array( __('Blog Plan Audima', 'audima-plugin')),
                'IdBlog' => array(__('Blog ID at Audima', 'audima-plugin'))),

        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions()
    {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr) > 1) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName()
    {
        return 'Audima';
    }

    protected function getMainPluginFileName()
    {
        return 'audima.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     *
     * @return void
     */
    protected function installDatabaseTables()
    {
        global $wpdb;
        $tableName = $this->prefixTableName('audio');
        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `$tableName` (
                `idpost` INTEGER NOT NULL  PRIMARY KEY,
                `postid` varchar(40),
                `overwrite` enum('yes','no') NOT NULL default 'no'
            )"
        );
    }

    public function install() {
        parent::install();
    }

    protected function otherInstall()
    {
        $url = get_bloginfo('wpurl');

        $bodyResult = $this->callAudimaRest(
            '/blog',
            array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'email' => get_bloginfo('admin_email'),
                'language' => get_bloginfo('language'),
                'platformid' => '00000000000000000000000000000001',
                'narratorid' => '00000000000000000000000000000001',
                'action' => 'save',
                'url' => $url,
                'typecreate' => 'plugin',
                'enableHomepage' => $this->getOption('EnableAudimaOnHomepage', 'true'),
                'behaviorDesktop' => $this->getOption('BehaviorDesktop', 'FIXED'),
                'behaviorMobile' => $this->getOption('BehaviorMobile', 'FIXED'),
            )
        );



        if ($bodyResult === false) {
            return;
        }

        $this->updateOption('IdBlog', $bodyResult['blogid']);
        $this->updateOption('Plan', $bodyResult['plan']);
    }





    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     *
     * @return void
     */
    protected function unInstallDatabaseTables()
    {
        global $wpdb;

        $this->notifyStatusPlugin('uninstalled');

        $tableName = $this->prefixTableName('audio');
        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     *
     * @return void
     */
    public function upgrade()
    {
        $url = get_bloginfo('wpurl');

        $bodyResult = $this->callAudimaRest(
            '/putblogparameters',
            array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'email' => get_bloginfo('admin_email'),
                'language' => get_bloginfo('language'),
                'platformid' => '00000000000000000000000000000001',
                'narratorid' => $this->getVoice(),
                'action' => 'update',
                'url' => $url,
                'typecreate' => 'plugin',
                'enableHomepage' => $this->getOption('EnableAudimaOnHomepage', 'true'),
                'behaviorDesktop' => $this->getOption('BehaviorDesktop', 'FIXED'),
                'behaviorMobile' => $this->getOption('BehaviorMobile', 'FIXED'),
            )
        );

        if ($bodyResult === false) {
            return;
        }

        $this->updateOption('IdBlog', $bodyResult['blogid']);
        $this->updateOption('Plan', $bodyResult['plan']);
    }

    public function activate()
    {
        $this->notifyStatusPlugin('enabled');
        parent::activate();
    }

    public function deactivate()
    {
        $this->notifyStatusPlugin('disabled');
        parent::deactivate();
    }

    public function addActionsAndFilters()
    {


        if ($this->getOption('Agree', 'false') != 'true'){

            add_action('admin_post_agree-audima-plugin', array(&$this, 'on_save_agree'));
            add_action('admin_post_nopriv_agree-audima-plugin', array(&$this, 'on_save_agree'));

            add_action('admin_notices', array(&$this, 'adminNotice'));
            return false;
        }

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47


        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        add_action('the_content', array(&$this, 'insertAudimaPlugin'));

        add_action('post_updated', array(&$this, 'updatePost'));

        add_filter('query_vars', array(&$this, 'addQueryVars'));

        add_action( 'admin_enqueue_scripts', array(&$this, 'myEnqueue' ));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41
    }

    protected function getPostInfo($idPost)
    {
        global $wpdb;

        $tableName = $this->prefixTableName('audio');
        return $wpdb->get_results(
            $wpdb->prepare(
                "select * from `$tableName` where idpost = %d ",
                $idPost
            ),
            OBJECT_K
        );
    }

    protected function updatePostInfo($idPost, $overwrite = null, $guid = null)
    {
        global $wpdb;
        $tableName = $this->prefixTableName('audio');



        if ($oldPostInfo = current($this->getPostInfo($idPost))){

            $overwrite = (is_null($overwrite))? $oldPostInfo->overwrite : $overwrite;
            $guid = (is_null($guid))? $oldPostInfo->postid : $guid;

            $prepared = $wpdb->prepare(
                "update `$tableName` set overwrite=%s, postid=%s where  idpost=%d",
                $overwrite,
                $guid,
                $idPost
            );

        }else{
            $prepared = $wpdb->prepare(
                "insert into `$tableName` (overwrite, idpost, postid) values (%s, %d, %s)",
                $overwrite,
                $idPost,
                $guid
            );

        }

        return $wpdb->query($prepared);
    }

    protected function callAudimaRest($url, $data)
    {
        $url = 'https://audio.audima.co/rest/audiowidget' . $url;
        $postData = json_encode($data);
        $result = wp_remote_post($url, array('body' => $postData));

        if (!is_array($result)) {
            return false;
        }

        if (isset($result['response']['code']) && $result['response']['code'] == "200") {
            $bodyResult = isset($result['body']) ? $result['body'] : "";
            return json_decode($bodyResult, true);
        }

        return false;
    }

    public function insertAudimaPlugin($content)
    {
        global $wpdb;
        $post = get_post();
        $voice = $this->getVoice();
        $idBlog = $this->getOption('IdBlog');
        $enabledOnHomepage = $this->getOption('EnableAudimaOnHomepage', 'false');
        $allTypes = $this->getOption('AllTypes', 'false');

        if ($post->post_status == 'publish'
            && $this->getOption('Agree', 'false') == 'true'
            && !is_front_page()
            && $voice !== false
            && !empty($idBlog)
        ) {
            if($allTypes == 'false'){
                if(is_singular('post') || is_singular('page')){
                    $categories = wp_get_post_categories($post->ID, array("fields" => "names"));
                    $categoryRestriction = json_decode($this->getOption('categoryRestriction'));

                    if (is_array($categoryRestriction)
                        && is_array($categories)
                        && count(array_intersect($categories, $categoryRestriction)) == 0
                    ){

                        return $content;
                    }
                }
                else{
                    return $content;
                }
            }

            $debug = "console.log('Aud01');";
            // Var Settings
            $idPost = $post->ID;
            $postTitle = $post->post_title;
            $authorName = get_the_author_meta('display_name');
            $permalink = get_permalink(get_post($idPost));
            $date = $post->post_date;
            $tags = wp_get_post_tags($post->ID, array("fields" => "names"));
            $behaviorMobile = $this->getOption('BehaviorMobile', 'FIXED');
            $behaviorDesktop = $this->getOption('BehaviorDesktop', 'FIXED');
            $enableAudimaOnHomepage = $this->getOption('EnableAudimaOnHomepage', 'true');
            $adClassResponsive = $this->getOption('AdClassResponsive', '');
            $customStyle = $this->getOption('CustomStyle', '');
            $playerSkin = $this->getOption('PlayerSkin', 'default');
            $snippet = str_replace(
                '__BEHAVIOR_MOBILE__',
                "AudimaWidget.$behaviorMobile",
                file_get_contents(dirname(__FILE__) . '/audima.html')

            );
            $snippet = str_replace(
                '__BEHAVIOR_DESKTOP__',
                "AudimaWidget.$behaviorDesktop",
                $snippet
            );
            $snippet = str_replace(
                '__ENABLE_AUDIMA_ON_HOMEPAGE__',
                $enableAudimaOnHomepage,
                $snippet
            );
            $snippet = str_replace(
                '__AD_CLASS_RESPONSIVE__',
                "'$adClassResponsive'",
                $snippet
            );
            $snippet = str_replace(
                '__WIDGET_SKIN__',
                "'$playerSkin'",
                $snippet
            );
            $snippet = str_replace(
                '__CUSTOM_STYLE__',
                "'$customStyle'",
                $snippet
            );



            // Post
            $postTts = current($this->getPostInfo($idPost));
            $audimaaction = get_query_var( 'audimaaction');

            if ((!$postTts
                    || $audimaaction == 'wp_checkaudio'
                    || $postTts->overwrite == 'yes'
                ) &&
                $idBlog
            ) {
                $debug .= "console.log('Aud02');";
                if ($this->getOption('AutoGenerateTts', 'true') == 'true') {
                    $debug .= "console.log('Aud03');";

                    $parametros = array(
                        'voice'      => $voice,
                        'permalink'  => $permalink,
                        'content'    => $content,
                        'title'      => $postTitle,
                        'authorName' => $authorName,
                        'createdate' => $date,
                        'categories' => $categories,
                        'tags'       => $tags,
                        // 'encoded'    => false,
                    );

                    if ($postTts
                        && $postTts->overwrite == 'yes'
                        && $postTts->postid
                    ) {
                        $parametros['postid'] = $postTts->postid;
                        $parametros['overwrite'] = 'true';
                    }

                    $bodyResult = $this->callAudimaRest(
                        "/post/$idBlog",
                        $parametros
                    );

                    if ($audimaaction == 'wp_checkaudio') {
                        $debug .= "console.log({" .
                            "p:'" . base64_encode(serialize($parametros)) . "', " .
                            "r:'" . base64_encode(serialize($bodyResult)) . "' " .
                            "});";
                    }

                    $guid = isset($bodyResult['post'])? $bodyResult['post'] : null;

                    $this->updatePostInfo($idPost, 'no', $guid);
                }
            }



            // Change Content
            $content = $snippet . $content . "<script>$debug</script>";
        }
        if ($enabledOnHomepage == 'true'
            && is_front_page()
            && $post->post_status == 'publish'
            && $this->getOption('Agree', 'false') == 'true'
            && $voice !== false
            && !empty($idBlog)
        ) {
            $debug = "console.log('Aud01');";
            // Var Settings
            $idPost = $post->ID;
            $postTitle = $post->post_title;
            $authorName = get_the_author_meta('display_name');
            $permalink = get_permalink(get_post($idPost));
            $date = $post->post_date;
            $tags = wp_get_post_tags($post->ID, array("fields" => "names"));
            $behaviorMobile = $this->getOption('BehaviorMobile', 'FIXED');
            $behaviorDesktop = $this->getOption('BehaviorDesktop', 'FIXED');
            $enableAudimaOnHomepage = $this->getOption('EnableAudimaOnHomepage', 'true');
            $adClassResponsive = $this->getOption('AdClassResponsive', '');
            $customStyle = $this->getOption('CustomStyle', '');
            $playerSkin = $this->getOption('PlayerSkin', 'default');
            $snippet = str_replace(
                '__BEHAVIOR_MOBILE__',
                "AudimaWidget.$behaviorMobile",
                file_get_contents(dirname(__FILE__) . '/audima.html')
            );
            $snippet = str_replace(
                '__BEHAVIOR_DESKTOP__',
                "AudimaWidget.$behaviorDesktop",
                $snippet
            );
            $snippet = str_replace(
                '__ENABLE_AUDIMA_ON_HOMEPAGE__',
                $enableAudimaOnHomepage,
                $snippet
            );
            $snippet = str_replace(
                '__AD_CLASS_RESPONSIVE__',
                "'$adClassResponsive'",
                $snippet
            );
            $snippet = str_replace(
                '__WIDGET_SKIN__',
                "'$playerSkin'",
                $snippet
            );
            $snippet = str_replace(
                '__CUSTOM_STYLE__',
                "'$customStyle'",
                $snippet
            );
            // Post
            $postTts = current($this->getPostInfo($idPost));
            $audimaaction = get_query_var( 'audimaaction');

            if ((!$postTts
                    || $audimaaction == 'wp_checkaudio'
                    || $postTts->overwrite == 'yes'
                ) &&
                $idBlog
            ) {
                $debug .= "console.log('Aud02');";
                if ($this->getOption('AutoGenerateTts', 'true') == 'true') {
                    $debug .= "console.log('Aud03');";

                    $parametros = array(
                        'voice'      => $voice,
                        'permalink'  => $permalink,
                        'content'    => $content,
                        'title'      => $postTitle,
                        'authorName' => $authorName,
                        'createdate' => $date,
                        'tags'       => $tags,
                        // 'encoded'    => false,
                    );

                    if ($postTts
                        && $postTts->overwrite == 'yes'
                        && $postTts->postid
                    ) {
                        $parametros['postid'] = $postTts->postid;
                        $parametros['overwrite'] = 'true';
                    }

                    $bodyResult = $this->callAudimaRest(
                        "/post/$idBlog",
                        $parametros
                    );

                    if ($audimaaction == 'wp_checkaudio') {
                        $debug .= "console.log({" .
                            "p:'" . base64_encode(serialize($parametros)) . "', " .
                            "r:'" . base64_encode(serialize($bodyResult)) . "' " .
                            "});";
                    }

                    $guid = isset($bodyResult['post'])? $bodyResult['post'] : null;

                    $this->updatePostInfo($idPost, 'no', $guid);
                }
            }


            // Change Content
            $content = $snippet . $content . "<script>$debug</script>";

        } else if (is_singular('post'))
        {
            $debug = "";
            if ($post->post_status != 'publish') {
                $debug .= "console.log('AUDIMA: Post is $post->post_status');";
            }
            if ($this->getOption('Agree', 'false') != 'true') {
                $debug .= "console.log('AUDIMA: Terms');";
            }
            if ($voice === false) {
                $debug .= "console.log('AUDIMA: Voice is not defined');";
            }
            if (empty($idBlog)) {
                $debug .= "console.log('AUDIMA: BlogId is not defined');";
            }

            if (!empty($debug)) {
                $content = $content . "<script>$debug</script>";
            }
        }
        return $content;
    }

    protected function addMark($text, $parts)
    {
        $length = strlen($text);
        $partLength = intval($length / $parts) + 1;

        $tagOpened = false;
        $countChar = 0;
        $result = "";
        $partNumber = 1;
        for ($i=0; $i<$length; $i++) {
            $chr = $text[$i];
            $result .= $chr;
            $countChar++;
            if ($chr == '<' || $chr == '[') {
                $tagOpened = true;
                continue;
            }
            if ($chr == '>' || $chr == ']') {
                $tagOpened = false;
                continue;
            }

            if ($tagOpened) {
                continue;
            }

            $isChar = ($chr >= 'a' && $chr <= 'z')
                || ($chr >= 'A' && $chr <= 'Z')
                || ($chr >= '0' && $chr <= '9')
                || (ord($chr) > 127);

            if ($countChar >= $partLength && !$isChar) {
                $result .= "<a name=\"audmark$partNumber\"></a>\n";
                $countChar = 0;
                $partNumber++;
            }
        }

        $result .= "<a name=\"audmark$partNumber\"></a>\n";

        return $result;
    }

    protected function getVoice()
    {
        $voice = $this->getOption('TtsVoice', '');

        if (empty($voice)) {
            return false;
        }

        $array = array();
        foreach($this->audimaVoices as $v){
            $array[$v['nome']] = $v['id'];
        }

        $voiceOptions = $array;

        if (isset($voiceOptions[$voice])) {
            return $voiceOptions[$voice];
        }

        return '-random-';
    }

    public function adminNotice() {
        echo '<div class="notice notice-warning">' .
            '<p>'.
            __('To use the Audima plugin you must agree to our ', 'audima') .
            '<a href="http://audima.co/terms/" target="_blank">'.__('Terms of Use').'</a> and <a href="http://audima.co/privacy/" target="_blank">'.__('Privacy Policy').'</a>'.
            '</p>'.
            '<p>'.
            '
        <form action="'. admin_url( 'admin-post.php' ) .'" method="post">'.
            wp_nonce_field('nonce-to-check').
            '<input type="hidden" name="action" value="agree-audima-plugin" />
            <input class="button button-primary" type="submit" value="'.__('Agree', 'audima').'"></input>
        </form>'.
            '</p>'.
            '</div>';
    }

    public function on_save_agree()
    {
        check_admin_referer( 'nonce-to-check' );
        $this->updateOption( 'Agree', 'true' );
        $this->notifyStatusPlugin('agreed');
        exit( wp_redirect( admin_url( 'plugins.php?page=Audima_PluginSettings' ) ) );
    }

    public function updatePost( $post_ID )
    {
        global $wpdb;

        $tableName = $this->prefixTableName( 'audio' );

        return $wpdb->query(
            $wpdb->prepare(
                "update `$tableName` set overwrite = 'yes' where idpost = %d",
                $post_ID
            )
        );
    }

    public function clearCache()
    {
        global $wpdb;

        $tableName = $this->prefixTableName('audio');
        return $wpdb->query("truncate `$tableName`");
    }

    public function addQueryVars($vars)
    {
        $vars[] = "audimaaction";
        return $vars;
    }

    public function myEnqueue() {
        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
            wp_enqueue_script('select2', plugins_url('/js/select2.min.js', __FILE__));
            wp_enqueue_style('select2-css', plugins_url('/css/select2.min.css', __FILE__));
            wp_enqueue_script('audima-admin', plugins_url('/js/audima-admin.js', __FILE__));

        }
    }

    public function hasSystemRequirements()
    {
        $url = get_bloginfo('wpurl');

        $result = $this->callAudimaRest('/blog', array(
            "url" => $url,
            "action" => "check"
        ));

        return ($result !== false);
    }



    public function notifyStatusPlugin($status = 'enabled'){
        $idBlog = $this->getOption('IdBlog');
        $this->callAudimaRest("/notify/{$idBlog}/{$status}", array());
    }
}