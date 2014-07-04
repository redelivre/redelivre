/**
 * Main admin control for the Panel interface
 *
 * @copyright Greg Priday 2013
 * @license GPL 2.0 http://www.gnu.org/licenses/gpl-2.0.html
 */

( function($){

    var newPanelIdInit = 0;
    panels.undoManager = new UndoManager();
    
    /**
     * A jQuery function to get widget data from a panel object
     */
    $.fn.panelsGetPanelData = function(){
        var $$ = $(this);
        var data = {};
        var parts;


        if( $$.data('missing') === true ) {
            // Start by handling the special case of the widget being missing
            data = JSON.parse( $$.find('input[name$="[data]"]').val() );
        }
        else if ( typeof $$.data('dialog') != 'undefined' && !$$.data('dialog').hasClass('ui-dialog-content-loading') ) {

            $$.data('dialog').find( '*[name]' ).not( '[data-info-field]' ).each( function () {
                var $$ = $(this);
                var name = /widgets\[[0-9]+\]\[(.*)\]/.exec( $$.attr('name') );

                name = name[1];
                parts = name.split('][');

                parts = parts.map(function(e){
                    if( !isNaN(parseFloat(e)) && isFinite(e) ) return parseInt(e);
                    else return e;
                });

                var sub = data;
                for(var i = 0; i < parts.length; i++) {
                    if(i == parts.length - 1) {

                        if( $$.attr('type') == 'checkbox' ){
                            if ( $$.is(':checked') ) sub[parts[i]] = $$.val() != '' ? $$.val() : true;
                        }
                        else {
                            sub[parts[i]] = $$.val();
                        }

                    }
                    else {
                        if(typeof sub[parts[i]] == 'undefined') {
                            sub[parts[i]] = {};
                        }
                        sub = sub[parts[i]];
                    }
                }
            } );

        }
        else if( $$.find('input[name$="[data]"]').val() == '' ) {
            data = {};
        }
        else {
            data = JSON.parse( $$.find('input[name$="[data]"]').val() );
        }

        return data;
    }

    /**
     * Create and return a new panel object
     *
     * @param type
     * @param data
     *
     * @return {*}
     */
    $.fn.panelsCreatePanel = function ( type, data ) {

        // Hide the undo message
        $('#panels-undo-message' ).fadeOut( function(){ $(this ).remove() } );

        var
            activeDialog,
            newPanelId = newPanelIdInit++,
            dialogWrapper = $( this )

        var panel = $( '<div class="panel new-panel"><div class="panel-wrapper"><div class="title"><h4></h4><span class="actions"></span></div><small class="description"></small></div></div>' );

        panel
            .attr('data-type', type)
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][data]" type="hidden">').val( JSON.stringify(data) ) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][raw]" type="hidden">').val(0) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][grid]" type="hidden">') )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][cell]" type="hidden">') )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][id]" type="hidden">').val(newPanelId) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][class]" type="hidden">').val(type) )
            .append( $('<input type="hidden" name="panel_order[]" type="hidden">').val(newPanelId) )
            .data('raw', false)
            .find( 'h4, h5' ).click( function () {
                $(this).closest('.panel').find('a.edit').click();
                return false;
            });


        var $$ = dialogWrapper.find('.panel-type' ).filter( function() { return $(this).data('class') === type } );
        if( $$.length != 0 ) {
            panel
                .data( {
                    // We need this data to update the title
                    'title': $$.data( 'title' )
                } )
                .find( '.description' ).html( $$.find( '.description' ).html() )
                .end().find( '.title h4' ).html( $$.find( 'h3' ).html() );
        }
        else if(typeof panelsMissingWidgets != 'undefined' && typeof panelsMissingWidgets[type] != 'undefined') {
            panel
                .data( {
                    // We need this data to update the title
                    'title': panelsMissingWidgets[type].title,
                    'missing' : true
                } )
                .find( '.description' ).html( panelsMissingWidgets[type].description )
                .end().find( '.title h4' ).html( panelsMissingWidgets[type].title );
        }
        else return;

        // Set the title
        panel.panelsSetPanelTitle( data );

        // The delete button
        var deleteFunction = function () {
            // Add an entry to the undo manager
            panels.undoManager.register(
                this,
                function(type, data, container, position){
                    // Readd the panel
                    var panel = $('#panels-dialog').panelsCreatePanel(type, data, container);
                    panels.addPanel(panel, container, position, true);

                    // We don't want to animate the undone panels
                    $( '#panels-container .panel' ).removeClass( 'new-panel' );
                },
                [ panel.attr('data-type'), panel.panelsGetPanelData(), panel.closest('.panels-container'), panel.index() ],
                'Remove Panel'
            );

            // Create the undo notification
            $('#panels-undo-message' ).remove();
            $('<div id="panels-undo-message" class="updated"><p>' + panels.i10n.messages.deleteWidget + ' - <a href="#" class="undo">' + panels.i10n.buttons.undo + '</a></p></div>' )
                .appendTo('body')
                .hide()
                .slideDown()
                .find('a.undo')
                .click(function(){
                    panels.undoManager.undo();
                    $('#panels-undo-message' ).fadeOut(function(){
                        $(this ).remove();
                    });
                    return false;
                })
            ;

            var remove = function () {
                // Remove the panel and refresh the grid container cell sizes
                var gridContainer = panel.closest('.grid-container');
                panel.remove();
                gridContainer.panelsResizeCells();
            };

            if(panels.animations) panel.slideUp( remove );
            else {
                panel.hide();
                remove();
            }
        };

        // Add the action buttons
        panel.find('.title .actions')
            .append(
                $('<a>' + panels.i10n.buttons.edit + '<a>' ).addClass('edit' ).click(function(){
                    if( typeof activeDialog != 'undefined' ) return false;

                    // The done button
                    var dialogButtons = {};
                    var doneClicked = false;
                    dialogButtons[ panels.i10n.buttons['done'] ] = function () {
                        doneClicked = true;
                        $( this ).trigger( 'panelsdone', panel, activeDialog );

                        var panelData = panel.panelsGetPanelData();

                        // If this was a missing widget form, then ignore what we get back from the form.
                        if( activeDialog.find('.panels-missing-widget-form').length == 0 ) {
                            panel.find('input[name$="[data]"]').val(JSON.stringify(panelData));
                            panel.panelsSetPanelTitle(panelData);
                            panel.find('input[name$="[info][raw]"]').val(1);
                        }

                        // Change the title of the panel
                        activeDialog.dialog( 'close' );
                    }

                    // Create a dialog for this form
                    activeDialog = $( '<div class="panel-dialog dialog-form"></div>' )
                        .data('widget-type', type)
                        .addClass( 'ui-dialog-content-loading' )
                        .addClass( 'widget-dialog-' + type.toLowerCase() )
                        .dialog( {
                            dialogClass: 'panels-admin-dialog',
                            autoOpen:    true,
                            modal:       false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
                            draggable:   false,
                            resizable:   false,
                            title:       panels.i10n.messages.editWidget.replace( '%s', panel.data( 'title' ) ),
                            minWidth:    760,
                            maxHeight:   Math.min( Math.round($(window).height() * 0.875), 800),
                            create:      function(event, ui){
                                $(this ).closest('.ui-dialog' ).find('.show-in-panels' ).show();
                            },
                            open:        function () {
                                // This fixes the A element focus issue
                                $(this ).closest('.ui-dialog' ).find('a' ).blur();

                                var overlay = $('<div class="siteorigin-panels-ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                                $(this).data( 'overlay', overlay ).closest( '.ui-dialog' ).before( overlay );
                            },
                            close: function(){
                                if(!doneClicked) {
                                    $( this ).trigger( 'panelsdone', panel, activeDialog );
                                }

                                // Destroy the dialog and remove it
                                $(this).data('overlay').remove();
                                $(this).dialog('destroy').remove();
                                activeDialog = undefined;
                            },
                            buttons: dialogButtons
                        } )
                        .keypress(function(e) {
                            if (e.keyCode == $.ui.keyCode.ENTER) {
                                if($(this ).closest('.ui-dialog' ).find('textarea:focus' ).length > 0) return;

                                // This is the same as clicking the add button
                                $(this ).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-button:eq(0)').click();
                                e.preventDefault();
                                return false;
                            }
                            else if (e.keyCode === $.ui.keyCode.ESCAPE) {
                                $(this ).closest('.ui-dialog' ).dialog('close');
                            }
                        });

                    // This is so we can access the dialog (and its forms) later.
                    panel.data('dialog', activeDialog);

                    // Load the widget form
                    var widgetClass = type;
                    try {
                        widgetClass = widgetClass.replace('\\\\', '\\');
                    }
                    catch(err){
                        return;
                    }


                    $.post(
                        ajaxurl,
                        {
                            'action' : 'so_panels_widget_form',
                            'widget' : widgetClass,
                            'instance' : JSON.stringify( panel.panelsGetPanelData() ),
                            'raw' : panel.find('input[name$="[info][raw]"]').val()
                        },
                        function(result){
                            // the newPanelId is defined at the top of this function.
                            try {
                                result = result.replace( /\{\$id\}/g, newPanelId );
                            }
                            catch (err) {
                                result = '';
                            }

                            activeDialog.html(result).dialog("option", "position", "center");

                            var $link = activeDialog.find('a.siteorigin-panels-help-link');
                            if($link.length) {
                                $link
                                    .attr('target', '_blank')
                                    .prependTo( activeDialog.closest('.ui-dialog').find('.ui-dialog-buttonpane') );
                            }


                            // This is to refresh the dialog positions
                            $( window ).resize();
                            $( document ).trigger('panelssetup', panel, activeDialog);
                            $( '#panels-container .panels-container' ).trigger( 'refreshcells' );

                            // This gives panel types a chance to influence the form
                            activeDialog.removeClass('ui-dialog-content-loading').trigger( 'panelsopen', panel, activeDialog );
                        },
                        'html'
                    );

                    return false;
                })
            )
            .append(
                $('<a>' + panels.i10n.buttons.duplicate + '<a>' ).addClass('duplicate' ).click(function(){
                    // Duplicate the widget
                    var duplicatePanel = $('#panels-dialog').panelsCreatePanel(panel.attr('data-type'), panel.panelsGetPanelData());
                    window.panels.addPanel(duplicatePanel, panel.closest('.panels-container'), null, false);
                    duplicatePanel.removeClass('new-panel');

                    return false;
                })
            )
            .append(
                $('<a>' + panels.i10n.buttons.delete + '<a>' ).addClass('delete').click(function(){
                    deleteFunction();
                    return false;
                })
            );

        return panel;
    }

    /**
     * Add a widget to the interface.
     *
     * @param panel The new panel (Widget) we're adding.
     * @param container The container we're adding it to
     * @param position The position
     * @param bool animate Should we animate the panel
     */
    panels.addPanel = function(panel, container, position, animate){
        if(container == null) container = $( '#panels-container .cell.cell-selected .panels-container' ).eq(0);
        if(container.length == 0) container = $( '#panels-container .cell .panels-container' ).eq(0);
        if(container.length == 0) {
            // There are no grids, so lets add one.
            panels.createGrid(1, [1]);
            container = $( '#panels-container .cell .panels-container' ).eq(0);
        }

        if (position == null) container.append( panel );
        else {
            var current = container.find('.panel' ).eq(position);
            if(current.length == 0) container.append( panel );
            else {
                panel.insertBefore(current);
            }
        }

        container.sortable( "refresh" ).trigger( 'refreshcells' );
        container.closest( '.grid-container' ).panelsResizeCells();
        if(animate) {
            if(panels.animations)
                $( '#panels-container .panel.new-panel' )
                    .hide()
                    .slideDown( 450 , function(){ panel.find('a.edit').click() } )
                    .removeClass( 'new-panel' );
            else {
                $( '#panels-container .panel.new-panel').show().removeClass( 'new-panel' );
                panel.find('a.edit').click();
            }
        }
    }

    /**
     * Set the title of the panel
     */
    $.fn.panelsSetPanelTitle = function ( data ) {
        return $(this ).each(function(){

            var titleValue = '';

            if( typeof data != 'undefined' ) {
                if( typeof data.title != 'undefined' && data.title != '' ) titleValue = data.title;
                else {
                    for ( var i in data ) {
                        if(typeof data[i] == 'string' && data[i] != '') {
                            titleValue = data[i];
                            break;
                        }
                    }
                }
            }

            try {
                titleValue = titleValue.substring(0, 80).replace(/(<([^>]+)>)/ig,"")
            }
            catch(err) {
                titleValue = '';
            }

            $(this ).find( 'h4' ).html( $(this ).data( 'title' ) + '<span>' + titleValue + '</span>' );
        });
    }

    /**
     * Loads panel data
     *
     * @param data
     */
    panels.loadPanels = function(data){
        panels.clearGrids();

        if( typeof data != 'undefined' && typeof data.grids != 'undefined' ) {
            // Create all the content
            for ( var gi in data.grids ) {
                var cellWeights = [];

                // Get the cell weights
                for ( var ci in data.grid_cells ) {
                    if ( Number( data.grid_cells[ci]['grid'] ) == gi ) {
                        cellWeights[cellWeights.length] = Number( data.grid_cells[ci].weight );
                    }
                }

                // Create the grids
                var grid = panels.createGrid( Number( data.grids[gi]['cells'] ), cellWeights, data.grids[gi]['style'] );

                // Add panels to the grid cells
                for ( var pi in data.widgets ) {

                    if( typeof  data.widgets[pi]['info'] == 'undefined') continue;

                    if ( Number( data.widgets[pi]['info']['grid'] ) == gi ) {
                        var pd = data.widgets[pi];
                        var panel = $('#panels-dialog').panelsCreatePanel( pd['info']['class'], pd );
                        grid
                            .find( '.panels-container' ).eq( Number( data.widgets[pi]['info']['cell'] ) )
                            .append( panel )
                    }
                }
            }
        }

        $( '#panels-container .panels-container' )
            .sortable( 'refresh' )
            .trigger( 'refreshcells' );

        // Remove the new-panel class from any of these created panels
        $( '#panels-container .panel' ).removeClass( 'new-panel' );
        
        // Make sure everything is sized properly
        $( '#panels-container .grid-container' ).each( function () {
            $( this ).panelsResizeCells();
        } );
    }

} )( jQuery );