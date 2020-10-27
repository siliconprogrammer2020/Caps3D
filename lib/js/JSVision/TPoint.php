function TPoint(x,y) {
   this.x=0;
   this.y=0;

   if (arguments.length === 1 ) { 
     this.assign(x);
   } else if (arguments.length === 2) {
     this.assign(x,y);
   };
}

TPoint.prototype.assign = function(x,y) {
  if (arguments.length === 1) {
    this.x=x.x;
    this.y=x.y;
  } else {
     this.x=x;
     this.y=y;
  }
};

TPoint.prototype.sub = function(x,y) {
   if (arguments.length === 1) {
       return new TPoint(this.x-x.x,this.y-x.y);
   } else {
       return new TPoint(this.x-x,this.y-y);
   }
};

TPoint.prototype.add = function(x,y) {
   if (arguments.length === 1) {
       return new TPoint(this.x+x.x,this.y+x.y);
   } else {
       return new TPoint(this.x+x,this.y+y);
   }
};

TPoint.prototype.mul = function(c) {
   return new TPoint(c*this.x,c*this.y);
};

TPoint.prototype.equals = function(p) {
    return (this.x == p.x) && (this.y == p.y);
};
