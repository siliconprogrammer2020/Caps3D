<?php
   include_once "TGroup.php";
?>

const wfMove  = 0x01,
      wfGrow  = 0x02,
      wfClose = 0x04,
      wfZoom  = 0x08;

function TWindow(bounds,title,number) {  
  TGroup.call(this,bounds);
  this.flags=wfMove | wfGrow | wfClose | wfZoom;
  this.getBounds(this.zoomRect = new TRect());
  this.number= number ;
  //this.palette =wpBlueWindow;
  this.frame=null;
  this.insert(new TStaticText(new TRect(10,10,this.size.x,60),this.title=title));
  this.state |= sfShadow;
  this.options |= ofSelectable | ofTopSelect;
  this.growMode = gfGrowAll | gfGrowRel;
  this.eventMask |= evMouseUp; //for TFrame
  
  //this.insert(new TFrame(this));
  this.insert(this.CLOSE = new TSpeedButton(new TPoint(this.size.x-52,10),'/image/close.png','AppletList['+this.id+'].close();'));
};

extend(TWindow,TGroup); //  inherit 


TWindow.prototype.close = function() { // draw complete view
  this.hide();

// NOTE: replace above code with this code.
//    if( this.valid( cmClose ) )
//        { // SET: tell the application we are closing
//        message( Application, evBroadcast, cmClosingWindow, this );
//        this. frame = null;  // so we don't try to use the frame after it's been deleted
//        CLY_destroy( this );
//        }
};

TWindow.prototype.getTitle = function( short ) {
    return TVIntl.prototype.getText.call(this,title,intlTitle);
};

TWindow.prototype.handleEvent = function( event ) { 
 var  limits = new TRect();
 var min= new TPoint(), max= new TPoint();

    TGroup.prototype.handleEvent.call(this,event);
    if( event.what== evCommand )
        switch (event.message.command)
            {
            case  cmResize:
                if( (flags & (wfMove | wfGrow)) != 0 )
                    {
                    limits = owner.getExtent();
                    sizeLimits(min, max);
                    dragView( event, dragMode | (flags & (wfMove | wfGrow)),
                              limits, min, max);
                    clearEvent(event);
                    }
                break;
            case  cmClose:
                if( (flags & wfClose) != 0 &&
                    ( event.message.infoPtr == 0 || event.message.infoPtr == this )
                  )
                    {
                    if( (state & sfModal) == 0 )
                        close();
                    else
                        {
                        event.what = evCommand;
                        event.message.command = cmCancel;
                        putEvent( event );
                        }
                    clearEvent( event );
                    }
                break;
            case  cmZoom:
                if( (flags & wfZoom) != 0 &&
                    (event.message.infoPtr == 0 || event.message.infoPtr == this)
                  )
                    {
                    zoom();
                    clearEvent(event);
                    }
                break;
            }
    else if( event.what == evKeyDown )
            switch (event.keyDown)
                {
                case  'Tab':
                case  'ArrowDown': case 'Down':
                case  'ArrowRight': case 'Right':
                    selectNext(False);
                    clearEvent(event);
                    break;
                //case  kbShiftTab:
                case  'ArrowUp': case 'Up':
                case  'ArrowLeft': case 'Left':
                    selectNext(True);
                    clearEvent(event);
                    break;
                }
    else if( event.what == evBroadcast && 
             event.message.command == cmSelectWindowNum &&
             event.message.infoInt == number && 
             (options & ofSelectable) != 0
           )
            {
            select();
            clearEvent(event);
            }
};

TWindow.prototype.InitFrame = function(r) { // draw complete view
  return new TFrame(r);
};

TWindow.prototype.setState = function( aState, enable ) {
  function C(x) { if (enable == True) enableCommand(x); else disableCommand(x) };
    TGroup.prototype.setState.call(this,aState, enable);
    if( (aState & sfSelected) != 0 )
        {
        setState(sfActive, enable);
        if( frame != 0 )
            frame.setState(sfActive,enable);
        C(cmNext);
        C(cmPrev);
        if( (flags & (wfGrow | wfMove)) != 0 )
          {
            C(cmResize);
          }
        if( (flags & wfClose) != 0 )
          {
            C(cmClose);
          }
        if( (flags & wfZoom) != 0 )
          {
            C(cmZoom);
          }
        }
};

TWindow.prototype.sizeLimits = function( min, max ) {
    TView.prototype.sizeLimits.call(this,min, max);
    min = minWinSize;
};

TWindow.prototype.standardScrollBar = function(AOptions) { // draw complete view
//  var sb = new TScrollBar(this,AOptions);
//  this.list.push(sb);
//  return sb;

    var  r = new TRect();
    this.getExtent(r);
    if( (aOptions & sbVertical) != 0 )
        r.assign( r.b.x-1, r.a.y+1, r.b.x, r.b.y-1 );
    else
        r.assign( r.a.x+2, r.b.y-1, r.b.x-2, r.b.y );

    var s = new TScrollBar(r);
    insert(s);
    if( (aOptions & sbHandleKeyboard) != 0 )
        s.options |= ofPostProcess;
    return s;

};

TWindow.prototype.zoom =function() {
    var minSize = new TPoint(), maxSize = new TPoint();
    sizeLimits( minSize, maxSize );
    if( size != maxSize )
        {
        zoomRect = getBounds();
        var r= new TRect( 0, 0, maxSize.x, maxSize.y );
        locate(r);
        }
    else
        locate( zoomRect );
};

TWindow.prototype.setTitle = function(title) { // draw complete view
  this.title='&nbsp;'+title+'&nbsp;&nbsp;';
  document.getElementById("title"+this.id).innerHTML='&nbsp;'+title+'&nbsp;&nbsp;';
};

TWindow.prototype.style = function() { 
  return TGroup.prototype.style.call(this)+'background-color:#ffbd31; box-shadow: 5px 5px 5px #000000;border-radius: 10px; border:1px solid black;';
};

TWindow.prototype.draw = function() {
 TGroup.prototype.draw.call(this);
 this.CLOSE.setState(sfVisible,(this.flags & wfClose)!=0 );
};
