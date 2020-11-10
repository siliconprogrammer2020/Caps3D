<?php
   include_once "TView.php";
?>
TWrapper = function(content) {
  TView.call(this);
  this.content = content;
};
   extend(TWrapper,TView); //  inherit TApplet


TWrapper.prototype.style = function() {
  return "position:absolute; left:"+this.origin.x+"px; top:"+this.origin.y+"px; "+this._style;
};
