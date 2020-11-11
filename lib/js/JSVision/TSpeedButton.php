<?php
   include_once "TView.php";
   include_once "TButton.php";
   include_once "TImage.php";
?>

function TSpeedButton(bounds,image,command,style,other) {
  TImage.call(this,bounds,image,command);
  this._style=(arguments.length >= 4)?style:'';
  this._other=(arguments.length === 5)?other:'';
};

extend(TSpeedButton,TImage); 

TSpeedButton.prototype.style = function() {
  return TImage.prototype.style.call(this)+'cursor:pointer;'+this._style;
};

TSpeedButton.prototype.other = function() {
  return TImage.prototype.other.call(this)+this._other
        +((this.command!='')?' onmouseover="'+SBmouseover+'" onmouseout="'+SBmouseout+'"':'');
};
