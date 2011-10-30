var shorten = false;
var lineCol='#23A4FF';
 var nodeCol='#333333' ;
 var selLineCol= "#111111";
 var selNodeCol="#E32D2D";
 var bgCol="#FFFFFF";
 var labelCol="#FFFFFF";

$(document).ready(function() {

loadSettings();
$("<style type='text/css'> .node { \
    background-color:transparent;  \
    font-weight:bold; \
	font-size:11px;\
    overflow:hidden;  \
	color:"+labelCol+";	\
    position:absolute;  \
    text-align:center;\
	border:hidden;\
	 height:30px;\
	 width:100px;\
	 -moz-focus-inner { border: 0; }\
}  </style>").appendTo("head");
$("#infovis").css("background-color",bgCol);
	$("#btn").val(shorten?"on":"off");
	$("#btn").attr("class",shorten?"on":"off");
	$("#lineColor").val(lineCol);
	$("#nodeColor").val(nodeCol);
	$("#selLineColor").val(selLineCol);
	$("#selNodeColor").val(selNodeCol);
	$("#bgColor").val(bgCol);
	$("#labelColor").val(labelCol);
	for(var i =0;i<$(".colorInput").length;i++){
	$('#colorpicker'+$(".colorInput")[i].id).farbtastic("#"+$(".colorInput")[i].id);
	}
	

stylizeBtn("bt","#444","#00aadd","#222");
if(!skip){
var windowHeight = $(window).height()-50;
$("#topframe").height(windowHeight);
$("#midframe").height(1);

document.getElementById("in").focus();
}
$("#cover").fadeOut();
  });


function stylizeBtn(classname,normal,hover,active){
$("."+classname).hover(function(){
$(this).animate({ backgroundColor: hover }, 250);
},function() {
$(this).animate({ backgroundColor: normal }, 250);
});
$("."+classname).mousedown(function(){
$(this).css("background-color",active);
});
$("."+classname).mouseup(function(){$(this).css("background-color",hover);
});
}
function togglestyle(el){
    if(el.className == "on") {
        el.className="off";
		el.value="off";
    } else {
        el.className="on";
		el.value="on";
    }
}
