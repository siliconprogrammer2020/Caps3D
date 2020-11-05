<?php
 include_once "../lib/js/JSVision/TView.php";
?>

var phFocused = 1;

function TGroup(bounds) {
  TView.call(this,bounds);
  this.current=null;
  this.last = null;
  this.phase = phFocused;
  this.lockFlag = 0;
  this.endState = 0;
  this.options |= ofSelectable | ofBuffered;
  this.getExtent(this.clip = new TRect());
  this.eventMask = 0xFFFF;

    this.list=[]; 
};

extend(TGroup,TView); //  inherit

TGroup.prototype.changeBounds = function( bounds ) {
    var d = new TPoint();

    d.x = (bounds.b.x - bounds.a.x) - size.x;
    d.y = (bounds.b.y - bounds.a.y) - size.y;
    if( d.x == 0 && d.y == 0 )
        {
        setBounds(bounds);
        drawView();
        }
    else
        {
        freeBuffer();
        setBounds( bounds );
        clip = getExtent();
        getBuffer();
        lock();
        forEach( doCalcChange, d );
        unlock();
        }
};

TGroup.prototype.remove = function(p) { // same as delete(p)
    var saveState = p.state;
    p.hide();
    removeView(p);
    p.owner = null;
    p.next= null;
    if( (saveState & sfVisible) != 0 )
        p.show();
};

TGroup.prototype.draw = function() { // draw complete view
//   var html = '';
//   this.list.forEach(function(item) {
//    html+=item.draw();
//   });
//  return TView.prototype.draw.call(this,html);

   TView.prototype.draw.call(this);
   this.list.forEach(function(item) {
    item.draw();
   });

// REPLACE ABOVE CODE WITH THIS BOTTOM CODE
//      this.getClipRect(this.clip);
//      this.redraw();
//      this.getExtent(this.clip);

};

TGroup.prototype.drawSubViews = function( p, bottom ) {
    while( p != bottom )
        {
        p.drawView();
        p = p.nextView();
        }
};

TGroup.prototype.endModal = function( command ) {
    if( (state & sfModal) != 0 )
        endState = command;
    else
        TGroup.prototype.endModal.call(this, command );
};

TGroup.prototype.eventError = function(event ){
    if (owner != 0 )
        owner.eventError( event );
};

TGroup.prototype.execute = function() {
    do  {
        endState = 0;
        do  {
            var e = new TEvent();
            this.getEvent( e );
            this.handleEvent( e );
            if( e.what != evNothing )
                eventError( e );
            } while( endState == 0 );
    } while( !valid(endState) );
    return endState;
};

TGroup.prototype.execView = function( p ) {
    if( p == null )
        return cmCancel;

    var saveOptions = p.options;
    var saveOwner = p.owner;
    var saveTopView = TheTopView;
    var saveCurrent= current;
    var saveCommands = [];
    getCommands( saveCommands );
    TheTopView = p;
    p.options = p.options & ~ofSelectable;
    p.setState(sfModal, true);
    setCurrent(p, enterSelect);
    if( saveOwner == 0 )
        insert(p);

    // Just be foolproof
    var oldLock=lockFlag;
    if (lockFlag)
      {
       lockFlag=1; unlock();
      }

    var retval = p.execute();
    p.setState(sfActive, false);

    // Re-lock if needed
    lockFlag=oldLock;

    if( saveOwner == 0 )
        remove(p);
    setCurrent(saveCurrent, leaveSelect);
    p.setState(sfModal, false);
    p.options = saveOptions;
    TheTopView = saveTopView;
    setCommands(saveCommands);
    return retval;
};

TGroup.prototype.first = function() {
    if( this.last == null )
        return null;
    else
        return this.last.next;
};

TGroup.prototype.firstMatch = function( aState, aOptions ) {
    if( this.last == null )
        return null;

    var temp = this.last;
    while(1)
        {
        if( ((temp.state & aState) == aState) && 
            ((temp.options & aOptions) ==  aOptions))
            return temp;

        temp = temp.next;
        if( temp == this.last )
            return null;
        }
};

TGroup.prototype.firstThat = function( func, args ) {
    //var temp = last;
//    if( temp == null )
//        return null;

   this.list.forEach(function(temp) {
     if( func( temp, args ) == true )
        return temp;   
   });
//    do  {
//        temp = temp.next;
//        if( func( temp, args ) == true )
//            return temp;
//        } while( temp != last );
    return null;
};

TGroup.prototype.forEach = function( func, args ) {
    var term = this.last;
    var temp = this.last;
    if( temp == null )
        return;

    var next = temp.next;
    do  {
        temp = next;
        next = temp.next;
        func( temp, args );
        } while( temp != term );

};

TGroup.prototype.getHelpCtx = function() {
    var h = hcNoContext;
    if( current!= null )
        h = current.getHelpCtx();
    if (h == hcNoContext)
        h = TView.prototype.getHelpCtx.call(this);
    return h;
};

function handleStruct(e,g) {
    this.event=e;
    this.grp=g;
};

function doHandleEvent( p, s ) {
    var ptr = s;

    if( (p == null) ||
        ( ((p.state & sfDisabled) != 0) &&
          ((ptr.event.what & (positionalEvents | focusedEvents)) != 0)
        )
      )
        return;

    switch( ptr.grp.phase )
        {
        case phPreProcess:
            if( (p.options & ofPreProcess) == 0 )
                return;
            break;
        case phPostProcess:
            if( (p.options & ofPostProcess) == 0 )
                return;
            break;
        default:
            break;
        }
    if( (ptr.event.what & p.eventMask) != 0 )
        p.handleEvent( ptr.event );
};

TGroup.prototype.handleEvent = function(event) { 
//   this.list.forEach(function(item) {
//      item.handleEvent(event);
//   });


    TView.prototype.handleEvent.call(this, event );

    var hs = new handleStruct( event, this );
    
    if( (event.what & focusedEvents) != 0 )
        {
        phase = phPreProcess;
        this.forEach( doHandleEvent, hs );

        phase = phFocused;
        doHandleEvent( current, hs );

        phase = phPostProcess;
        this.forEach( doHandleEvent, hs );
        }
    else
        {
        phase = phFocused;
        if( (event.what & positionalEvents) != 0 )
            {
            doHandleEvent( firstThat( hasMouse, event ), hs );
            }
        else
            this.forEach( doHandleEvent, hs );
        }
};

TGroup.prototype.insert = function( p ) {
    //this.insertBefore( p, this.first() );
   if (p==null) return;
   this.list.push(p); 
   p.owner=this;
   p.draw();
};

TGroup.prototype.insertBefore = function( p, Target ) { console.log('insertBefore: '+this.id);
console.log(p.owner==null);
console.log(( (p != null) && (p.owner == null) && ((Target == null) || (Target.owner == this)) ));
    if( (p != null) && (p.owner == null) && ((Target == null) || (Target.owner == this)) )
        { console.log('sergio');
        if( (p.options & ofCenterX) != 0 )
            p.origin.x = (size.x - p.size.x)/2;
        if( (p.options & ofCenterY) != 0 )
            p.origin.y = (size.y - p.size.y)/2;
        var saveState = p.state;
        p.hide();
        this.insertView( p, Target );
        if( (saveState & sfVisible) != 0 )
            p.show();
        }
};

TGroup.prototype.insertView = function( p, Target ) { console.log('insertView: '+this.id);
    p.owner = this;
    if( Target != null )
        {
        Target = Target.prev();
        p.next = Target.next;
        Target.next= p;
        }
    else
        {
        if( this.last== null )
            p.next = p;
        else
            {
            p.next = this.last.next;
            this.last.next = p;
            }
        this.last = p; console.log('InsertLast: '+p.id);
        }
};

TGroup.prototype.lock = function() {
    if( this.lockFlag != 0 )
        this.lockFlag++;
};

TGroup.prototype.redraw = function() {
    this.drawSubViews( this.first(), null );
};

TGroup.prototype.resetCurrent = function() {
    this.setCurrent( this.firstMatch( sfVisible, ofSelectable ), normalSelect );
};

TGroup.prototype.selectNext = function( forwards ) {
    if( current != null )
        {
        var p = current;
        do  {
            if (forwards)
                p = p.next;
            else
                p = p.prev();
            } while ( !(
              (((p.state & (sfVisible + sfDisabled)) == sfVisible) &&
              (p.options & ofSelectable)) || (p == current)
              ) );
        p.select();
        }
};

TGroup.prototype.focusView = function( p, enable ) {
    if(((this.state & sfFocused) != 0) && (p != null) )
        p.setState( sfFocused, enable );
};

TGroup.prototype.setCurrent = function( p, mode ) {
    if (this.current!= p)
        {
        this.lock();
        this.focusView( this.current, false );
        // Test if focus lost was allowed and focus has really been loose
        if ( (mode == normalSelect) &&
             this.current &&
             (this.current.state & sfFocused)
           )
           {
            this.unlock(); 
            return; 
           }
        if( mode != enterSelect )
            if( this.current != null )
                this.current.setState( sfSelected, false );
        if( mode != leaveSelect )
            if( p != null )
                p.setState( sfSelected, true );
        this.focusView( p, true );
        this.current = p;
        this.unlock();
        }
};

function doExpose( p, enable ) {
    if( (p.state & sfVisible) != 0 )
        p.setState( sfExposed,enable );
};

function setBlock() {
    this.st;
    this.en;
};

TGroup.prototype.setState = function( aState, enable ) {
    var sb = new setBlock();
    sb.st = aState;
    sb.en = enable;

    TView.prototype.setState.call(this, aState, enable );

    if( (aState & (sfActive | sfDragging)) != 0 )
        { 
        lock();
        forEach( doSetState, sb );
        unlock();
        }

    if( (aState & sfFocused) != 0 )
        {
        if( this.current != null )
            this.current.setState( sfFocused, enable );
        }

    if( (aState & sfExposed) != 0 )
        {
          this.forEach( doExpose, enable );
        }
};

TGroup.prototype.unlock = function() {
    if( (this.lockFlag != 0) && (--this.lockFlag == 0) )
       {
        this.drawView();
        // SET: Now is time to hide/show mouse according to
        // changes while we were locked.
        this.resetCursor();
       }
};

function isInvalid( p, commandP) {
    return !p.valid( commandP );
};

TGroup.prototype.valid = function( command ) {
    return this.firstThat( isInvalid, command ) == null ;
};

// SET: TViews will ask us if that's good time to draw cursor changes
TGroup.prototype.canShowCursor = function() {
 return this.lockFlag ? false : true;
};

TGroup.prototype.push = function( list ) {
  var self=this;
  list.forEach(function(item) { self.list.push(item); item.owner=self;});
};

TGroup.prototype.unshift = function( list ) {
  list.reverse();
  var self=this;
  list.forEach(function(item) { self.list.unshift(item); item.owner=self;});
};
