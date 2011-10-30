 
var startingPoint = {
x:null,
y:null
}
var offsetPoint = {
x:0,
y:0
}
var dragged = false;
var engaged = false;
var curFocused;
function over(){
$("#mycanvas-canvas").css("cursor","url(grab.cur)");
}
function hideFill(el){

if(el==curFocused)
$('#autosuggest').slideUp(500);
}


function isFocused(el){
curFocused = el.id;
}
function move(evt){
var x = evt.x||evt.clientX;
var y = evt.y||evt.clientY; 
if(startingPoint.x!=null){
st.drag((x-startingPoint.x+offsetPoint.x),(y-startingPoint.y+offsetPoint.y));
dragged = true;
}
}
function down(evt){
var x = evt.x||evt.clientX;
var y = evt.y||evt.clientY; 
startingPoint.x = x;
startingPoint.y = y;
$("#mycanvas-canvas").css("cursor","url(/grabbing.cur),auto");
return false;
}
function up(evt){
var x = evt.x||evt.clientX;
var y = evt.y||evt.clientY; 
offsetPoint.x+=x-startingPoint.x;
offsetPoint.y+=y-startingPoint.y;
startingPoint.x = null;
startingPoint.y = null;
dragged = false;
$("#mycanvas-canvas").css("cursor","url(/grab.cur),auto");
}
function autofill(courseId){
	//find location for the autocomplete box
	var x = $("#in").offset().left;
	var y = $("#in").offset().top+20;
	//$("#autosuggest").offset({left:0,top:0});
	$("#autosuggest").css("left",x+"px");
	$("#autosuggest").css("top",y+"px");
	
	
	if(courseId.length==0){
    $('#autosuggest').animate({ 
     height: 'hide',
     opacity: 'hide'
 }, 'fast');
    }
    else{
	$('#autosuggest').animate({ 
     height: 'show',
     opacity: 'show'
 }, 'fast');
    $.get('/autocomplete.php?text='+courseId.replace(" ","-"), function(data) {
 		$("#autosuggest").html(data);
  
	});
    
    }
}

function select(courseId){
	//console.log("select called");
	courseId = courseId.split("(")[0].trim();
//	console.log(courseId);
	$('#in').attr("value",courseId);
}
function engage(){
//$('#infovis').html("");




	var windowHeight = $(window).height()-65;
	$("#share").fadeOut();

	$("#footer").fadeOut(200);
	$('#autosuggest').slideUp(500,function(){
	
		if(engaged){
			$("#mycanvas").remove();
			start();
		}
		else{
		
		$('#logo').fadeOut();
		$('#texts').fadeOut();
		$('#topframe').animate({height: 65},500,function(){
		$("#smallLogo").fadeIn();
		$('#midframe').animate({height: windowHeight},500,function(){
			$("#share2").fadeIn();
			engaged = true; 
			start();
		});
		
	});
	}
	});
}

function getStyle(className) {
    var classes = document.styleSheets[0].rules || document.styleSheets[0].cssRules;
	var s;
    for(var x=0;x<classes.length;x++) {
        if(classes[x].selectorText==className) {
			
                (classes[x].cssText) ? s=(classes[x].cssText) : s=(classes[x].style.cssText);
				
        }
    }
	bgc = s.substring(s.indexOf("background-color"),s.indexOf(";",s.indexOf("background-color"))).split(":")[1].trim();
	height = s.substring(s.indexOf("height"),s.indexOf(";",s.indexOf("height"))).split(":")[1].trim();
	return bgc+":"+height;
}
function load(course){
$("#mycanvas").remove();
$("#in").val(course);
			start();
}
function start(){
	courseId = $('#in').val().replace(" ","-");
	$.get('/lookup.php?course='+courseId+'&reverse=0',function(data){
	try{
		data = JSON.parse(data);
	}catch(e){}
	
	init(data);
	 st.onClick(st.root);  
	});
	
}
function mouseOver(id){
highlightedName = st.graph.nodes[id].name;
$(":input[innerText*='"+highlightedName+"']").css("color","#FFA600");

}
function mouseOut(id){
$(".node").css("color",labelCol);

}
function draw(){

}
var st;
var jsonString;
function getPosition(who){
    var T= 0,L= 0;
    while(who){
        L+= who.offsetLeft;
        T+= who.offsetTop;
        who= who.offsetParent;
    }
    return [L,T];    
}
function init(json) {  
	$(".links").fadeIn();
      var canvas = new Canvas('mycanvas', {  
         //Where to inject canvas. Any HTML container will do.  
         'injectInto':'infovis',  
         //Set width and height, default's to 200.  
       'height':parseInt($("#infovis").css("height")),
		'width':parseInt($("#infovis").css("width")),
         //Set a background color in case the browser  
         //does not support clearing a specific area.  
        'backgroundColor': '#000000'  
      });  
    //Create a new ST instance  
    st= new ST(canvas, {  
      //Set node and edge colors  
      //Set overridable=true to be able  
      //to override these properties  
      //individually  
	    duration: 500,
		
        //set animation transition type
        transition: Trans.Quart.easeInOut,
	  levelsToShow: 10,
	  constrained:false,
	  levelDistance:25,
	  orientation: "left",
	 multitree:true,
	  align: "center",
      Node: {  
	  type: "roundedrect",
	   height: 30,  
    width: 100, 
       overridable: true,  
       color: nodeCol  
      },  
       Edge: {
            type: 'bezier',
            lineWidth: 2,
            color:lineCol,
            overridable: true
        }, 
    //Add an event handler to the node when creating it.  
        onCreateLabel: function(label, node) {  
            label.id = node.id;  
            label.innerHTML = "<center>"+node.name+"</center>";  
            label.onclick = function(){  
				
                st.onClick(node.id);  
            };  
			var style = label.style;  
                 
        style.cursor = 'pointer';  
        },  
		
        //This method is called right before plotting  
        //a node. It's useful for changing an individual node  
        //style properties before plotting it.  
        //The data properties prefixed with a dollar  
        //sign will override the global node style properties.  
        onBeforePlotNode: function(node) {  
            //add some color to the nodes in the path between the  
            //root node and the selected node.  
			
			
            if (node.selected) {  
				
                node.data.$color = selNodeCol;  
            } else {  
                delete node.data.$color;  
            }  
        },  
  		onAfterCompute: function(){
		
		$(".node").attr("onMouseOver","mouseOver(this.id)");
		$(".node").attr("onMouseOut","mouseOut(this.id)");
		if(!dragged){
		var shortenData = st.clickedNode.data.desc;
		
		if(shorten){
		var data = $("<div>"+shortenData+"</div>");
		shortenData = "<h1>"+data.find("h1").html()+"</h1>";
		var prereqs = data.find("h6");
		for(var i =0;i<prereqs.length;i++){
		shortenData += "<h6>"+prereqs[i].innerHTML+"</h6>";
		}
		}
		
		$("#infobox").html(shortenData);
		$("#infobox").fadeIn();
		
		var x = (document.width?document.width:window.innerWidth)-$("#infobox").width()-20;
		var y = $(window).height()-$("#infobox").height()-20;
		//console.log(x);
		$("#infobox").offset({left:x,top:y});
		offsetPoint.x = st.graph.nodes['node0'].pos.x;
		offsetPoint.y = st.graph.nodes['node0'].pos.y;
		}
		
		}, 
		request: function(nodeId, level, onComplete) {
			
			//alert(count);
			var courseId = st.graph.nodes[nodeId].name.replace(" ","-");
			if(st.graph.nodes[nodeId].data.$orn=='left'){
       		$.get('/lookup.php?course='+courseId+'&reverse=1&nodeCount='+0,function(data){
			//alert(data);
			
			if(typeof data != "string")
			data = JSON.stringify(data);
			addChildren(nodeId,jsonString,JSON.parse(data));
			data = "{\"id\":\""+nodeId+"\",\"children\":"+data+"}";
			
			data = JSON.parse(data);
			//count+=data.children.length;
			 onComplete.onComplete(nodeId, data); 
			});
          }
		  else{
		  	 onComplete.onComplete(nodeId, null); 
		  }
        },
        //This method is called right before plotting  
        //an edge. It's useful for changing an individual edge  
        //style properties before plotting it.  
        //Edge data properties prefixed with a dollar sign will  
        //override the Edge global style properties.  
        onBeforePlotLine: function(adj){  
            if (adj.nodeFrom.selected && adj.nodeTo.selected) {  
                adj.data.$color =selLineCol;  
                adj.data.$lineWidth = 3;  
				
            }  
            else {  
                delete adj.data.$color;  
                delete adj.data.$lineWidth;  
            }  
        }  
    });  
    //load json data  
	jsonString = json;
	st.fx.labels=null;
	st.fx.labels={};
	//console.log(st.fx.labels['node0']);
    st.loadJSON(json);  
    //compute node positions and layout  
    st.compute();  
    //optional: make a translation of the tree  
  
    //Emulate a click on the root node.  
   
	 
} 
function addChildren(id,root,orphans){
for(i in root.children){
if(root.children[i].id==id){
//console.log(root.children[i].id);
root.children[i].children=orphans;
}
if(root.children[i].children.length>0)
addChildren(id,root.children[i],orphans);
}
}
function sendGraphData(onComplete){
var dataPacket = {
json:(jsonString),
clickedNode:st.clickedNode.id,
offset:{
x:st.clickedNode.pos.x,
y:st.clickedNode.pos.y
}
}
dataPacket= JSON.stringify(dataPacket);
dataPacket = lzw_encode(dataPacket);
$.post("/savestate.php",{"data":dataPacket},onComplete
);
}
var highlightedName;
//document.onkeydown = processdown;
document.onkeydown = processKey;
function processdown(e){
	if (null == e)
    e = window.event ;
}
function processKey(e)
{
	//console.log("processKey called");
  if (null == e)
    e = window.event ;
  if (e.keyCode == 13&&(document.activeElement.id=="in"||document.activeElement.id=="autosuggest"))  {
  
	engage();
  }
  if (e.keyCode == 38&&(document.activeElement.id=="in"))  {
  
	if(document.getElementById("autosuggest").selectedIndex>-1){
		document.getElementById("autosuggest").focus();
	//	document.getElementById("autosuggest").selectedIndex--;
		
		select(document.getElementById("autosuggest").value);
	}
	//return false;
  }
  if (e.keyCode == 40&&(document.activeElement.id=="in"))  {
  
	if(document.getElementById("autosuggest").selectedIndex==-1){
		document.getElementById("autosuggest").focus();
		if(navigator.userAgent.indexOf("WebKit")!=-1)
		document.getElementById("autosuggest").selectedIndex=0;
		select(document.getElementById("autosuggest").value);   
  }
  else{
		document.getElementById("autosuggest").focus();
	//	document.getElementById("autosuggest").selectedIndex++;
		select(document.getElementById("autosuggest").value);   
	}
	//return false;
  }
  var curPos;
  if(st!=null){
  curPos = st.graph.nodes['node0'].pos;
  }
  try{
  if((e.keyCode == 87||e.keyCode==73)&&document.activeElement.id!="in"){//w/i
		curPos.y-=10;
		st.drag(curPos.x,curPos.y);
  }
  if((e.keyCode == 65||e.keyCode==74)&&document.activeElement.id!="in"){//a/j		
		curPos.x-=10;
		st.drag(curPos.x,curPos.y);
  }
  if((e.keyCode == 83||e.keyCode==75)&&document.activeElement.id!="in"){//s/k
		curPos.y+=10;
		st.drag(curPos.x,curPos.y);
  }
  if((e.keyCode == 68||e.keyCode==76)&&document.activeElement.id!="in"){//d/l
		curPos.x+=10;
		st.drag(curPos.x,curPos.y);
  }
	}
	catch(e){}
}
function showLink(){
$("#showLink").show();
var courseCode = st.graph.nodes['node0'].name;
$("#link").val("http://"+document.domain+"/courses/"+courseCode.split(" ")[0]+"/"+courseCode.split(" ")[1]);
}
function generateLink(){
$("#message").html("Generating link...");
$("#showLink").show();
sendGraphData(function(hash){
$("#message").html("This link doesn't live forever! It will die in a few months.");
$("#link").val("http://"+document.domain+"/load.html?i="+hash);
});
}