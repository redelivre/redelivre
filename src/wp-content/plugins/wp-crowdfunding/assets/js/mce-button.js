/*========================================================================
 * Crowdfunding Shortcode Button
 *======================================================================== */
(function() {
	tinymce.PluginManager.add('crowdfunding_button', function( editor, url ) {
		editor.addButton( 'crowdfunding_button', {
			text: 'CF Shortcode',
			icon: false,
			type: 'menubutton',
			menu: [
				{
					text: 'Registration',
					onclick: function() {
						editor.insertContent('[wpcf_registration]');
					}
				},{
					text: 'Dashboard',
					onclick: function() {
						editor.insertContent('[wpcf_dashboard]');
					}
				},{
					text: 'Search',
					onclick: function() {
						editor.insertContent('[wpcf_search]');
					}
				},{
					text: 'Form Shortcode',
					onclick: function() {
						editor.insertContent('[wpcf_form]');
					}
				},{
					text: 'Listing',
					onclick: function() {
						editor.windowManager.open( {
							title: 'Campaign Listing Shortcode',
							body: [
								{
									type: 'textbox',
									name: 'number',
									label: 'number',
									value: '-1'
								},
								{
									type: 'textbox',
									name: 'cat',
									label: 'Category Slug',
									value: '',
								}
							],
							onsubmit: function( e ) {
								editor.insertContent( '[wpcf_listing number="' + e.data.number + '" cat="' + e.data.cat + '" ]');
							}
						});
					}
				},{
                    text: 'Single Campaign Page',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Campaign Single Shortcode',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'campaign_id',
                                    label: 'Campaign ID',
                                    value: '0'
                                }
                            ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[wpcf_single_campaign campaign_id="' + e.data.campaign_id + '" ]');
                            }
                        });
                    }
                },{
                    text: 'Single Campaign Box',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Single Campaign Box Shortcode',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'campaign_id',
                                    label: 'Campaign ID',
                                    value: '0'
                                }
                            ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[wpcf_campaign_box campaign_id="' + e.data.campaign_id + '" ]');
                            }
                        });
                    }
                },{
                    text: 'Popular Campaigns',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Single Campaign Box Shortcode',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'limit',
                                    label: 'Limit',
                                    value: '4'
                                },{
                                    type: 'textbox',
                                    name: 'column',
                                    label: 'Column',
                                    value: '4'
                                },
                                { 
                                    type: 'listbox',
                                    name: 'order',
                                    label: 'Order :',
                                    onselect: function(e) {

                                    },
                                    'values': [
                                        {text: 'DESC', value: 'DESC'},
                                        {text: 'ASC', value: 'ASC'}
                                    ]
                                },{
                                    type: 'textbox',
                                    name: 'class',
                                    label: 'Class',
                                    value: '',

                                    tooltip: 'Multiple classes should be separate by comma (,)',
                                }
                            ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[wpcf_popular_campaigns limit="' + e.data.limit + '" column="'+e.data.column+'" order="'+e.data.order+'" class="'+e.data.class+'" ]');
                            }
                        });
                    }
                }, {
                    text: 'Donate Button',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Campaign Donate Shortcode from Anywhere',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'campaign_id',
                                    label: 'Campaign ID',
                                    value: ''
                                },{
                                    type: 'textbox',
                                    name: 'amount',
                                    label: 'Amount',
                                    value: ''
                                },
                                {
                                    type: 'textbox',
                                    name: 'min_amount',
                                    label: 'Min Amount',
                                    value: ''
                                },
                                {
                                    type: 'textbox',
                                    name: 'max_amount',
                                    label: 'Max Amount',
                                    value: ''
                                },
                                {type: 'listbox',
                                    name: 'show_input_box',
                                    label: 'Show Input Box :',
                                    onselect: function(e) {

                                    },
                                    'values': [
                                        {text: 'Yes', value: 'true'},
                                        {text: 'No', value: 'false'}
                                    ]
                                },{
                                    type: 'textbox',
                                    name: 'donate_button_text',
                                    label: 'Donate Button Text',
                                    value: 'Donate Now',
                                }
                            ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[wpcf_donate campaign_id="' + e.data.campaign_id + '" amount="'+e.data.amount+'" min_amount="'+e.data.min_amount+'" max_amount="'+e.data.max_amount+'" show_input_box="'+e.data.show_input_box+'" donate_button_text="'+e.data.donate_button_text+'" ]');
                            }
                        });
                    }
                }
			]
		});
	});
})();




