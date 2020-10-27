<?php
  include_once "TPoint.php";
?>

function TRect(ax,ay,bx,by) {
   this.a = new TPoint();
   this.b = new TPoint();
   
   if (arguments.length === 1) {
     this.assign(ax);
   } else if (arguments.length === 2) {
     this.assign(ax,ay);
   } else if (arguments.length === 4) {
     this.assign(ax,ay,bx,by);
   }
};

TRect.prototype.assign = function(xa,ya,xb,yb) {
 if (arguments.length === 1) {
   this.a.assign(xa.a);
   this.b.assign(xa.b);
 } else if (arguments.length === 2) {
   this.a.assign(xa);
   this.b.assign(ya);
 } else {
   this.a.assign(xa,ya);
   this.b.assign(xb,yb);
 }
};

TRect.prototype.copy = TRect.prototype.assign;

TRect.prototype.move = function( aDX, aDY ) {
   if (arguments.length === 1) {
     var p = new TPoint(aDX);
   } else {
     var p = new TPoint(aDX,aDY);
   };

    this.a = this.a.add(p);
    this.b = this.b.add(p);
    return this;
}

TRect.prototype.grow = function( aDX, aDY ) {
   if (arguments.length === 1) {
     var p = new TPoint(aDX);
   } else {
     var p = new TPoint(aDX,aDY);
   };

    this.a = this.a.sub(p);
    this.b = this.b.add(p);
    return this;
}

TRect.prototype.intersect = function( r ) {
    this.a.x = Math.max( this.a.x, r.a.x );
    this.a.y = Math.max( this.a.y, r.a.y );
    this.b.x = Math.min( this.b.x, r.b.x );
    this.b.y = Math.min( this.b.y, r.b.y );
}

TRect.prototype.union = function( r ) {
    this.a.x = Math.min( this.a.x, r.a.x );
    this.a.y = Math.min( this.a.y, r.a.y );
    this.b.x = Math.max( this.b.x, r.b.x );
    this.b.y = Math.max( this.b.y, r.b.y );
}

TRect.prototype.contains = function( p ) {
    return (p.x >= this.a.x) && (p.x < this.b.x) && (p.y >= this.a.y) && (p.y < this.b.y);
}

TRect.prototype.equals = function(r) {
    return ( this.a.x == r.a.x) && (this.a.y==r.a.y) && (this.b.x == r.b.x ) && (this.b.y ==r.b.y);
};


TRect.prototype.isEmpty = function() {
    return ( this.a.x >= this.b.x) || (this.a.y >= this.b.y );
};
