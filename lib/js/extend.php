
<!-- saved from url=(0054)http://sergiofernandez.sytes.net/dev/lib/js/extend.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>function extend(Child,Parent) {
  var F = function() {};
  F.prototype = Parent.prototype;
  Child.prototype = new F();
  Child.prototype.constructor = Child;
};

</body></html>