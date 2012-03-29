Ext.ns('Tine.Messenger.Window');

Tine.Messenger.RootNode = function() {
    return Ext.getCmp('messenger-roster').getRootNode();
};

Tine.Messenger.Window.AddGroupHandler = function(dialog){
    var gname = dialog.findById('messenger-group-mngt-name').getValue().trim();
    if(Tine.Messenger.Window._addGroupAction(gname)){
        dialog.close();
    }   
}

Tine.Messenger.Window._addGroupAction = function(name){
    if(name){
        if(!Tine.Messenger.RosterTree().groupExist(name)){
            Tine.Messenger.RosterTree().addGroup(name);
            Tine.Messenger.LogHandler.status(name, _('Group created successfully!'));
            return true;
        } else {
            Ext.Msg.alert(_('Add Group'),_('The group already exists!'));
        }
    }
    return false;
}

Tine.Messenger.Window.AddBuddyWindow = function(_jid){
    
    if(_jid){
        Tine.Messenger.RosterTree().addBuddy(_jid);
    } else {
        _jid = '';
    }
    
    var dialog = new Tine.Messenger.SimpleDialog(Tine.Messenger.Config.AddBuddyLayout).init();
         
    dialog.findById('messenger-contact-add-jid').setValue(_jid).disable();
}

Tine.Messenger.Window.AddBuddyHandler = function(dialog){
    
    var jid = dialog.findById('messenger-contact-add-jid').getValue().trim(),
        name = dialog.findById('messenger-contact-add-name').getValue().trim(),
        group = dialog.findById('messenger-contact-add-group').getValue();

    var result = Tine.Messenger.Window._addBuddyAction(jid, name, group);
    if(result){
        dialog.close();
    }
    
}

Tine.Messenger.Window._addBuddyAction = function(jid, name, group){
    
    var buddy = Tine.Messenger.RosterHandler.getContactElement(jid);
    if(buddy){
        var buddys = new Array();
        buddys.push([jid, name, group]);
//        Tine.Messenger.RosterHandler.renameContact(jid, name, group);
        Tine.Messenger.RosterHandler.modifyBuddys(buddys);
        Tine.Messenger.LogHandler.status(jid || name, _('Added successfuly!'));
        return true;
    } else {
        if(jid){
            if(Tine.Messenger.RosterHandler.addContact(jid, name, group)){
                Tine.Messenger.LogHandler.status(jid || name, _('Added successfuly!'));
            } else {
                Ext.Msg.alert(_('Add Buddy'),_('Buddy not added!'))
            }
            return true;
        }
    }
    return false;
}

Tine.Messenger.Window._onMoveWindowAction = function (_dialog){
    var pos_X = _dialog.x,
        pos_Y = _dialog.y,
        win_X = _dialog.container.dom.clientWidth,
        win_Y = _dialog.container.dom.clientHeight,
        width = _dialog.width,
        height = _dialog.height;
        
//        Tine.Messenger.Log.debug(pos_X +' x '+ pos_Y +' : position');
//        Tine.Messenger.Log.debug(win_X +' x '+ win_Y +' : window');
//        Tine.Messenger.Log.debug(width +' x '+ height +' : length');
        
    var new_X = pos_X,
        new_Y = pos_Y;
        
	new_X = (pos_X < 0) ? 5 : new_X;
        new_X = (win_X < pos_X + 30) ? win_X - 50 : new_X;
        
        new_Y = (pos_Y < 0) ? 5 : new_Y;
        new_Y = (win_Y < pos_Y + 30) ? win_Y - 50 : new_Y;
        
        
        
        if(pos_X != new_X || pos_Y != new_Y)
            _dialog.setPagePosition(new_X, new_Y);
}