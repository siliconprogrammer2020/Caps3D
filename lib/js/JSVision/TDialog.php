<?php
   include_once "TWindow.php";
?>

function TDialog(bounds,aTitle) { 
  TWindow.call(this,bounds,aTitle,0);
  this.growMode = 0;
  this.flags = wfMove | wfClose;
};

extend(TDialog,TWindow); //  inherit 

TDialog.prototype.handleEvent = function(event) { 
    TWindow.prototype.handleEvent.call(this,event);
    switch (event.what)
        {
        case evKeyDown:
            switch (event.keyDown)
                {
                case 'Esc':
                    event.what = evCommand;
                    event.message.command = cmCancel;
                    event.message.infoPtr = 0;
                    putEvent(event);
                    clearEvent(event);
                    break;
                case 'Enter':
                    event.what = evBroadcast;
                    event.message.command = cmDefault;
                    event.message.infoPtr = 0;
                    putEvent(event);
                    clearEvent(event);
                    break;
                }
            break;

        case evCommand:
            switch( event.message.command )
                {
                case cmOK:
                case cmCancel:
                case cmYes:
                case cmNo:
                    if( (state & sfModal) != 0 )
                        {
                        endModal(event.message.command);
                        clearEvent(event);
                        }
                    break;
                }
            break;
        }
}

TDialog.prototype.valid = function( command ) {
    if( command == cmCancel )
        return True;
    else
        return TGroup.prototype.valid.call(this, command );
}
