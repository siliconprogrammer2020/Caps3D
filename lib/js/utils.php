function reload() {
  window.location.href="index.php";
};

function fileExists(url) {
  var http = new XMLHttpRequest();
  http.open('HEAD',url,false);
  http.send();
  return http.status!=404;
};

function include(file) {
   var http = new XMLHttpRequest();
   http.open('GET',file,false);
   http.send();
   return http.responseText;
};

var mobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
var isAndroid = (navigator.userAgent.indexOf('Android') != -1);
var  isTablet = (navigator.userAgent.indexOf('iPad') != -1);
var isiPhone = /iPhone/i.test(navigator.userAgent);

function is_touch_device() {
 return (('ontouchstart' in window) || (navigator.MaxTouchPoints>0) || (navigator.msMaxTouchPoints>0));
};

var css_prefix ='';
if (document.body.style.webkitTransform!=undefined) {
      css_prefix="-webkit-";
      setTransform = function(obj,ani) {
        obj.webkitTransform=ani;
      };
      setTransformOrigin = function(obj,ani) {
        obj.webkitTransformOrigin=ani;
      };
   } else {
      setTransform = function(obj,ani) {
        obj.transform=ani;
      };
      setTransformOrigin = function(obj,ani) {
        obj.transformOrigin=ani;
      };
};

function createArray(length) {
  var arr = new Array(length || 0), i= length;

  if (arguments.length>1) {
   var args = Array.prototype.slice.call(arguments,1);
   while(i--) arr[length-1-i]=createArray.apply(this,args);
  }

  return arr;
};
