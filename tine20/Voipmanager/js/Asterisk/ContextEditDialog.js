/**
 * Tine 2.0
 * 
 * @package     Voipmanager
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Thomas Wadewitz <t.wadewitz@metaways.de>
 * @copyright   Copyright (c) 2007-2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id:$
 *
 */
 
Ext.namespace('Tine.Voipmanager');

/**
 * Asterisk Context Edit Dialog
 */
Tine.Voipmanager.AsteriskContextEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {

    
    /**
     * @private
     */
    windowNamePrefix: 'AsteriskContextEditWindow_',
    appName: 'Voipmanager',
    recordClass: Tine.Voipmanager.Model.AsteriskContext,
    recordProxy: Tine.Voipmanager.AsteriskContextBackend,
    loadRecord: false,
    tbarItems: [{xtype: 'widget-activitiesaddbutton'}],
    
    /**
     * overwrite update toolbars function (we don't have record grants yet)
     */
    updateToolbars: function(record) {
        this.onContextUpdate();
    	Tine.Voipmanager.AsteriskContextEditDialog.superclass.updateToolbars.call(this, record, 'id');
    },
    
    /**
     * this gets called when initializing and if a new timeaccount is chosen
     * 
     * @param {} field
     * @param {} timeaccount
     */
    onContextUpdate: function(field, timeaccount) {
        
    },
    
    /**
     * returns dialog
     * 
     * NOTE: when this method gets called, all initalisation is done.
     */
    getFormItems: function() { 
        return {
            xtype: 'tabpanel',
            border: false,
            plain:true,
            activeTab: 0,
            items:[
            	{
                    xtype: 'textfield',
                    fieldLabel: this.app.i18n._('Name'),
                    name: 'name',
                    maxLength: 80,
                    anchor: '100%',
                    allowBlank: false
                }, {
                    xtype: 'textarea',
                    name: 'description',
                    fieldLabel: this.app.i18n._('Description'),
                    grow: false,
                    preventScrollbars: false,
                    anchor: '100%',
                    height: 40
                }]
        };
    }
});

/**
 * Asterisk Context Edit Popup
 */
Tine.Voipmanager.AsteriskContextEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 800,
        height: 470,
        name: Tine.Voipmanager.AsteriskContextEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Voipmanager.AsteriskContextEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};