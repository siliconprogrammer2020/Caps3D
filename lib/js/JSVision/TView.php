<?php
 include_once "TRect.php";
 include_once "TObject.php";
 include_once "TEvent.php";
 include_once "../lib/js/extend.php";
?>
const sfVisible   = 0x001,
      sfCursorVis = 0x002,
      sfCursorIns = 0x004,
      sfShadow    = 0x008,
      sfActive    = 0x010,
      sfSelected  = 0x020,
      sfFocused   = 0x040,
      sfDragging  = 0x080,
      sfDisabled  = 0x100,
      sfModal     = 0x200,
      sfExposed   = 0x800,

      dmDragMove  = 0x001,
      dmDragGrow  = 0x002,
      dmLimitLoX  = 0x010,
      dmLimitLoY  = 0x020,
      dmLimitHiX  = 0x040,
      dmLimitHiY  = 0x080,

      ofSelectable = 0x0001,
      ofTopSelect  = 0x0002,
      ofFirstClick = 0x0004,
      ofFramed     = 0x0008,
      ofPreProcess = 0x0010,
      ofPostProcess= 0x0020,
      ofBuffered   = 0x0040,
      ofTileable   = 0x0080,
      ofCenterX    = 0x0100,
      ofCenterY    = 0x0200,

      gfGrowLoX    = 0x0001,
      gfGrowLoY    = 0x0002,
      gfGrowHiX    = 0x0004,
      gfGrowHiY    = 0x0008,
      gfGrowAll    = 0x000f,
      gfGrowRel    = 0x0010,

//  Standard command codes

      cmValid         = 0,
      cmQuit          = 1,
      cmError         = 2,
      cmMenu          = 3,
      cmClose         = 4,
      cmZoom          = 5,
      cmResize        = 6,
      cmNext          = 7,
      cmPrev          = 8,
      cmHelp          = 9,

//  TDialog standard commands

      cmOK            = 10,
      cmCancel        = 11,
      cmYes           = 12,
      cmNo            = 13,
      cmDefault       = 14,

//  Application command codes

      cmCut           = 20,
      cmCopy          = 21,
      cmPaste         = 22,
      cmUndo          = 23,
      cmClear         = 24,
      cmTile          = 25,
      cmCascade       = 26,

// Standard messages 

     cmReceivedFocus     = 50,
     cmReleasedFocus     = 51,
     cmCommandSetChanged = 52,

// TScrollBar messages 

     cmScrollBarChanged  = 53,
     cmScrollBarClicked  = 54,

// TWindow select messages 

     cmSelectWindowNum   = 55,

//  TListViewer messages

     cmListItemSelected  = 56,

// SET: This is a broadcast that TWindow sends to TProgram::application
// to notify the window is closing. In this way the application can keep
// track of closed windows

     cmClosingWindow     = 57,

// SET: Usually the owner doesn't have any information about the state of
// your TCluster members. I added this to change the situation so you don't
// need to create a specialized TCluster for it.
// I think that's how an event/message system should work.
     
     cmClusterMovedTo    = 58,
     cmClusterPress      = 59,

    // From TButton::press()
     cmRecordHistory     = 60,

// SET: Here is a broadcast for TListViewer objects. It sends a broadcast
// when an item is focused.

     cmListItemFocused   = 61,

// SET: Moved here to know they exist

     cmGrabDefault       = 62,
     cmReleaseDefault    = 63,

// SET: This is a broadcast sent each time the code page encoding changes.
//      All objects that uses non ASCII symbols should remap them.

     cmUpdateCodePage    = 64,

// SET: The user screen it not always available. This command is disabled in
//      this case.

     cmCallShell         = 65,

//  Event masks

     positionalEvents    = evMouse,
     focusedEvents       = evKeyboard | evCommand,

     normalSelect=0,
     enterSelect =1,
     leaveSelect =2;

var commandSetChanged = false;

function TView(bounds) { 
   TObject.call(this);
   this.owner = null;
   this.next = null;
   this.origin = new TPoint();
   this.size = new TPoint();
   this.cursor = new TPoint();
   this.growMode = 0;
   this.dragMode = dmLimitLoY;
   this.helpCtx = null;
   this.state = sfVisible;
   this.options = 0;
   this.eventMask = evMouseDown | evKeyDown | evCommand;

   this.content='';
   this._class=null;
   this._style='';

    if (arguments.length === 1) {
     this.setBounds(bounds);
    }
};


extend(TView,TObject); //  inherit TObject

TView.prototype.handleEvent = function(ev) { 
  if (ev.what == evMouseDown) {
   if (!(this.state & (sfSelected | sfDisabled)) && (this.options & ofSelectable)) {
    this.select();
    if (!(this.state & sfSelected) || !(this.options & ofFirstClick)) {
     this.clearEvent(ev);
    }
   }
  }
};

TView.prototype.blockCursor = function() { 
  setState(sfCursorIns,true);
};

TView.prototype.calcBounds = function(bounds,delta) { 
  bounds = getBounds();
 
  var s = owner.size.x;
  var d= delta.x;

  if ((growMode & gfGrowLoX) != 0)
    grow(Bounds.a.x);

  if ((growMode & gfGrowHiX) != 0)
    grow(bounds.b.x);

  s=owner.size.y;
  d=delta.y;

  if ((growMode & gfGrowLoY) != 0)
    grow(bounds.a.y);

  if ((growMode & gfGrowHiY) != 0)
    grow(bounds.b.y);

  minLim = new TPoint();
  maxLim = new TPoint();
  sizeLimits(minLim,maxLim); 
  bounds.b.x = bounds.a.x + range( bounds.b.x-bounds.a.x,minLim.x,maxLim.x);
  bounds.b.y = bounds.a.y + range( bounds.b.y-bounds.a.y,minLim.y,maxLim.y);
};

TView.prototype.changeBounds = function(bounds) {
  setBounds(bounds);
  drawView();
};

TView.prototype.clearEvent = function(ev) {
  ev.what=evNothing;
  ev.event.preventDefault();
};

TView.prototype.commandEnabled = function(command) {
  return curCommandSet.has(command);
};

TView.prototype.disableCommands = function(commands) {
  commandSetChanged = commandSetChnaged || !(curCommandSet & commands).isEmpty();
  curCommandSet.disableCmd(commands);
};

TView.prototype.dragView = function(event,mode,limits,minSize,maxSize) {
    var saveBounds = new TRect();

    var p = new TPoint();
    var s = new TPoint();
    setState( sfDragging, True );

    if( event.what == evMouseDown )
        {
        if( (mode & dmDragMove) != 0 )
            {
            p = origin - event.mouse.where;
            do  {
                event.mouse.where += p;
                moveGrow( event.mouse.where, size, limits, minSize, maxSize, mode);
                } while( mouseEvent(event,evMouseMove) );
            }
        else
            {
            p = size - event.mouse.where;
            do  {
                event.mouse.where += p;
                moveGrow( origin, event.mouse.where, limits, minSize, maxSize, mode);
                } while( mouseEvent(event,evMouseMove) );
            }
        }
    else
        {
            dragView.goLeft      =  new TPoint(-1, 0); 
            dragView.goRight     =  new TPoint( 1, 0); 
            dragView.goUp        =  new TPoint( 0,-1); 
            dragView.goDown      =  new TPoint( 0, 1); 
            dragView.goCtrlLeft  =  new TPoint(-8, 0); 
            dragView.goCtrlRight =  new TPoint( 8, 0);
            
        saveBounds = getBounds();
        do  {
            p = origin;
            s = size;
            keyEvent(event);
            switch (event.keyDown.keyCode)
                {
                case kbLeft:
                    change(mode,goLeft,p,s);
                    break;
                case kbRight:
                    change(mode,goRight,p,s);
                    break;
                case kbUp:
                    change(mode,goUp,p,s);
                    break;
                case kbDown:
                    change(mode,goDown,p,s);
                    break;
                case kbCtLeft:
                    change(mode,goCtrlLeft,p,s);
                    break;
                case kbCtRight:
                    change(mode,goCtrlRight,p,s);
                    break;
                // Shift info goes in the key
                case kbShLeft:
                    change(mode,goLeft,p,s,1);
                    break;
                case kbShRight:
                    change(mode,goRight,p,s,1);
                    break;
                case kbShUp:
                    change(mode,goUp,p,s,1);
                    break;
                case kbShDown:
                    change(mode,goDown,p,s,1);
                    break;
                case kbShCtLeft:
                    change(mode,goCtrlLeft,p,s,1);
                    break;
                case kbShCtRight:
                    change(mode,goCtrlRight,p,s,1);
                    break;
                case kbHome:
                    p.x = limits.a.x;
                    break;
                case kbEnd:
                    p.x = limits.b.x - s.x;
                    break;
                case kbPgUp:
                    p.y = limits.a.y;
                    break;
                case kbPgDn:
                    p.y = limits.b.y - s.y;
                    break;
                }
            moveGrow( p, s, limits, minSize, maxSize, mode );
            } while( event.keyDown.keyCode != kbEsc &&
                     event.keyDown.keyCode != kbEnter
                   );
        if( event.keyDown.keyCode == kbEsc )
            locate(saveBounds);
        }
    setState(sfDragging, False);
};


TView.prototype.draw = function(content) { // draw complete view
  if (arguments.length === 0) {
   content="";
  };

  var temp=document.getElementById("view"+this.id);
  if (temp!=null) {
    temp.innerHTML=content;
    temp.style.cssText=this.style();
    if (this._class!=null) temp.className=this._class;
    return;
  };

  var _class=(this._class!=null) ? "class='"+this._class+"'":"";
  var html="<div id='view"+this.id+"' "+_class+" style='"+this.style()+"' "+this.other()+">"+this.content+content+"</div>";

  if (this.owner==null) document.body.innerHTML+=html;
  else {
    var temp=document.getElementById("view"+this.owner.id);
    if (temp!=null) temp.innerHTML+=html;
  }

  return html;
};

TView.prototype.drawCursor = function() {
    // SET: do it only if our owner gives permission
    if( (this.state & sfFocused) != 0 && this.owner && this.owner.canShowCursor())
        this.resetCursor();
};

TView.prototype.drawHide = function( lastView ) {
    this.drawCursor();
    this.drawUnderView(this.state & sfShadow, lastView);
};

TView.prototype.drawShow = function( lastView ) {
    this.drawView();
    if( (this.state & sfShadow) != 0 )
        this.drawUnderView( true, lastView );
};

TView.prototype.drawUnderRect = function( r, lastView ) {
    this.owner.clip.intersect(r);
    this.owner.drawSubViews(this.nextView(), lastView);
    this.owner.getExtent(this.owner.clip );
};

TView.prototype.drawUnderView = function( doShadow, lastView ) {
    var r = new TRect();
    this.getBounds(r);
    if( doShadow != false )
        r.b += shadowSize;
    this.drawUnderRect( r, lastView );
};

TView.prototype.drawView = function() {
    if (this.exposed())
        { console.log('drawView: '+this.id);
        this.draw();
        this.drawCursor();
        }
};

TView.prototype.enableCommands = function( commands ) {
    commandSetChanged = commandSetChanged || ((curCommandSet&commands) != commands);
    curCommandSet += commands;
};

TView.prototype.endModal = function( command ) {
    if( TopView() != 0 )
        TopView().endModal(command);
};

TView.prototype.eventAvail = function() {
    var event = new TEvent();
    getEvent(event);
    if( event.what != evNothing )
        putEvent(event);
    return event.what != evNothing;
};

TView.prototype.getBounds = function(bounds) {
  bounds.assign(this.origin.x,this.origin.y,this.origin.x+this.size.x,this.origin.y+this.size.y);
};

TView.prototype.execute = function() {
    return cmCancel;
};

TView.prototype.getClipRect = function(clip) {
    this.getBounds(clip);
    if( this.owner != null )
        clip.intersect(this.owner.clip);
    clip.move(-origin.x, -origin.y);
};

TView.prototype.resetCursor = function() {
 // NOTE: NOT YET IMPLEMENTED
};

TView.prototype.exposed = function() {
 return true;
};

TView.prototype.getCommands = function( commands ) {
    commands = curCommandSet;
};

TView.prototype.getEvent = function( event ) {
    if( owner != 0 )
        owner.getEvent(event);
};

TView.prototype.getExtent = function(extent) {
    extent.assign( 0, 0, this.size.x, this.size.y );
};

TView.prototype.getHelpCtx = function() {
    if( (state & sfDragging) != 0 )
        return hcDragging;
    return helpCtx;
};

TView.prototype.getState = function( aState ) {
    return (state & aState) == aState ;
};

TView.prototype.growTo = function( x, y ) {
    var r = new TRect();
    r.assign(origin.x, origin.y, origin.x + x, origin.y + y);
    locate(r);
};

TView.prototype.hide = function() { // draw complete view
     if( (this.state & sfVisible) != 0 )
        this.setState( sfVisible, false );
};

TView.prototype.hideCursor = function() {
    this.setState( sfCursorVis, false );
};

TView.prototype.keyEvent = function( event ) {
    do {
       getEvent(event);
        } while( event.what != evKeyDown );
};

TView.prototype.locate = function( bounds ) {
    var min = new TPoint();
    var max = new TPoint();
    sizeLimits(min, max);
    bounds.b.x = bounds.a.x + range(bounds.b.x - bounds.a.x, min.x, max.x);
    bounds.b.y = bounds.a.y + range(bounds.b.y - bounds.a.y, min.y, max.y);
    var r = new TRect();
    r = getBounds();
    if( bounds != r )
        {
        changeBounds( bounds );
        if( owner != 0 && (state & sfVisible) != 0 )
            {
            if( (state & sfShadow) != 0 )
                {
                r.Union(bounds);
                r.b += shadowSize;
                }
            drawUnderRect( r, 0 );
            }
        }
};

TView.prototype.makeFirst = function() {
    putInFrontOf(owner.first());
};

TView.prototype.makeGlobal = function( source ) {
    var temp = new TPoint();
    temp = source + origin;
    var cur = this;
    while( cur.owner != 0 )
        {
        cur = cur.owner;
        temp += cur.origin;
        }
    return temp;
};

TView.prototype.makeLocal = function( source ) {
    var temp = new TPoint();
    temp = source - origin;
    var cur = this;
    while( cur.owner != 0 )
        {
        cur = cur.owner;
        temp -= cur.origin;
        }
    return temp;
};

TView.prototype.mouseEvent = function(event, mask) {
    do {
       getEvent(event);
        } while( !(event.what & (mask | evMouseUp)) );

    return event.what != evMouseUp;
};

TView.prototype.mouseInView = function(mouse) {
     mouse = makeLocal( mouse );
     var r = getExtent();
     return r.contains(mouse);
};

TView.prototype.moveTo = function(x,y) {
  this.origin.assign(x,y);
  document.getElementById("view"+this.id).style.left=x+'px';
  document.getElementById("view"+this.id).style.top=y+'px';

     var r = new TRect( x, y, x+this.size.x, y+this.size.y );
     this.locate(r);
};

TView.prototype.nextView = function() {
    if( this == this.owner.last )
        return null;
    else
        return this.next;
};

TView.prototype.normalCursor = function() {
    setState(sfCursorIns, False);
};

TView.prototype.prev = function() {
    var res = this;
    while( res.next != this )
        res = res.next;
    return res;
};

TView.prototype.prevView = function() {
    if( this == owner.first() )
        return 0;
    else
        return prev();
};

TView.prototype.putEvent = function( event ) {
    if( owner != 0 )
        owner.putEvent(event);
};

TView.prototype.putInFrontOf = function( Target ) {
    var p, lastView;

    if( owner != 0 && Target != this && Target != nextView() &&
         ( Target == 0 || Target.owner == owner)
      )
        if( (state & sfVisible) == 0 )
            {
            owner.removeView(this);
            owner.insertView(this, Target);
            }
        else
            {
            lastView = nextView();
            p = Target;
            while( p != 0 && p != this )
                p = p.nextView();
            if( p == 0 )
                lastView = Target;
            state &= ~sfVisible;
            if( lastView == Target )
                drawHide(lastView);
            owner.removeView(this);
            owner.insertView(this, Target);
            state |= sfVisible;
            if( lastView != Target )
                drawShow(lastView);
            if( (options & ofSelectable) != 0 )
                owner.resetCurrent();
            }
};

TView.prototype.select = function() {
    if( (options & ofTopSelect) != 0 )
        makeFirst();
    else if( owner != 0 )
        owner.setCurrent( this, normalSelect );
};

TView.prototype.setBounds = function(bounds) {
     this.origin.assign(bounds.a.x,bounds.a.y);
     this.size.assign(bounds.b.x-bounds.a.x,bounds.b.y-bounds.a.y);
};

TView.prototype.setCommands = function( commands ) {
    commandSetChanged = commandSetChanged || (curCommandSet != commands );
    curCommandSet = commands;
};

TView.prototype.setCursor = function( x, y ){
    cursor.assign(x,y);
    drawCursor();
};

TView.prototype.setState = function(aState,enable) {
  if (enable==true) this.state |= aState;
  else this.state &=~aState;

    if( this.owner == null )
        return;

    switch( aState )
        {
        case  sfVisible: 
            if( (this.owner.state & sfExposed) != 0 )
                this.setState( sfExposed, enable ); 
            if( enable == true ) {
              var temp=document.getElementById("view"+this.id);
              if (temp!=null) temp.style.display="block";
              //else this.draw();
            } else {
              var temp=document.getElementById("view"+this.id);
              if (temp!=null) temp.style.display="none"; 
            };
            if( (this.options & ofSelectable) != 0 )
                this.owner.resetCurrent();
            break;
        case  sfCursorVis:
        case  sfCursorIns:
            this.drawCursor();
            break;
        case  sfShadow:
            this.drawUnderView( true, 0 );
            break;
        case  sfFocused:
            if (this.owner && this.owner.canShowCursor())
               // SET: do it only if our owner gives permission
               this.resetCursor();
            message( this.owner,
                     evBroadcast,
                     (enable == true) ? cmReceivedFocus : cmReleasedFocus,
                     this
                   );
            break;
        }
};

TView.prototype.show = function() { // draw complete view
    if( (this.state & sfVisible) == 0 )
        this.setState(sfVisible, true);
};

TView.prototype.showCursor = function() {
    this.setState( sfCursorVis, true );
};

TView.prototype.sizeLimits = function( min, max ){
    min.assign(0,0);
    if( this.owner != null )
        max = this.owner.size;
    else
        max.x = max.y = Number.MAX_SAFE_INTEGER;
};

TView.prototype.TopView = function() {
    if( TheTopView != 0 )
        return TheTopView;
    else
        {
        var p = this;
        while( p != 0 && !(p.state & sfModal) )
            p = p.owner;
        return p;
        }
};

TView.prototype.valid = function( command ) {
    return true;
};

function message( receiver, what, command, infoPtr) {
    if( receiver == null)
        return null;

    var event = new TEvent();
    event.what = what;
    event.message.command = command;
    event.message.infoPtr = infoPtr; 
    receiver.handleEvent( event );
    if( event.what == evNothing )
        return event.message.infoPtr;
    else
        return null;
}

TView.prototype.style = function() {
  return "position:absolute; overflow:hidden; display:"+((this.state & sfVisible)==0? 'none':'block')+"; left:"+this.origin.x+"px; top:"+this.origin.y+"px; width:"+this.size.x+"px;  height:"+this.size.y+"px;"+this._style;
};

TView.prototype.other = function() {
  return "";
};

