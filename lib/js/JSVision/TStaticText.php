<?php
   include_once "TView.php";
?>

function TStaticText(bounds,text) {  
  TView.call(this,bounds); 
  this.content = text;
};

extend(TStaticText,TView); //  inherit TApplet


TStaticText.prototype.style = function() {
  return TView.prototype.style.call(this)+' font-family:'+document.body.style.fontFamily+'; color:white; text-shadow: 2px 2px black;';
};
