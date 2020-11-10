<?php
   include_once "TView.php";
?>

var SBmouseover ="this.style.WebkitFilter=\'invert(25%)\';";
var SBmouseout ="this.style.WebkitFilter=\'invert(0%)\';";

if (/firefox/i.test(navigator.userAgent)) {
  SBmouseover ="this.style.filter=\'invert(25%)\';";
  SBmouseout ="this.style.filter=\'invert(0%)\';";
} else if ((/trident/i.test(navigator.userAgent)) ||  (/msie/i.test(navigator.userAgent))){
  SBmouseover ="this.style.opacity='0.5';";
  SBmouseout ="this.style.opacity='1.0';";
};

function TButton(bounds, aTitle,aCommand,aFlags) { 
  TView.call(this,bounds);
  this.content=aTitle;
  this.command = aCommand.replace(/"/g,"\'");
  this.flags =aFlags;
};

extend(TButton,TView); 


TButton.prototype.other = function() { 
    return ' onmouseover="'+SBmouseover+'" onmouseout="'+SBmouseout+'" onclick="'+this.command+'"'
};

TButton.prototype.style = function() { 
  return TView.prototype.style.call(this)+'font-family:'+document.body.style.fontFamily+'; white-space:nowrap; color:white; background-color:#34abeb; box-shadow: 5px 5px 5px #000000;border-radius: 10px; border:1px solid black; text-align:center; vertical-align: middle; cursor:pointer;';
};
