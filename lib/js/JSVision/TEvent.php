
const evNothing   = 0x0000,
      evMouseDown = 0x0001,
      evMouseUp   = 0x0002,
      evMouseMove = 0x0004,
      evMouseAuto = 0x0008,
      evMouse     = 0x000F,
      evKeyDown   = 0x0010,
      evKeyboard  = 0x0010,
      evCommand   = 0x0100,
      evBroadcast = 0x0200,
      evMessage   = 0xFF00;

function TEvent(ev) {
  this.what = evNothing;
  this.event;
  this.message = {
    command:null,
    infoPtr:null
  }
  if (arguments.length === 1) { 
   this.event = ev;
  };
};

var pending = new TEvent();

TEvent.prototype.getMouseEvent = function( event ) {
 // NOTE: GET MOUSE EVENT FROM EVENT QUEUE
};

TEvent.prototype.getKeyEvent = function( event ) {
 // NOTE: GET KEY EVENT FROM EVENT QUEUE
};
