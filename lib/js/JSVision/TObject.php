var AppletList = [];
var internalID = 0;

function uniqueID() {
 return ++internalID;
}

function TObject() {
  this.id = uniqueID();
  AppletList[this.id]=this;
};

TObject.prototype.done = function() {
   AppletList[this.id]=null;
}
