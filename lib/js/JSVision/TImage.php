<?php
   include_once "TView.php";
?>

function TImage(bounds,image,command) { 
  if (typeof bounds.a !== 'undefined') {
     TView.call(this,bounds);
  } else {
     TView.call(this);
     this.origin = bounds;
  }
  this.image = image;
  this.command=(arguments.length === 3)?command.replace(/"/g,"\'"):'';
};

extend(TImage,TView); 

TImage.prototype.draw = function() { // draw complete view
  var html = '<img style="'+this.style()+'" id="view'+this.id+'" src="'+this.image+'" '+this.other()+'>';
  
  var temp=document.getElementById("view"+this.owner.id);
  if (temp!=null) temp.innerHTML+=html;

  return html;
};

TImage.prototype.style = function() { 
  return 'position:absolute; top:'+this.origin.y+'px; left:'+this.origin.x+'px;';
};

TImage.prototype.other = function() { 
  return ((this.size.x!=0)?'width="'+this.size.x+'" height="'+this.size.y+'"':'')
        +((this.command!='')?' onclick="'+this.command+'"':'')
};
