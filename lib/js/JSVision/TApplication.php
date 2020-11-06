<?php
 include_once "TProgram.php";
 include_once "TWrapper.php";
 include_once "../lib/js/utils.php";
?>

function TApplication(width,height,overflow_width,overflow_height) { 
   initMemory();

   if (arguments.length === 4) this.initVideo(width,height,overflow_width,overflow_height);
   else this.initVideo(width,height,0,0);

   this.initEvents();
   initSysError();
   initHistory();
   TProgram.call(this,width,height);

   this.insert(new TWrapper("<image id='rotate' src='/image/1x1.png' style='position:absolute; z-index:200;top:"+this.overflow.y+"px; left:"+this.overflow.x+"px' border='0'>")); 

   var self = this;
   if (mobileDevice) {setTimeout(function(){self.updateScreen();},1000);} else self.updateScreen();
};

extend(TApplication,TProgram); //  inherit 

function   initMemory() {};

TApplication.prototype.initVideo = function(width,height,of_x,of_y) {
   this.overflow = new TPoint(of_x,of_y); // often instead of a boxed game, we want a full screen game, the overflow is an extra distance around the game, added to show on the browser in the game world, to make sure the entire browser screen is used without blank space around the game showing.

   this.aspect = width/height;
   this.scaler = 1;

   var self = this;

   // the setTimeout delay on the resize event, give a bit of extra time for the dom dimension data to properly update
   window.addEventListener("orientationchange",function(){ setTimeout(function(){self.updateScreen();},1000);},false);
   window.addEventListener("resize",function(){ 
     if (mobileDevice) {setTimeout(function(){self.updateScreen();},1000);} else self.updateScreen();
   },false);
};

TApplication.prototype.initEvents = function() {
   var self = this;

   window.addEventListener("touchmove",function(ev){ ev.preventDefault();},false);

   window.addEventListener("keydown",function(ev){ 
      var event = new TEvent(ev); 
      event.what=evKeyDown;
      event.keyDown=ev.key; 
      event.keyCode=ev.keyCode;
      self.handleEvent(event);
   },false);
   
};

function   initSysError() {};
function   initHistory() {};

TApplication.prototype.updateScreen = function() { 
   window.scrollTo(0,0); 

   var actual = new TPoint(window.innerWidth,window.innerHeight);// size and actual are used to control screen orientation, and scaling.

   // scale io with no black spaces on top or sides.
   this.scaler=(actual.x/this.size.x)<(actual.y/this.size.y)?(actual.x/this.size.x):(actual.y/this.size.y);

   var real_aspect = actual.x/actual.y;
   // internal = (actual-scaler*(size-2*overflow))/2
   this.internal = actual.sub(this.size.add(this.overflow.mul(2)).mul(this.scaler)).div(2);

   setTransform(document.getElementById('view'+this.id).style,"translate("+this.internal.x+"px,"+this.internal.y+"px) scale("+this.scaler+")");
   setTransformOrigin(document.getElementById('view'+this.id).style,"left top");
   
    window.scrollTo(0,0); 

  if (mobileDevice) { 
    document.getElementById("rotate").src="/image/1x1.png"; 
    document.getElementById("rotate").width=1;
    // if wrong orientation, show rotate device icon
    if ((this.aspect>1) && (real_aspect<1)) { 
       document.getElementById("rotate").src="/image/rotate_side.png";
       document.getElementById("rotate").width=this.size.x;
    } else if ((this.aspect<1) && (real_aspect>1)) {  
       document.getElementById("rotate").src="/image/rotate_up.png";
       document.getElementById("rotate").height=this.size.y;
    }
  }
};
