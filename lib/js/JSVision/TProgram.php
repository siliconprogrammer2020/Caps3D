<?php
 include_once "TGroup.php";
?>

var statusLine = null;
var MenuBar = null;
var DeskTop = null;
var Application;

var doNotHandleAltNumber = false;

function TProgram(width,height) { 
  Application = this;
  var bounds = new TRect(0,0,width,height);
  TGroup.call(this,bounds);
  this.initScreen();
  this.state = sfVisible | sfSelected | sfFocused | sfModal | sfExposed;
  this.options = 0;

  this.inIdle = false;

//  this.push([this.InitDeskTop();,this.InitMenuBar(),this.InitStatusBar()]);
  this.insert(statusLine=this.initStatusLine(bounds));
  this.insert(MenuBar=this.InitMenuBar());
  this.insert(DeskTop=this.InitDeskTop());
};

extend(TProgram,TGroup); //  inherit 

TProgram.prototype.getEvent = function(event) {
    if( pending.what != evNothing )
        {
        event = pending;
        pending.what = evNothing;
        inIdle=False;
        }
    else
        {
        event.getMouseEvent();
        if( event.what == evNothing )
            {
            event.getKeyEvent();
            if( event.what == evNothing )
                {
                if( this.inIdle )
                    {
                    var t=Date.now();
                    inIdleTime+=t-lastIdleClock;
                    lastIdleClock=t;
                    }
                else
                    {
                    inIdleTime=0;
                    lastIdleClock=Date.now();
                    this.inIdle=true;
                    }
               // if (TScreen.checkForWindowSize())
               //   {
               //    setScreenMode(0xFFFF);
               //    CLY_Redraw();
               //   }
                this.idle();
                }
            else
                inIdle=False;
            }
        else
            inIdle=False;
        }

    if( statusLine != null )
        {
        if( (event.what & evKeyDown) != 0 ||
            ( (event.what & evMouseDown) != 0 &&
              firstThat( hasMouse, event ) == statusLine
            )
          )
            statusLine.handleEvent( event );
        }
};

TProgram.prototype.handleEvent = function( event ) { 
    if( !doNotHandleAltNumber && event.what == evKeyDown )
        {
        var c = event.keyDown;
        if( c >= '1' && c <= '9' )
            {
               if (current.valid(cmReleasedFocus))
               {
                   if( message( deskTop,
                            evBroadcast,
                            cmSelectWindowNum,
                            (c - '0')
                           ) != 0 )
                   clearEvent( event );
               }
            }
        }

    TGroup.prototype.handleEvent.call(this, event );
    if( event.what == evCommand && event.message.command == cmQuit )
        {
        endModal( cmQuit );
        clearEvent( event );
        }
};

TProgram.prototype.idle = function()
{
    if( statusLine != null )
        statusLine.update();

    if( commandSetChanged == true )
        {
        message( this, evBroadcast, cmCommandSetChanged, 0 );
        commandSetChanged = false;
        }
};

TProgram.prototype.InitDeskTop = function() {
  return null;
};

TProgram.prototype.initScreen = function() {// insert any additonal code you want executed when windows resizes or redrawn
 this.draw();
};

TProgram.prototype.InitMenuBar = function() {
  return null;
};

TProgram.prototype.initStatusLine = function(bounds) {
    var r = new TRect(bounds);
    r.a.y=r.b.y-20;
//    return new TStatusLine( r,
//        new TStatusDef( 0, 0xFFFF ) +
//            new TStatusItem( __("~Alt-X~ Exit"), kbAltX, cmQuit ) +
//            new TStatusItem( 0, kbF10, cmMenu ) +
//            new TStatusItem( 0, kbAltF3, cmClose ) +
//            new TStatusItem( 0, kbF5, cmZoom ) +
//            new TStatusItem( 0, kbCtrlF5, cmResize )
//            );

  // NOTE: REPLACE BELLOW CODE WITH ABOVE CODE
  return null;
};

TProgram.prototype.outOfMemory = function() {
};

TProgram.prototype.putEvent = function( event ){
    pending = event;
};

TProgram.prototype.run= function() {
   var self = this;
   if (typeof Promise !=='undefined') {
    const p = new Promise(function() {self.execute()});
   } else self.execute();
//    this.execute();
};

TProgram.prototype.validView = function(p) {
    if( p == 0 )
        return 0;
    if( lowMemory() )
        {
        CLY_destroy( p );
        outOfMemory();
        return 0;
        }
    if( !p.valid( cmValid ) )
        {
        CLY_destroy( p );
        return 0;
        }
    return p;
};

