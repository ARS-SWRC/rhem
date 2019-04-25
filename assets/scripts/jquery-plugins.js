<!--  DOMWINDOW --->
(function($){
	//closeDOMWindow
	$.fn.closeDOMWindow = function(settings){
		
		if(!settings){settings={};}
		
		var run = function(passingThis){
			
			if(settings.anchoredClassName){
				var $anchorClassName = $('.'+settings.anchoredClassName);
				$anchorClassName.fadeOut('fast',function(){
					// Change by Gerardo: changed $.fn.draggable to settings.draggable in order for the 
					// window to be removed when closed
					if(settings.draggable){
						$anchorClassName.draggable('destory').trigger("unload").remove();	
					}else{
						$anchorClassName.trigger("unload").remove();
					}
				});
				if(settings.functionCallOnClose){settings.functionCallAfterClose();}
			}else{
				var $DOMWindowOverlay = $('#DOMWindowOverlay');
				var $DOMWindow = $('#DOMWindow');
				$DOMWindowOverlay.fadeOut('fast',function(){
					$DOMWindowOverlay.trigger('unload').unbind().remove();																	  
				});
				$DOMWindow.fadeOut('fast',function(){
					// Change by Gerardo: changed $.fn.draggable to settings.draggable in order for the 
					// window to be removed when closed
					if(settings.draggable){
						$DOMWindow.draggable("destroy").trigger("unload").remove();
					}else{
						$DOMWindow.trigger("unload").remove();
					}
				});
			
				$(window).unbind('scroll.DOMWindow');
				$(window).unbind('resize.DOMWindow');
				
				if($.fn.openDOMWindow.isIE6){$('#DOMWindowIE6FixIframe').remove();}
				if(settings.functionCallOnClose){settings.functionCallAfterClose();}
			}	
		};
		
		if(settings.eventType){//if used with $().
			return this.each(function(index){
				$(this).bind(settings.eventType, function(){
					run(this);
					return false;
				});
			});
		}else{//else called as $.function
			run();
		}
		
	};
	
	//allow for public call, pass settings
	$.closeDOMWindow = function(s){$.fn.closeDOMWindow(s);};
	
	//openDOMWindow
	$.fn.openDOMWindow = function(instanceSettings){	
		
		var shortcut =  $.fn.openDOMWindow;
	
		//default settings combined with callerSettings////////////////////////////////////////////////////////////////////////
		
		shortcut.defaultsSettings = {
			anchoredClassName:'',
			anchoredSelector:'',
			borderColor:'#ccc',
			borderSize:'4',
			draggable:0,
			eventType:null, //click, blur, change, dblclick, error, focus, load, mousedown, mouseout, mouseup etc...
			fixedWindowY:100,
			functionCallOnOpen:null,
			functionCallOnClose:null,
			height:500,
			loader:0,
			loaderHeight:0,
			loaderImagePath:'',
			loaderWidth:0,
			modal:0,
			overlay:1,
			overlayColor:'#000',
			overlayOpacity:'85',
			positionLeft:0,
			positionTop:0,
			positionType:'centered', // centered, anchored, absolute, fixed
			width:500, 
			windowBGColor:'#fff',
			windowBGImage:null, // http path
			windowHTTPType:'get',
			windowPadding:10,
			windowSource:'inline', //inline, ajax, iframe
			windowSourceID:'',
			windowSourceURL:'',
			windowSourceAttrURL:'href'
		};
		
		var settings = $.extend({}, $.fn.openDOMWindow.defaultsSettings , instanceSettings || {});
		
		//Public functions
		
		shortcut.viewPortHeight = function(){ return self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;};
		shortcut.viewPortWidth = function(){ return self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;};
		shortcut.scrollOffsetHeight = function(){ return self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;};
		shortcut.scrollOffsetWidth = function(){ return self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;};
		shortcut.isIE6 = typeof document.body.style.maxHeight === "undefined";
		
		//Private Functions/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		var sizeOverlay = function(){
			var $DOMWindowOverlay = $('#DOMWindowOverlay');
			if(shortcut.isIE6){//if IE 6
				var overlayViewportHeight = document.documentElement.offsetHeight + document.documentElement.scrollTop - 4;
				var overlayViewportWidth = document.documentElement.offsetWidth - 21;
				$DOMWindowOverlay.css({'height':overlayViewportHeight +'px','width':overlayViewportWidth+'px'});
			}else{//else Firefox, safari, opera, IE 7+
				$DOMWindowOverlay.css({'height':'100%','width':'100%','position':'fixed'});
			}	
		};
		
		var sizeIE6Iframe = function(){
			var overlayViewportHeight = document.documentElement.offsetHeight + document.documentElement.scrollTop - 4;
			var overlayViewportWidth = document.documentElement.offsetWidth - 21;
			$('#DOMWindowIE6FixIframe').css({'height':overlayViewportHeight +'px','width':overlayViewportWidth+'px'});
		};
		
		var centerDOMWindow = function() {
			var $DOMWindow = $('#DOMWindow');
			if(settings.height + 50 > shortcut.viewPortHeight()){//added 50 to be safe
				$DOMWindow.css('left',Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindow.outerWidth())/2));
			}else{
				$DOMWindow.css('left',Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindow.outerWidth())/2));
				$DOMWindow.css('top',Math.round(shortcut.viewPortHeight()/2) + shortcut.scrollOffsetHeight() - Math.round(($DOMWindow.outerHeight())/2));
			}
		};
		
		var centerLoader = function() {
			var $DOMWindowLoader = $('#DOMWindowLoader');
			if(shortcut.isIE6){//if IE 6
				$DOMWindowLoader.css({'left':Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindowLoader.innerWidth())/2),'position':'absolute'});
				$DOMWindowLoader.css({'top':Math.round(shortcut.viewPortHeight()/2) + shortcut.scrollOffsetHeight() - Math.round(($DOMWindowLoader.innerHeight())/2),'position':'absolute'});
			}else{
				$DOMWindowLoader.css({'left':'50%','top':'50%','position':'fixed'});
			}
			
		};
		
		var fixedDOMWindow = function(){
			var $DOMWindow = $('#DOMWindow');
			$DOMWindow.css('left', settings.positionLeft + shortcut.scrollOffsetWidth());
			$DOMWindow.css('top', + settings.positionTop + shortcut.scrollOffsetHeight());
		};
		
		var showDOMWindow = function(instance){
			if(arguments[0]){
				$('.'+instance+' #DOMWindowLoader').remove();
				$('.'+instance+' #DOMWindowContent').fadeIn('fast',function(){if(settings.functionCallOnOpen){settings.functionCallOnOpen();}});
				$('.'+instance+ '.closeDOMWindow').click(function(){
					$.closeDOMWindow();	
					return false;
				});
			}else{
				$('#DOMWindowLoader').remove();
				$('#DOMWindow').fadeIn('fast',function(){if(settings.functionCallOnOpen){settings.functionCallOnOpen();}});
				$('#DOMWindow .closeDOMWindow').click(function(){						
					$.closeDOMWindow();
					return false;
				});
			}
			
		};
		
		var urlQueryToObject = function(s){
			  var query = {};
			  s.replace(/b([^&=]*)=([^&=]*)b/g, function (m, a, d) {
				if (typeof query[a] != 'undefined') {
				  query[a] += ',' + d;
				} else {
				  query[a] = d;
				}
			  });
			  return query;
		};
			
		//Run Routine ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		var run = function(passingThis){
			
			//get values from element clicked, or assume its passed as an option
			settings.windowSourceID = $(passingThis).attr('href') || settings.windowSourceID;
			settings.windowSourceURL = $(passingThis).attr(settings.windowSourceAttrURL) || settings.windowSourceURL;
			settings.windowBGImage = settings.windowBGImage ? 'background-image:url('+settings.windowBGImage+')' : '';
			var urlOnly, urlQueryObject;
			
			if(settings.positionType == 'anchored'){//anchored DOM window
				
				var anchoredPositions = $(settings.anchoredSelector).position();
				var anchoredPositionX = anchoredPositions.left + settings.positionLeft;
				var anchoredPositionY = anchoredPositions.top + settings.positionTop;
				
				$('body').append('<div class="'+settings.anchoredClassName+'" style="'+settings.windowBGImage+';background-repeat:no-repeat;padding:'+settings.windowPadding+'px;overflow:auto;position:absolute;top:'+anchoredPositionY+'px;left:'+anchoredPositionX+'px;height:'+settings.height+'px;width:'+settings.width+'px;background-color:'+settings.windowBGColor+';border:'+settings.borderSize+'px solid '+settings.borderColor+';z-index:10001"><div id="DOMWindowContent" style="display:none"></div></div>');		
				//loader
				if(settings.loader && settings.loaderImagePath !== ''){
					$('.'+settings.anchoredClassName).append('<div id="DOMWindowLoader" style="width:'+settings.loaderWidth+'px;height:'+settings.loaderHeight+'px;"><img src="'+settings.loaderImagePath+'" /></div>');
					
				}

				if($.fn.draggable){
					if(settings.draggable){$('.' + settings.anchoredClassName).draggable({cursor:'move'});}
				}
				
				switch(settings.windowSource){
					case 'inline'://////////////////////////////// inline //////////////////////////////////////////
						$('.' + settings.anchoredClassName+" #DOMWindowContent").append($(settings.windowSourceID).children());
						$('.' + settings.anchoredClassName).unload(function(){// move elements back when you're finished
							$('.' + settings.windowSourceID).append( $('.' + settings.anchoredClassName+" #DOMWindowContent").children());				
						});
						showDOMWindow(settings.anchoredClassName);
					break;
					case 'iframe'://////////////////////////////// iframe //////////////////////////////////////////
						$('.' + settings.anchoredClassName+" #DOMWindowContent").append('<iframe frameborder="0" hspace="0" wspace="0" src="'+settings.windowSourceURL+'" name="DOMWindowIframe'+Math.round(Math.random()*1000)+'" style="width:100%;height:100%;border:none;background-color:#fff;" class="'+settings.anchoredClassName+'Iframe" ></iframe>');
						$('.'+settings.anchoredClassName+'Iframe').load(showDOMWindow(settings.anchoredClassName));
					break;
					case 'ajax'://////////////////////////////// ajax //////////////////////////////////////////	
						if(settings.windowHTTPType == 'post'){
							
							if(settings.windowSourceURL.indexOf("?") !== -1){//has a query string
								urlOnly = settings.windowSourceURL.substr(0, settings.windowSourceURL.indexOf("?"));
								urlQueryObject = urlQueryToObject(settings.windowSourceURL);
							}else{
								urlOnly = settings.windowSourceURL;
								urlQueryObject = {};
							}
							$('.' + settings.anchoredClassName+" #DOMWindowContent").load(urlOnly,urlQueryObject,function(){
								showDOMWindow(settings.anchoredClassName);
							});
						}else{
							if(settings.windowSourceURL.indexOf("?") == -1){ //no query string, so add one
								settings.windowSourceURL += '?';
							}
							$('.' + settings.anchoredClassName+" #DOMWindowContent").load(
								settings.windowSourceURL + '&random=' + (new Date().getTime()),function(){
								showDOMWindow(settings.anchoredClassName);
							});
						}
					break;
				}
				
			}else{//centered, fixed, absolute DOM window
				
				//overlay & modal
				if(settings.overlay){
					$('body').append('<div id="DOMWindowOverlay" style="z-index:10000;display:none;position:absolute;top:0;left:0;background-color:'+settings.overlayColor+';filter:alpha(opacity='+settings.overlayOpacity+');-moz-opacity: 0.'+settings.overlayOpacity+';opacity: 0.'+settings.overlayOpacity+';"></div>');
					if(shortcut.isIE6){//if IE 6
						$('body').append('<iframe id="DOMWindowIE6FixIframe"  src="blank.html"  style="width:100%;height:100%;z-index:9999;position:absolute;top:0;left:0;filter:alpha(opacity=0);"></iframe>');
						sizeIE6Iframe();
					}
					sizeOverlay();
					var $DOMWindowOverlay = $('#DOMWindowOverlay');
					$DOMWindowOverlay.fadeIn('fast');
					if(!settings.modal){$DOMWindowOverlay.click(function(){$.closeDOMWindow();});}
				}
				
				//loader
				if(settings.loader && settings.loaderImagePath !== ''){
					$('body').append('<div id="DOMWindowLoader" style="z-index:10002;width:'+settings.loaderWidth+'px;height:'+settings.loaderHeight+'px;"><img src="'+settings.loaderImagePath+'" /></div>');
					centerLoader();
				}

				//add DOMwindow
				$('body').append('<div id="DOMWindow" style="background-repeat:no-repeat;'+settings.windowBGImage+';overflow:auto;padding:'+settings.windowPadding+'px;display:none;height:'+settings.height+'px;width:'+settings.width+'px;background-color:'+settings.windowBGColor+';border:'+settings.borderSize+'px solid '+settings.borderColor+'; position:absolute;z-index:10001"></div>');
				
				var $DOMWindow = $('#DOMWindow');
				//centered, absolute, or fixed
				switch(settings.positionType){
					case 'centered':
						centerDOMWindow();
						if(settings.height + 50 > shortcut.viewPortHeight()){//added 50 to be safe
							$DOMWindow.css('top', (settings.fixedWindowY + shortcut.scrollOffsetHeight()) + 'px');
						}
					break;
					case 'absolute':
						$DOMWindow.css({'top':(settings.positionTop+shortcut.scrollOffsetHeight())+'px','left':(settings.positionLeft+shortcut.scrollOffsetWidth())+'px'});
						if($.fn.draggable){
							if(settings.draggable){$DOMWindow.draggable({cursor:'move'});}
						}
					break;
					case 'fixed':
						fixedDOMWindow();
					break;
					case 'anchoredSingleWindow':
						var anchoredPositions = $(settings.anchoredSelector).position();
						var anchoredPositionX = anchoredPositions.left + settings.positionLeft;
						var anchoredPositionY = anchoredPositions.top + settings.positionTop;
						$DOMWindow.css({'top':anchoredPositionY + 'px','left':anchoredPositionX+'px'});
								
					break;
				}
				
				$(window).bind('scroll.DOMWindow',function(){
					if(settings.overlay){sizeOverlay();}
					if(shortcut.isIE6){sizeIE6Iframe();}
					if(settings.positionType == 'centered'){centerDOMWindow();}
					if(settings.positionType == 'fixed'){fixedDOMWindow();}
				});

				$(window).bind('resize.DOMWindow',function(){
					if(shortcut.isIE6){sizeIE6Iframe();}
					if(settings.overlay){sizeOverlay();}
					if(settings.positionType == 'centered'){centerDOMWindow();}
				});
				
				switch(settings.windowSource){
					case 'inline'://////////////////////////////// inline //////////////////////////////////////////
						$DOMWindow.append($(settings.windowSourceID).children());
						$DOMWindow.unload(function(){// move elements back when you're finished
							$(settings.windowSourceID).append($DOMWindow.children());				
						});
						showDOMWindow();
					break;
					case 'iframe'://////////////////////////////// iframe //////////////////////////////////////////
						$DOMWindow.append('<iframe frameborder="0" hspace="0" wspace="0" src="'+settings.windowSourceURL+'" name="DOMWindowIframe'+Math.round(Math.random()*1000)+'" style="width:100%;height:100%;border:none;background-color:#fff;" id="DOMWindowIframe" ></iframe>');
						$('#DOMWindowIframe').load(showDOMWindow());
					break;
					case 'ajax'://////////////////////////////// ajax //////////////////////////////////////////
						if(settings.windowHTTPType == 'post'){
							
							if(settings.windowSourceURL.indexOf("?") !== -1){//has a query string
								urlOnly = settings.windowSourceURL.substr(0, settings.windowSourceURL.indexOf("?"));
								urlQueryObject = urlQueryToObject(settings.windowSourceURL);
							}else{
								urlOnly = settings.windowSourceURL;
								urlQueryObject = {};
							}
							$DOMWindow.load(urlOnly,urlQueryObject,function(){
								showDOMWindow();
							});
						}else{
							if(settings.windowSourceURL.indexOf("?") == -1){ //no query string, so add one
								settings.windowSourceURL += '?';
							}
							$DOMWindow.load(
								settings.windowSourceURL + '&random=' + (new Date().getTime()),function(){
								showDOMWindow();
							});
						}
					break;
				}
				
			}//end if anchored, or absolute, fixed, centered
			
		};//end run()
		
		if(settings.eventType){//if used with $().
			return this.each(function(index){				  
				$(this).bind(settings.eventType,function(){
					run(this);
					return false;
				});
			});	
		}else{//else called as $.function
			run();
		}
		
	};//end function openDOMWindow
	
	//allow for public call, pass settings
	$.openDOMWindow = function(s){$.fn.openDOMWindow(s);};
	
})(jQuery);





<!--  TABLE SORTER jquery.tablesorter.min.js -->
(function($){$.extend({tablesorter:new function(){var parsers=[],widgets=[];this.defaults={cssHeader:"header",cssAsc:"headerSortUp",cssDesc:"headerSortDown",sortInitialOrder:"asc",sortMultiSortKey:"shiftKey",sortForce:null,sortAppend:null,textExtraction:"simple",parsers:{},widgets:[],widgetZebra:{css:["even","odd"]},headers:{},widthFixed:false,cancelSelection:true,sortList:[],headerList:[],dateFormat:"us",decimal:'.',debug:false};function benchmark(s,d){log(s+","+(new Date().getTime()-d.getTime())+"ms");}this.benchmark=benchmark;function log(s){if(typeof console!="undefined"&&typeof console.debug!="undefined"){console.log(s);}else{alert(s);}}function buildParserCache(table,$headers){if(table.config.debug){var parsersDebug="";}var rows=table.tBodies[0].rows;if(table.tBodies[0].rows[0]){var list=[],cells=rows[0].cells,l=cells.length;for(var i=0;i<l;i++){var p=false;if($.metadata&&($($headers[i]).metadata()&&$($headers[i]).metadata().sorter)){p=getParserById($($headers[i]).metadata().sorter);}else if((table.config.headers[i]&&table.config.headers[i].sorter)){p=getParserById(table.config.headers[i].sorter);}if(!p){p=detectParserForColumn(table,cells[i]);}if(table.config.debug){parsersDebug+="column:"+i+" parser:"+p.id+"\n";}list.push(p);}}if(table.config.debug){log(parsersDebug);}return list;};function detectParserForColumn(table,node){var l=parsers.length;for(var i=1;i<l;i++){if(parsers[i].is($.trim(getElementText(table.config,node)),table,node)){return parsers[i];}}return parsers[0];}function getParserById(name){var l=parsers.length;for(var i=0;i<l;i++){if(parsers[i].id.toLowerCase()==name.toLowerCase()){return parsers[i];}}return false;}function buildCache(table){if(table.config.debug){var cacheTime=new Date();}var totalRows=(table.tBodies[0]&&table.tBodies[0].rows.length)||0,totalCells=(table.tBodies[0].rows[0]&&table.tBodies[0].rows[0].cells.length)||0,parsers=table.config.parsers,cache={row:[],normalized:[]};for(var i=0;i<totalRows;++i){var c=table.tBodies[0].rows[i],cols=[];cache.row.push($(c));for(var j=0;j<totalCells;++j){cols.push(parsers[j].format(getElementText(table.config,c.cells[j]),table,c.cells[j]));}cols.push(i);cache.normalized.push(cols);cols=null;};if(table.config.debug){benchmark("Building cache for "+totalRows+" rows:",cacheTime);}return cache;};function getElementText(config,node){if(!node)return"";var t="";if(config.textExtraction=="simple"){if(node.childNodes[0]&&node.childNodes[0].hasChildNodes()){t=node.childNodes[0].innerHTML;}else{t=node.innerHTML;}}else{if(typeof(config.textExtraction)=="function"){t=config.textExtraction(node);}else{t=$(node).text();}}return t;}function appendToTable(table,cache){if(table.config.debug){var appendTime=new Date()}var c=cache,r=c.row,n=c.normalized,totalRows=n.length,checkCell=(n[0].length-1),tableBody=$(table.tBodies[0]),rows=[];for(var i=0;i<totalRows;i++){rows.push(r[n[i][checkCell]]);if(!table.config.appender){var o=r[n[i][checkCell]];var l=o.length;for(var j=0;j<l;j++){tableBody[0].appendChild(o[j]);}}}if(table.config.appender){table.config.appender(table,rows);}rows=null;if(table.config.debug){benchmark("Rebuilt table:",appendTime);}applyWidget(table);setTimeout(function(){$(table).trigger("sortEnd");},0);};function buildHeaders(table){if(table.config.debug){var time=new Date();}var meta=($.metadata)?true:false,tableHeadersRows=[];for(var i=0;i<table.tHead.rows.length;i++){tableHeadersRows[i]=0;};$tableHeaders=$("thead th",table);$tableHeaders.each(function(index){this.count=0;this.column=index;this.order=formatSortingOrder(table.config.sortInitialOrder);if(checkHeaderMetadata(this)||checkHeaderOptions(table,index))this.sortDisabled=true;if(!this.sortDisabled){$(this).addClass(table.config.cssHeader);}table.config.headerList[index]=this;});if(table.config.debug){benchmark("Built headers:",time);log($tableHeaders);}return $tableHeaders;};function checkCellColSpan(table,rows,row){var arr=[],r=table.tHead.rows,c=r[row].cells;for(var i=0;i<c.length;i++){var cell=c[i];if(cell.colSpan>1){arr=arr.concat(checkCellColSpan(table,headerArr,row++));}else{if(table.tHead.length==1||(cell.rowSpan>1||!r[row+1])){arr.push(cell);}}}return arr;};function checkHeaderMetadata(cell){if(($.metadata)&&($(cell).metadata().sorter===false)){return true;};return false;}function checkHeaderOptions(table,i){if((table.config.headers[i])&&(table.config.headers[i].sorter===false)){return true;};return false;}function applyWidget(table){var c=table.config.widgets;var l=c.length;for(var i=0;i<l;i++){getWidgetById(c[i]).format(table);}}function getWidgetById(name){var l=widgets.length;for(var i=0;i<l;i++){if(widgets[i].id.toLowerCase()==name.toLowerCase()){return widgets[i];}}};function formatSortingOrder(v){if(typeof(v)!="Number"){i=(v.toLowerCase()=="desc")?1:0;}else{i=(v==(0||1))?v:0;}return i;}function isValueInArray(v,a){var l=a.length;for(var i=0;i<l;i++){if(a[i][0]==v){return true;}}return false;}function setHeadersCss(table,$headers,list,css){$headers.removeClass(css[0]).removeClass(css[1]);var h=[];$headers.each(function(offset){if(!this.sortDisabled){h[this.column]=$(this);}});var l=list.length;for(var i=0;i<l;i++){h[list[i][0]].addClass(css[list[i][1]]);}}function fixColumnWidth(table,$headers){var c=table.config;if(c.widthFixed){var colgroup=$('<colgroup>');$("tr:first td",table.tBodies[0]).each(function(){colgroup.append($('<col>').css('width',$(this).width()));});$(table).prepend(colgroup);};}function updateHeaderSortCount(table,sortList){var c=table.config,l=sortList.length;for(var i=0;i<l;i++){var s=sortList[i],o=c.headerList[s[0]];o.count=s[1];o.count++;}}function multisort(table,sortList,cache){if(table.config.debug){var sortTime=new Date();}var dynamicExp="var sortWrapper = function(a,b) {",l=sortList.length;for(var i=0;i<l;i++){var c=sortList[i][0];var order=sortList[i][1];var s=(getCachedSortType(table.config.parsers,c)=="text")?((order==0)?"sortText":"sortTextDesc"):((order==0)?"sortNumeric":"sortNumericDesc");var e="e"+i;dynamicExp+="var "+e+" = "+s+"(a["+c+"],b["+c+"]); ";dynamicExp+="if("+e+") { return "+e+"; } ";dynamicExp+="else { ";}var orgOrderCol=cache.normalized[0].length-1;dynamicExp+="return a["+orgOrderCol+"]-b["+orgOrderCol+"];";for(var i=0;i<l;i++){dynamicExp+="}; ";}dynamicExp+="return 0; ";dynamicExp+="}; ";eval(dynamicExp);cache.normalized.sort(sortWrapper);if(table.config.debug){benchmark("Sorting on "+sortList.toString()+" and dir "+order+" time:",sortTime);}return cache;};function sortText(a,b){return((a<b)?-1:((a>b)?1:0));};function sortTextDesc(a,b){return((b<a)?-1:((b>a)?1:0));};function sortNumeric(a,b){return a-b;};function sortNumericDesc(a,b){return b-a;};function getCachedSortType(parsers,i){return parsers[i].type;};this.construct=function(settings){return this.each(function(){if(!this.tHead||!this.tBodies)return;var $this,$document,$headers,cache,config,shiftDown=0,sortOrder;this.config={};config=$.extend(this.config,$.tablesorter.defaults,settings);$this=$(this);$headers=buildHeaders(this);this.config.parsers=buildParserCache(this,$headers);cache=buildCache(this);var sortCSS=[config.cssDesc,config.cssAsc];fixColumnWidth(this);$headers.click(function(e){$this.trigger("sortStart");var totalRows=($this[0].tBodies[0]&&$this[0].tBodies[0].rows.length)||0;if(!this.sortDisabled&&totalRows>0){var $cell=$(this);var i=this.column;this.order=this.count++%2;if(!e[config.sortMultiSortKey]){config.sortList=[];if(config.sortForce!=null){var a=config.sortForce;for(var j=0;j<a.length;j++){if(a[j][0]!=i){config.sortList.push(a[j]);}}}config.sortList.push([i,this.order]);}else{if(isValueInArray(i,config.sortList)){for(var j=0;j<config.sortList.length;j++){var s=config.sortList[j],o=config.headerList[s[0]];if(s[0]==i){o.count=s[1];o.count++;s[1]=o.count%2;}}}else{config.sortList.push([i,this.order]);}};setTimeout(function(){setHeadersCss($this[0],$headers,config.sortList,sortCSS);appendToTable($this[0],multisort($this[0],config.sortList,cache));},1);return false;}}).mousedown(function(){if(config.cancelSelection){this.onselectstart=function(){return false};return false;}});$this.bind("update",function(){this.config.parsers=buildParserCache(this,$headers);cache=buildCache(this);}).bind("sorton",function(e,list){$(this).trigger("sortStart");config.sortList=list;var sortList=config.sortList;updateHeaderSortCount(this,sortList);setHeadersCss(this,$headers,sortList,sortCSS);appendToTable(this,multisort(this,sortList,cache));}).bind("appendCache",function(){appendToTable(this,cache);}).bind("applyWidgetId",function(e,id){getWidgetById(id).format(this);}).bind("applyWidgets",function(){applyWidget(this);});if($.metadata&&($(this).metadata()&&$(this).metadata().sortlist)){config.sortList=$(this).metadata().sortlist;}if(config.sortList.length>0){$this.trigger("sorton",[config.sortList]);}applyWidget(this);});};this.addParser=function(parser){var l=parsers.length,a=true;for(var i=0;i<l;i++){if(parsers[i].id.toLowerCase()==parser.id.toLowerCase()){a=false;}}if(a){parsers.push(parser);};};this.addWidget=function(widget){widgets.push(widget);};this.formatFloat=function(s){var i=parseFloat(s);return(isNaN(i))?0:i;};this.formatInt=function(s){var i=parseInt(s);return(isNaN(i))?0:i;};this.isDigit=function(s,config){var DECIMAL='\\'+config.decimal;var exp='/(^[+]?0('+DECIMAL+'0+)?$)|(^([-+]?[1-9][0-9]*)$)|(^([-+]?((0?|[1-9][0-9]*)'+DECIMAL+'(0*[1-9][0-9]*)))$)|(^[-+]?[1-9]+[0-9]*'+DECIMAL+'0+$)/';return RegExp(exp).test($.trim(s));};this.clearTableBody=function(table){if($.browser.msie){function empty(){while(this.firstChild)this.removeChild(this.firstChild);}empty.apply(table.tBodies[0]);}else{table.tBodies[0].innerHTML="";}};}});$.fn.extend({tablesorter:$.tablesorter.construct});var ts=$.tablesorter;ts.addParser({id:"text",is:function(s){return true;},format:function(s){return $.trim(s.toLowerCase());},type:"text"});ts.addParser({id:"digit",is:function(s,table){var c=table.config;return $.tablesorter.isDigit(s,c);},format:function(s){return $.tablesorter.formatFloat(s);},type:"numeric"});ts.addParser({id:"currency",is:function(s){return/^[£$€?.]/.test(s);},format:function(s){return $.tablesorter.formatFloat(s.replace(new RegExp(/[^0-9.]/g),""));},type:"numeric"});ts.addParser({id:"ipAddress",is:function(s){return/^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);},format:function(s){var a=s.split("."),r="",l=a.length;for(var i=0;i<l;i++){var item=a[i];if(item.length==2){r+="0"+item;}else{r+=item;}}return $.tablesorter.formatFloat(r);},type:"numeric"});ts.addParser({id:"url",is:function(s){return/^(https?|ftp|file):\/\/$/.test(s);},format:function(s){return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//),''));},type:"text"});ts.addParser({id:"isoDate",is:function(s){return/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);},format:function(s){return $.tablesorter.formatFloat((s!="")?new Date(s.replace(new RegExp(/-/g),"/")).getTime():"0");},type:"numeric"});ts.addParser({id:"percent",is:function(s){return/\%$/.test($.trim(s));},format:function(s){return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g),""));},type:"numeric"});ts.addParser({id:"usLongDate",is:function(s){return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));},format:function(s){return $.tablesorter.formatFloat(new Date(s).getTime());},type:"numeric"});ts.addParser({id:"shortDate",is:function(s){return/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);},format:function(s,table){var c=table.config;s=s.replace(/\-/g,"/");if(c.dateFormat=="us"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/,"$3/$1/$2");}else if(c.dateFormat=="uk"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/,"$3/$2/$1");}else if(c.dateFormat=="dd/mm/yy"||c.dateFormat=="dd-mm-yy"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/,"$1/$2/$3");}return $.tablesorter.formatFloat(new Date(s).getTime());},type:"numeric"});ts.addParser({id:"time",is:function(s){return/^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);},format:function(s){return $.tablesorter.formatFloat(new Date("2000/01/01 "+s).getTime());},type:"numeric"});ts.addParser({id:"metadata",is:function(s){return false;},format:function(s,table,cell){var c=table.config,p=(!c.parserMetadataName)?'sortValue':c.parserMetadataName;return $(cell).metadata()[p];},type:"numeric"});ts.addWidget({id:"zebra",format:function(table){if(table.config.debug){var time=new Date();}$("tr:visible",table.tBodies[0]).filter(':even').removeClass(table.config.widgetZebra.css[1]).addClass(table.config.widgetZebra.css[0]).end().filter(':odd').removeClass(table.config.widgetZebra.css[0]).addClass(table.config.widgetZebra.css[1]);if(table.config.debug){$.tablesorter.benchmark("Applying Zebra widget",time);}}});})(jQuery);



<!--  IMPROMPTU jQuery -->
/*
 * jQuery Impromptu
 * By: Trent Richardson [http://trentrichardson.com]
 * Version 2.8
 * Last Modified: 2/3/2010
 * 
 * Copyright 2010 Trent Richardson
 * Dual licensed under the MIT and GPL licenses.
 * http://trentrichardson.com/Impromptu/GPL-LICENSE.txt
 * http://trentrichardson.com/Impromptu/MIT-LICENSE.txt
 * 
 */
//(function($){$.prompt=function(message,options){options=$.extend({},$.prompt.defaults,options);$.prompt.currentPrefix=options.prefix;var ie6=($.browser.msie&&$.browser.version<7);var $body=$(document.body);var $window=$(window);var msgbox='<div class="'+options.prefix+'box" id="'+options.prefix+'box">';if(options.useiframe&&(($('object, applet').length>0)||ie6)){msgbox+='<iframe src="javascript:false;" style="display:block;position:absolute;z-index:-1;" class="'+options.prefix+'fade" id="'+options.prefix+'fade"></iframe>';}else{if(ie6){$('select').css('visibility','hidden');}msgbox+='<div class="'+options.prefix+'fade" id="'+options.prefix+'fade"></div>';}msgbox+='<div class="'+options.prefix+'" id="'+options.prefix+'"><div class="'+options.prefix+'container"><div class="';msgbox+=options.prefix+'close">X</div><div id="'+options.prefix+'states"></div>';msgbox+='</div></div></div>';var $jqib=$(msgbox).appendTo($body);var $jqi=$jqib.children('#'+options.prefix);var $jqif=$jqib.children('#'+options.prefix+'fade');if(message.constructor==String){message={state0:{html:message,buttons:options.buttons,focus:options.focus,submit:options.submit}};}var states="";$.each(message,function(statename,stateobj){stateobj=$.extend({},$.prompt.defaults.state,stateobj);message[statename]=stateobj;states+='<div id="'+options.prefix+'_state_'+statename+'" class="'+options.prefix+'_state" style="display:none;"><div class="'+options.prefix+'message">'+stateobj.html+'</div><div class="'+options.prefix+'buttons">';$.each(stateobj.buttons,function(k,v){states+='<button name="'+options.prefix+'_'+statename+'_button'+k+'" id="'+options.prefix+'_'+statename+'_button'+k+'" value="'+v+'">'+k+'</button>';});states+='</div></div>';});$jqi.find('#'+options.prefix+'states').html(states).children('.'+options.prefix+'_state:first').css('display','block');$jqi.find('.'+options.prefix+'buttons:empty').css('display','none');$.each(message,function(statename,stateobj){var $state=$jqi.find('#'+options.prefix+'_state_'+statename);$state.children('.'+options.prefix+'buttons').children('button').click(function(){var msg=$state.children('.'+options.prefix+'message');var clicked=stateobj.buttons[$(this).text()];var forminputs={};$.each($jqi.find('#'+options.prefix+'states :input').serializeArray(),function(i,obj){if(forminputs[obj.name]===undefined){forminputs[obj.name]=obj.value;}else if(typeof forminputs[obj.name]==Array||typeof forminputs[obj.name]=='object'){forminputs[obj.name].push(obj.value);}else{forminputs[obj.name]=[forminputs[obj.name],obj.value];}});var close=stateobj.submit(clicked,msg,forminputs);if(close===undefined||close){removePrompt(true,clicked,msg,forminputs);}});$state.find('.'+options.prefix+'buttons button:eq('+stateobj.focus+')').addClass(options.prefix+'defaultbutton');});var ie6scroll=function(){$jqib.css({top:$window.scrollTop()});};var fadeClicked=function(){if(options.persistent){var i=0;$jqib.addClass(options.prefix+'warning');var intervalid=setInterval(function(){$jqib.toggleClass(options.prefix+'warning');if(i++>1){clearInterval(intervalid);$jqib.removeClass(options.prefix+'warning');}},100);}else{removePrompt();}};var keyPressEventHandler=function(e){var key=(window.event)?event.keyCode:e.keyCode;if(key==27){fadeClicked();}if(key==9){var $inputels=$(':input:enabled:visible',$jqib);var fwd=!e.shiftKey&&e.target==$inputels[$inputels.length-1];var back=e.shiftKey&&e.target==$inputels[0];if(fwd||back){setTimeout(function(){if(!$inputels)return;var el=$inputels[back===true?$inputels.length-1:0];if(el)el.focus();},10);return false;}}};var positionPrompt=function(){$jqib.css({position:(ie6)?"absolute":"fixed",height:$window.height(),width:"100%",top:(ie6)?$window.scrollTop():0,left:0,right:0,bottom:0});$jqif.css({position:"absolute",height:$window.height(),width:"100%",top:0,left:0,right:0,bottom:0});$jqi.css({position:"absolute",top:options.top,left:"50%",marginLeft:(($jqi.outerWidth()/2)*-1)});};var stylePrompt=function(){$jqif.css({zIndex:options.zIndex,display:"none",opacity:options.opacity});$jqi.css({zIndex:options.zIndex+1,display:"none"});$jqib.css({zIndex:options.zIndex});};var removePrompt=function(callCallback,clicked,msg,formvals){$jqi.remove();if(ie6){$body.unbind('scroll',ie6scroll);}$window.unbind('resize',positionPrompt);$jqif.fadeOut(options.overlayspeed,function(){$jqif.unbind('click',fadeClicked);$jqif.remove();if(callCallback){options.callback(clicked,msg,formvals);}$jqib.unbind('keypress',keyPressEventHandler);$jqib.remove();if(ie6&&!options.useiframe){$('select').css('visibility','visible');}});};positionPrompt();stylePrompt();if(ie6){$window.scroll(ie6scroll);}$jqif.click(fadeClicked);$window.resize(positionPrompt);$jqib.bind("keydown keypress",keyPressEventHandler);$jqi.find('.'+options.prefix+'close').click(removePrompt);$jqif.fadeIn(options.overlayspeed);$jqi[options.show](options.promptspeed,options.loaded);$jqi.find('#'+options.prefix+'states .'+options.prefix+'_state:first .'+options.prefix+'defaultbutton').focus();if(options.timeout>0)setTimeout($.prompt.close,options.timeout);return $jqib;};$.prompt.defaults={prefix:'jqi',buttons:{Ok:true},loaded:function(){},submit:function(){return true;},callback:function(){},opacity:0.6,zIndex:999,overlayspeed:'slow',promptspeed:'fast',show:'fadeIn',focus:0,useiframe:false,top:"15%",persistent:true,timeout:0,state:{html:'',buttons:{Ok:true},focus:0,submit:function(){return true;}}};$.prompt.currentPrefix=$.prompt.defaults.prefix;$.prompt.setDefaults=function(o){$.prompt.defaults=$.extend({},$.prompt.defaults,o);};$.prompt.setStateDefaults=function(o){$.prompt.defaults.state=$.extend({},$.prompt.defaults.state,o);};$.prompt.getStateContent=function(state){return $('#'+$.prompt.currentPrefix+'_state_'+state);};$.prompt.getCurrentState=function(){return $('.'+$.prompt.currentPrefix+'_state:visible');};$.prompt.getCurrentStateName=function(){var stateid=$.prompt.getCurrentState().attr('id');return stateid.replace($.prompt.currentPrefix+'_state_','');};$.prompt.goToState=function(state){$('.'+$.prompt.currentPrefix+'_state').slideUp('slow');$('#'+$.prompt.currentPrefix+'_state_'+state).slideDown('slow',function(){$(this).find('.'+$.prompt.currentPrefix+'defaultbutton').focus();});};$.prompt.nextState=function(){var $next=$('.'+$.prompt.currentPrefix+'_state:visible').next();$('.'+$.prompt.currentPrefix+'_state').slideUp('slow');$next.slideDown('slow',function(){$next.find('.'+$.prompt.currentPrefix+'defaultbutton').focus();});};$.prompt.prevState=function(){var $next=$('.'+$.prompt.currentPrefix+'_state:visible').prev();$('.'+$.prompt.currentPrefix+'_state').slideUp('slow');$next.slideDown('slow',function(){$next.find('.'+$.prompt.currentPrefix+'defaultbutton').focus();});};$.prompt.close=function(){$('#'+$.prompt.currentPrefix+'box').fadeOut('fast',function(){$(this).remove();});};})(jQuery);

/*
 * jQuery Impromptu
 * By: Trent Richardson [http://trentrichardson.com]
 * Version 6.2.1
 * Last Modified: 2/3/2010
 * 
 * Copyright 2010 Trent Richardson
 * Dual licensed under the MIT and GPL licenses.
 * http://trentrichardson.com/Impromptu/GPL-LICENSE.txt
 * http://trentrichardson.com/Impromptu/MIT-LICENSE.txt
 * 
 */
!function(t,e){"function"==typeof define&&define.amd?define(["jquery"],e):e(t.jQuery)}(this,function(t){"use strict";var e=function(t,i){var n=this;return n.id=e.count++,e.lifo.push(n),t&&n.open(t,i),n};e.defaults={prefix:"jqi",classes:{box:"",fade:"",prompt:"",form:"",close:"",title:"",message:"",buttons:"",button:"",defaultButton:""},title:"",closeText:"&times;",buttons:{Ok:!0},buttonTimeout:1e3,loaded:function(){},submit:function(){},close:function(){},statechanging:function(){},statechanged:function(){},opacity:.6,zIndex:999,overlayspeed:"slow",promptspeed:"fast",show:"fadeIn",hide:"fadeOut",focus:0,defaultButton:0,useiframe:!1,top:"15%",position:{container:null,x:null,y:null,arrow:null,width:null},persistent:!0,timeout:0,states:{},initialState:0,state:{name:null,title:"",html:"",buttons:{Ok:!0},focus:0,defaultButton:0,position:{container:null,x:null,y:null,arrow:null,width:null},submit:function(){return!0}}},e.setDefaults=function(i){e.defaults=t.extend({},e.defaults,i)},e.setStateDefaults=function(i){e.defaults.state=t.extend({},e.defaults.state,i)},e.count=0,e.lifo=[],e.getLast=function(){var t=e.lifo.length;return t>0?e.lifo[t-1]:!1},e.removeFromStack=function(t){for(var i=e.lifo.length-1;i>=0;i--)if(e.lifo[i].id===t)return e.lifo.splice(i,1)[0]},e.prototype={id:null,open:function(i,n){var o=this;o.options=t.extend({},e.defaults,n),o.timeout&&clearTimeout(o.timeout),o.timeout=!1;var s=o.options,a=t(document.body),r=t(window),u='<div class="'+s.prefix+"box "+s.classes.box+'">';u+=s.useiframe&&t("object, applet").length>0?'<iframe src="javascript:false;" class="'+s.prefix+"fade "+s.classes.fade+'"></iframe>':'<div class="'+s.prefix+"fade "+s.classes.fade+'"></div>',u+='<div class="'+s.prefix+" "+s.classes.prompt+'"><form action="#" class="'+s.prefix+"form "+s.classes.form+'"><div class="'+s.prefix+"close "+s.classes.close+'">'+s.closeText+'</div><div class="'+s.prefix+'states"></div></form></div></div>',o.jqib=t(u).appendTo(a),o.jqi=o.jqib.children("."+s.prefix),o.jqif=o.jqib.children("."+s.prefix+"fade"),i.constructor===String&&(i={state0:{title:s.title,html:i,buttons:s.buttons,position:s.position,focus:s.focus,defaultButton:s.defaultButton,submit:s.submit}}),o.options.states={};var f,l;for(f in i)l=t.extend({},e.defaults.state,{name:f},i[f]),o.addState(l.name,l),""===o.currentStateName&&(o.currentStateName=l.name);o.jqi.on("click","."+s.prefix+"buttons button",function(){var e=t(this),i=e.parents("."+s.prefix+"state"),n=i.data("jqi-name"),a=o.options.states[n],r=i.children("."+s.prefix+"message"),u=a.buttons[e.text()]||a.buttons[e.html()],f={};if(o.options.buttonTimeout>0&&(o.disableStateButtons(n),setTimeout(function(){o.enableStateButtons(n)},o.options.buttonTimeout)),void 0===u)for(var l in a.buttons)(a.buttons[l].title===e.text()||a.buttons[l].title===e.html())&&(u=a.buttons[l].value);t.each(o.jqi.children("form").serializeArray(),function(t,e){void 0===f[e.name]?f[e.name]=e.value:typeof f[e.name]===Array||"object"==typeof f[e.name]?f[e.name].push(e.value):f[e.name]=[f[e.name],e.value]});var p=new t.Event("impromptu:submit");p.stateName=a.name,p.state=i,i.trigger(p,[u,r,f]),p.isDefaultPrevented()||o.close(!0,u,r,f)});var p=function(){if(s.persistent){var e=s.top.toString().indexOf("%")>=0?r.height()*(parseInt(s.top,10)/100):parseInt(s.top,10),i=parseInt(o.jqi.css("top").replace("px",""),10)-e;t("html,body").animate({scrollTop:i},"fast",function(){var t=0;o.jqib.addClass(s.prefix+"warning");var e=setInterval(function(){o.jqib.toggleClass(s.prefix+"warning"),t++>1&&(clearInterval(e),o.jqib.removeClass(s.prefix+"warning"))},100)})}else o.close(!0)},d=function(e){var i=window.event?event.keyCode:e.keyCode;if(27===i&&p(),13===i){var n=o.getCurrentState().find("."+s.prefix+"defaultbutton"),a=t(e.target);a.is("textarea,."+s.prefix+"button")===!1&&n.length>0&&(e.preventDefault(),n.click())}if(9===i){var r=t("input,select,textarea,button",o.getCurrentState()),u=!e.shiftKey&&e.target===r[r.length-1],f=e.shiftKey&&e.target===r[0];if(u||f)return setTimeout(function(){if(r){var t=r[f===!0?r.length-1:0];t&&t.focus()}},10),!1}};return o.position(),o.style(),o._windowResize=function(t){o.position(t)},r.resize({animate:!1},o._windowResize),o.jqif.click(p),o.jqi.find("."+s.prefix+"close").click(function(){o.close()}),o.jqi.find("."+s.prefix+"form").submit(function(){return!1}),o.jqib.on("keydown",d).on("impromptu:loaded",s.loaded).on("impromptu:close",s.close).on("impromptu:statechanging",s.statechanging).on("impromptu:statechanged",s.statechanged),o.jqif[s.show](s.overlayspeed),o.jqi[s.show](s.promptspeed,function(){o.goToState(isNaN(s.initialState)?s.initialState:o.jqi.find("."+s.prefix+"states ."+s.prefix+"state").eq(s.initialState).data("jqi-name")),o.jqib.trigger("impromptu:loaded")}),s.timeout>0&&(o.timeout=setTimeout(function(){o.close(!0)},s.timeout)),o},close:function(i,n,o,s){var a=this;return e.removeFromStack(a.id),a.timeout&&(clearTimeout(a.timeout),a.timeout=!1),a.jqib&&a.jqib[a.options.hide]("fast",function(){a.jqib.trigger("impromptu:close",[n,o,s]),a.jqib.remove(),t(window).off("resize",a._windowResize),"function"==typeof i&&i()}),a.currentStateName="",a},addState:function(i,n,o){var s,a,r,u,f,l=this,p="",d=null,c="",m="",h=l.options,v=l.jqi.find("."+h.prefix+"states"),g=[],b=0;if(n=t.extend({},e.defaults.state,{name:i},n),null!==n.position.arrow&&(c='<div class="'+h.prefix+"arrow "+h.prefix+"arrow"+n.position.arrow+'"></div>'),n.title&&""!==n.title&&(m='<div class="lead '+h.prefix+"title "+h.classes.title+'">'+n.title+"</div>"),s=n.html,"function"==typeof n.html&&(s="Error: html function must return text"),p+='<div class="'+h.prefix+'state" data-jqi-name="'+i+'">'+c+m+'<div class="'+h.prefix+"message "+h.classes.message+'">'+s+'</div><div class="'+h.prefix+"buttons"+(t.isEmptyObject(n.buttons)?"hide ":" ")+h.classes.buttons+'">',t.isArray(n.buttons))g=n.buttons;else if(t.isPlainObject(n.buttons))for(r in n.buttons)n.buttons.hasOwnProperty(r)&&g.push({title:r,value:n.buttons[r]});for(b=0,f=g.length;f>b;b++)u=g[b],a=n.focus===b||isNaN(n.focus)&&n.defaultButton===b?h.prefix+"defaultbutton "+h.classes.defaultButton:"",p+='<button class="'+h.classes.button+" "+h.prefix+"button "+a,"undefined"!=typeof u.classes&&(p+=" "+(t.isArray(u.classes)?u.classes.join(" "):u.classes)+" "),p+='" name="'+h.prefix+"_"+i+"_button"+u.title.replace(/[^a-z0-9]+/gi,"")+'" value="'+u.value+'">'+u.title+"</button>";return p+="</div></div>",d=t(p).css({display:"none"}),d.on("impromptu:submit",n.submit),void 0!==o?l.getState(o).after(d):v.append(d),l.options.states[i]=n,d},removeState:function(t,e){var i=this,n=i.getState(t),o=function(){n.remove()};return 0===n.length?!1:("none"!==n.css("display")?void 0!==e&&i.getState(e).length>0?i.goToState(e,!1,o):n.next().length>0?i.nextState(o):n.prev().length>0?i.prevState(o):i.close():n.slideUp("slow",o),!0)},getApi:function(){return this},getBox:function(){return this.jqib},getPrompt:function(){return this.jqi},getState:function(t){return this.jqi.find('[data-jqi-name="'+t+'"]')},getCurrentState:function(){return this.getState(this.getCurrentStateName())},getCurrentStateName:function(){return this.currentStateName},disableStateButtons:function(e,i,n){var o=this;t.isArray(e)&&(i=e,e=null),o.getState(e||o.getCurrentStateName()).find("."+o.options.prefix+"button").each(function(e,o){(void 0===i||-1!==t.inArray(o.value,i))&&(o.disabled=!n)})},enableStateButtons:function(t,e){this.disableStateButtons(t,e,!0)},position:function(e){var i=this,n=t.fx.off,o=i.getCurrentState(),s=i.options.states[o.data("jqi-name")],a=s?s.position:void 0,r=t(window),u=document.body.scrollHeight,f=t(window).height(),l=(t(document).height(),u>f?u:f),p=parseInt(r.scrollTop(),10),d=p+(i.options.top.toString().indexOf("%")>=0?f*(parseInt(i.options.top,10)/100):parseInt(i.options.top,10));if(void 0!==e&&e.data.animate===!1&&(t.fx.off=!0),i.jqib.css({position:"absolute",height:l,width:"100%",top:0,left:0,right:0,bottom:0}),i.jqif.css({position:"fixed",height:l,width:"100%",top:0,left:0,right:0,bottom:0}),a&&a.container){var c=t(a.container).offset(),m=!1;t.isPlainObject(c)&&void 0!==c.top&&(d=c.top+a.y-(i.options.top.toString().indexOf("%")>=0?f*(parseInt(i.options.top,10)/100):parseInt(i.options.top,10)),i.jqi.css({position:"absolute"}),i.jqi.animate({top:c.top+a.y,left:c.left+a.x,marginLeft:0,width:void 0!==a.width?a.width:null},function(){!m&&c.top+a.y+i.jqi.outerHeight(!0)>p+f&&(t("html,body").animate({scrollTop:d},"slow","swing",function(){}),m=!0)}),(p>d||d>p+f)&&(t("html,body").animate({scrollTop:d},"slow","swing",function(){}),m=!0))}else a&&a.width?(i.jqi.css({position:"absolute",left:"50%"}),i.jqi.animate({top:a.y||d,left:a.x||"50%",marginLeft:a.width/2*-1,width:a.width})):i.jqi.css({position:"absolute",top:d,left:"50%",marginLeft:i.jqi.outerWidth(!1)/2*-1});void 0!==e&&e.data.animate===!1&&(t.fx.off=n)},style:function(){var t=this;t.jqif.css({zIndex:t.options.zIndex,display:"none",opacity:t.options.opacity}),t.jqi.css({zIndex:t.options.zIndex+1,display:"none"}),t.jqib.css({zIndex:t.options.zIndex})},goToState:function(e,i,n){var o=this,s=(o.jqi,o.options),a=o.getState(e),r=s.states[a.data("jqi-name")],u=new t.Event("impromptu:statechanging"),f=o.options;if(void 0!==r){if("function"==typeof r.html){var l=r.html;a.find("."+f.prefix+"message ").html(l())}"function"==typeof i&&(n=i,i=!1),o.jqib.trigger(u,[o.getCurrentStateName(),e]),!u.isDefaultPrevented()&&a.length>0&&(o.jqi.find("."+f.prefix+"parentstate").removeClass(f.prefix+"parentstate"),i?(o.jqi.find("."+f.prefix+"substate").not(a).slideUp(s.promptspeed).removeClass("."+f.prefix+"substate").find("."+f.prefix+"arrow").hide(),o.jqi.find("."+f.prefix+"state:visible").addClass(f.prefix+"parentstate"),a.addClass(f.prefix+"substate")):o.jqi.find("."+f.prefix+"state").not(a).slideUp(s.promptspeed).find("."+f.prefix+"arrow").hide(),o.currentStateName=r.name,a.slideDown(s.promptspeed,function(){var i=t(this);o.enableStateButtons(),"string"==typeof r.focus?i.find(r.focus).eq(0).focus():i.find("."+f.prefix+"defaultbutton").focus(),i.find("."+f.prefix+"arrow").show(s.promptspeed),"function"==typeof n&&o.jqib.on("impromptu:statechanged",n),o.jqib.trigger("impromptu:statechanged",[e]),"function"==typeof n&&o.jqib.off("impromptu:statechanged",n)}),i||o.position())}return a},nextState:function(t){var e=this,i=e.getCurrentState().next();return i.length>0&&e.goToState(i.data("jqi-name"),t),i},prevState:function(t){var e=this,i=e.getCurrentState().prev();return i.length>0&&e.goToState(i.data("jqi-name"),t),i}},t.prompt=function(t,i){var n=new e(t,i);return n.jqi},t.each(e,function(e,i){t.prompt[e]=i}),t.each(e.prototype,function(i){t.prompt[i]=function(){var t=e.getLast();return t&&"function"==typeof t[i]?t[i].apply(t,arguments):void 0}}),t.fn.prompt=function(e){void 0===e&&(e={}),void 0===e.withDataAndEvents&&(e.withDataAndEvents=!1),t.prompt(t(this).clone(e.withDataAndEvents).html(),e)},window.Impromptu=e});

/*! jQuery-Impromptu - v6.2.3 - 2016-04-23
* http://trentrichardson.com/Impromptu
* Copyright (c) 2016 Trent Richardson; Licensed MIT */
//!function(a,b){"function"==typeof define&&define.amd?define(["jquery"],b):b(a.jQuery)}(this,function(a){"use strict";var b=function(a,c){var d=this;return d.id=b.count++,b.lifo.push(d),a&&d.open(a,c),d};b.defaults={prefix:"jqi",classes:{box:"",fade:"",prompt:"",form:"",close:"",title:"",message:"",buttons:"",button:"",defaultButton:""},title:"",closeText:"&times;",buttons:{Ok:!0},buttonTimeout:1e3,loaded:function(a){},submit:function(a,b,c,d){},close:function(a,b,c,d){},statechanging:function(a,b,c){},statechanged:function(a,b){},opacity:.6,zIndex:999,overlayspeed:"slow",promptspeed:"fast",show:"fadeIn",hide:"fadeOut",focus:0,defaultButton:0,useiframe:!1,top:"15%",position:{container:null,x:null,y:null,arrow:null,width:null},persistent:!0,timeout:0,states:{},initialState:0,state:{name:null,title:"",html:"",buttons:{Ok:!0},focus:0,defaultButton:0,position:{container:null,x:null,y:null,arrow:null,width:null},submit:function(a,b,c,d){return!0}}},b.setDefaults=function(c){b.defaults=a.extend({},b.defaults,c)},b.setStateDefaults=function(c){b.defaults.state=a.extend({},b.defaults.state,c)},b.count=0,b.lifo=[],b.getLast=function(){var a=b.lifo.length;return a>0?b.lifo[a-1]:!1},b.removeFromStack=function(a){for(var c=b.lifo.length-1;c>=0;c--)if(b.lifo[c].id===a)return b.lifo.splice(c,1)[0]},b.prototype={id:null,open:function(c,d){var e=this;e.options=a.extend({},b.defaults,d),e.timeout&&clearTimeout(e.timeout),e.timeout=!1;var f=e.options,g=a(document.body),h=a(window),i='<div class="'+f.prefix+"box "+f.classes.box+'">';i+=f.useiframe&&a("object, applet").length>0?'<iframe src="javascript:false;" class="'+f.prefix+"fade "+f.classes.fade+'"></iframe>':'<div class="'+f.prefix+"fade "+f.classes.fade+'"></div>',i+='<div class="'+f.prefix+" "+f.classes.prompt+'"><form action="#" class="'+f.prefix+"form "+f.classes.form+'"><div class="'+f.prefix+"close "+f.classes.close+'">'+f.closeText+'</div><div class="'+f.prefix+'states"></div></form></div></div>',e.jqib=a(i).appendTo(g),e.jqi=e.jqib.children("."+f.prefix),e.jqif=e.jqib.children("."+f.prefix+"fade"),c.constructor===String&&(c={state0:{title:f.title,html:c,buttons:f.buttons,position:f.position,focus:f.focus,defaultButton:f.defaultButton,submit:f.submit}}),e.options.states={};var j,k;for(j in c)k=a.extend({},b.defaults.state,{name:j},c[j]),e.addState(k.name,k),""===e.currentStateName&&(e.currentStateName=k.name);e.jqi.on("click","."+f.prefix+"buttons button",function(b){var c=a(this),d=c.parents("."+f.prefix+"state"),g=d.data("jqi-name"),h=e.options.states[g],i=d.children("."+f.prefix+"message"),j=h.buttons[c.text()]||h.buttons[c.html()],k={};if(e.options.buttonTimeout>0&&(e.disableStateButtons(g),setTimeout(function(){e.enableStateButtons(g)},e.options.buttonTimeout)),void 0===j)for(var l in h.buttons)(h.buttons[l].title===c.text()||h.buttons[l].title===c.html())&&(j=h.buttons[l].value);a.each(e.jqi.children("form").serializeArray(),function(a,b){void 0===k[b.name]?k[b.name]=b.value:typeof k[b.name]===Array||"object"==typeof k[b.name]?k[b.name].push(b.value):k[b.name]=[k[b.name],b.value]});var m=new a.Event("impromptu:submit");m.stateName=h.name,m.state=d,d.trigger(m,[j,i,k]),m.isDefaultPrevented()||e.close(!0,j,i,k)});var l=function(){if(f.persistent){var b=f.top.toString().indexOf("%")>=0?h.height()*(parseInt(f.top,10)/100):parseInt(f.top,10),c=parseInt(e.jqi.css("top").replace("px",""),10)-b;a("html,body").animate({scrollTop:c},"fast",function(){var a=0;e.jqib.addClass(f.prefix+"warning");var b=setInterval(function(){e.jqib.toggleClass(f.prefix+"warning"),a++>1&&(clearInterval(b),e.jqib.removeClass(f.prefix+"warning"))},100)})}else e.close(!0)},m=function(b){var c=window.event?event.keyCode:b.keyCode;if(27===c&&l(),13===c){var d=e.getCurrentState().find("."+f.prefix+"defaultbutton"),g=a(b.target);g.is("textarea,."+f.prefix+"button")===!1&&d.length>0&&(b.preventDefault(),d.click())}if(9===c){var h=a("input,select,textarea,button",e.getCurrentState()),i=!b.shiftKey&&b.target===h[h.length-1],j=b.shiftKey&&b.target===h[0];if(i||j)return setTimeout(function(){if(h){var a=h[j===!0?h.length-1:0];a&&a.focus()}},10),!1}};return e.position(),e.style(),e._windowResize=function(a){e.position(a)},h.resize({animate:!1},e._windowResize),e.jqif.click(l),e.jqi.find("."+f.prefix+"close").click(function(){e.close()}),e.jqi.find("."+f.prefix+"form").submit(function(){return!1}),e.jqib.on("keydown",m).on("impromptu:loaded",f.loaded).on("impromptu:close",f.close).on("impromptu:statechanging",f.statechanging).on("impromptu:statechanged",f.statechanged),e.jqif[f.show](f.overlayspeed),e.jqi[f.show](f.promptspeed,function(){e.goToState(isNaN(f.initialState)?f.initialState:e.jqi.find("."+f.prefix+"states ."+f.prefix+"state").eq(f.initialState).data("jqi-name")),e.jqib.trigger("impromptu:loaded")}),f.timeout>0&&(e.timeout=setTimeout(function(){e.close(!0)},f.timeout)),e},close:function(c,d,e,f){var g=this;return b.removeFromStack(g.id),g.timeout&&(clearTimeout(g.timeout),g.timeout=!1),g.jqib&&g.jqib[g.options.hide]("fast",function(){g.jqib.trigger("impromptu:close",[d,e,f]),g.jqib.remove(),a(window).off("resize",g._windowResize),"function"==typeof c&&c()}),g.currentStateName="",g},addState:function(c,d,e){var f,g,h,i,j,k=this,l="",m=null,n="",o="",p=k.options,q=a.isFunction(d.position)?d.position():d.position,r=k.jqi.find("."+p.prefix+"states"),s=[],t=0;if(d=a.extend({},b.defaults.state,{name:c},d),a.isPlainObject(q)&&null!==q.arrow&&(n='<div class="'+p.prefix+"arrow "+p.prefix+"arrow"+q.arrow+'"></div>'),d.title&&""!==d.title&&(o='<div class="lead '+p.prefix+"title "+p.classes.title+'">'+d.title+"</div>"),f=d.html,"function"==typeof d.html&&(f="Error: html function must return text"),l+='<div class="'+p.prefix+'state" data-jqi-name="'+c+'">'+n+o+'<div class="'+p.prefix+"message "+p.classes.message+'">'+f+'</div><div class="'+p.prefix+"buttons"+(a.isEmptyObject(d.buttons)?"hide ":" ")+p.classes.buttons+'">',a.isArray(d.buttons))s=d.buttons;else if(a.isPlainObject(d.buttons))for(h in d.buttons)d.buttons.hasOwnProperty(h)&&s.push({title:h,value:d.buttons[h]});for(t=0,j=s.length;j>t;t++)i=s[t],g=d.focus===t||isNaN(d.focus)&&d.defaultButton===t?p.prefix+"defaultbutton "+p.classes.defaultButton:"",l+='<button class="'+p.classes.button+" "+p.prefix+"button "+g,"undefined"!=typeof i.classes&&(l+=" "+(a.isArray(i.classes)?i.classes.join(" "):i.classes)+" "),l+='" name="'+p.prefix+"_"+c+"_button"+i.title.replace(/[^a-z0-9]+/gi,"")+'" value="'+i.value+'">'+i.title+"</button>";return l+="</div></div>",m=a(l).css({display:"none"}),m.on("impromptu:submit",d.submit),void 0!==e?k.getState(e).after(m):r.append(m),k.options.states[c]=d,m},removeState:function(a,b){var c=this,d=c.getState(a),e=function(){d.remove()};return 0===d.length?!1:("none"!==d.css("display")?void 0!==b&&c.getState(b).length>0?c.goToState(b,!1,e):d.next().length>0?c.nextState(e):d.prev().length>0?c.prevState(e):c.close():d.slideUp("slow",e),!0)},getApi:function(){return this},getBox:function(){return this.jqib},getPrompt:function(){return this.jqi},getState:function(a){return this.jqi.find('[data-jqi-name="'+a+'"]')},getCurrentState:function(){return this.getState(this.getCurrentStateName())},getCurrentStateName:function(){return this.currentStateName},disableStateButtons:function(b,c,d){var e=this;a.isArray(b)&&(c=b,b=null),e.getState(b||e.getCurrentStateName()).find("."+e.options.prefix+"button").each(function(b,e){(void 0===c||-1!==a.inArray(e.value,c))&&(e.disabled=!d)})},enableStateButtons:function(a,b){this.disableStateButtons(a,b,!0)},position:function(b){var c=this,d=a.fx.off,e=c.getCurrentState(),f=c.options.states[e.data("jqi-name")],g=f?a.isFunction(f.position)?f.position():f.position:void 0,h=a(window),i=document.body.scrollHeight,j=a(window).height(),k=(a(document).height(),i>j?i:j),l=parseInt(h.scrollTop(),10),m=l+(c.options.top.toString().indexOf("%")>=0?j*(parseInt(c.options.top,10)/100):parseInt(c.options.top,10));if(void 0!==b&&b.data.animate===!1&&(a.fx.off=!0),c.jqib.css({position:"absolute",height:k,width:"100%",top:0,left:0,right:0,bottom:0}),c.jqif.css({position:"fixed",height:k,width:"100%",top:0,left:0,right:0,bottom:0}),g&&g.container){var n=a(g.container).offset(),o=!1;a.isPlainObject(n)&&void 0!==n.top&&(m=n.top+g.y-(c.options.top.toString().indexOf("%")>=0?j*(parseInt(c.options.top,10)/100):parseInt(c.options.top,10)),c.jqi.css({position:"absolute"}),c.jqi.animate({top:n.top+g.y,left:n.left+g.x,marginLeft:0,width:void 0!==g.width?g.width:null},function(){!o&&n.top+g.y+c.jqi.outerHeight(!0)>l+j&&(a("html,body").animate({scrollTop:m},"slow","swing",function(){}),o=!0)}),(l>m||m>l+j)&&(a("html,body").animate({scrollTop:m},"slow","swing",function(){}),o=!0))}else g&&g.width?(c.jqi.css({position:"absolute",left:"50%"}),c.jqi.animate({top:g.y||m,left:g.x||"50%",marginLeft:g.width/2*-1,width:g.width})):c.jqi.css({position:"absolute",top:m,left:"50%",marginLeft:c.jqi.outerWidth(!1)/2*-1});void 0!==b&&b.data.animate===!1&&(a.fx.off=d)},style:function(){var a=this;a.jqif.css({zIndex:a.options.zIndex,display:"none",opacity:a.options.opacity}),a.jqi.css({zIndex:a.options.zIndex+1,display:"none"}),a.jqib.css({zIndex:a.options.zIndex})},goToState:function(b,c,d){var e=this,f=(e.jqi,e.options),g=e.getState(b),h=f.states[g.data("jqi-name")],i=new a.Event("impromptu:statechanging"),j=e.options;if(void 0!==h){if("function"==typeof h.html){var k=h.html;g.find("."+j.prefix+"message ").html(k())}"function"==typeof c&&(d=c,c=!1),e.jqib.trigger(i,[e.getCurrentStateName(),b]),!i.isDefaultPrevented()&&g.length>0&&(e.jqi.find("."+j.prefix+"parentstate").removeClass(j.prefix+"parentstate"),c?(e.jqi.find("."+j.prefix+"substate").not(g).slideUp(f.promptspeed).removeClass("."+j.prefix+"substate").find("."+j.prefix+"arrow").hide(),e.jqi.find("."+j.prefix+"state:visible").addClass(j.prefix+"parentstate"),g.addClass(j.prefix+"substate")):e.jqi.find("."+j.prefix+"state").not(g).slideUp(f.promptspeed).find("."+j.prefix+"arrow").hide(),e.currentStateName=h.name,g.slideDown(f.promptspeed,function(){var c=a(this);e.enableStateButtons(),"string"==typeof h.focus?c.find(h.focus).eq(0).focus():c.find("."+j.prefix+"defaultbutton").focus(),c.find("."+j.prefix+"arrow").show(f.promptspeed),"function"==typeof d&&e.jqib.on("impromptu:statechanged",d),e.jqib.trigger("impromptu:statechanged",[b]),"function"==typeof d&&e.jqib.off("impromptu:statechanged",d)}),c||e.position())}return g},nextState:function(a){var b=this,c=b.getCurrentState().next();return c.length>0&&b.goToState(c.data("jqi-name"),a),c},prevState:function(a){var b=this,c=b.getCurrentState().prev();return c.length>0&&b.goToState(c.data("jqi-name"),a),c}},a.prompt=function(a,c){var d=new b(a,c);return d.jqi},a.each(b,function(b,c){a.prompt[b]=c}),a.each(b.prototype,function(c,d){a.prompt[c]=function(){var a=b.getLast();return a&&"function"==typeof a[c]?a[c].apply(a,arguments):void 0}}),a.fn.prompt=function(b){void 0===b&&(b={}),void 0===b.withDataAndEvents&&(b.withDataAndEvents=!1),a.prompt(a(this).clone(b.withDataAndEvents).html(),b)},window.Impromptu=b});

<!--  JSON jquery.json-1.3.min.js -->
(function($){function toIntegersAtLease(n)
{return n<10?'0'+n:n;}
Date.prototype.toJSON=function(date)
{return this.getUTCFullYear()+'-'+
toIntegersAtLease(this.getUTCMonth())+'-'+
toIntegersAtLease(this.getUTCDate());};var escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};$.quoteString=function(string)
{if(escapeable.test(string))
{return'"'+string.replace(escapeable,function(a)
{var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};$.toJSON=function(o,compact)
{var type=typeof(o);if(type=="undefined")
return"undefined";else if(type=="number"||type=="boolean")
return o+"";else if(o===null)
return"null";if(type=="string")
{return $.quoteString(o);}
if(type=="object"&&typeof o.toJSON=="function")
return o.toJSON(compact);if(type!="function"&&typeof(o.length)=="number")
{var ret=[];for(var i=0;i<o.length;i++){ret.push($.toJSON(o[i],compact));}
if(compact)
return"["+ret.join(",")+"]";else
return"["+ret.join(", ")+"]";}
if(type=="function"){throw new TypeError("Unable to convert object of type 'function' to json.");}
var ret=[];for(var k in o){var name;type=typeof(k);if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;var val=$.toJSON(o[k],compact);if(typeof(val)!="string"){continue;}
if(compact)
ret.push(name+":"+val);else
ret.push(name+": "+val);}
return"{"+ret.join(", ")+"}";};$.compactJSON=function(o)
{return $.toJSON(o,true);};$.evalJSON=function(src)
{return eval("("+src+")");};$.secureEvalJSON=function(src)
{var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};})(jQuery);


<!--  HOVERINTENT hoverIntent r7 -->
/*!
 * hoverIntent r7 // 2013.03.11 // jQuery 1.9.1+
 * http://cherne.net/brian/resources/jquery.hoverIntent.html
 *
 * You may use hoverIntent under the terms of the MIT license.
 * Copyright 2007, 2013 Brian Cherne
 */
(function(e){e.fn.hoverIntent=function(t,n,r){var i={interval:100,sensitivity:7,timeout:0};if(typeof t==="object"){i=e.extend(i,t)}else if(e.isFunction(n)){i=e.extend(i,{over:t,out:n,selector:r})}else{i=e.extend(i,{over:t,out:t,selector:n})}var s,o,u,a;var f=function(e){s=e.pageX;o=e.pageY};var l=function(t,n){n.hoverIntent_t=clearTimeout(n.hoverIntent_t);if(Math.abs(u-s)+Math.abs(a-o)<i.sensitivity){e(n).off("mousemove.hoverIntent",f);n.hoverIntent_s=1;return i.over.apply(n,[t])}else{u=s;a=o;n.hoverIntent_t=setTimeout(function(){l(t,n)},i.interval)}};var c=function(e,t){t.hoverIntent_t=clearTimeout(t.hoverIntent_t);t.hoverIntent_s=0;return i.out.apply(t,[e])};var h=function(t){var n=jQuery.extend({},t);var r=this;if(r.hoverIntent_t){r.hoverIntent_t=clearTimeout(r.hoverIntent_t)}if(t.type=="mouseenter"){u=n.pageX;a=n.pageY;e(r).on("mousemove.hoverIntent",f);if(r.hoverIntent_s!=1){r.hoverIntent_t=setTimeout(function(){l(n,r)},i.interval)}}else{e(r).off("mousemove.hoverIntent",f);if(r.hoverIntent_s==1){r.hoverIntent_t=setTimeout(function(){c(n,r)},i.timeout)}}};return this.on({"mouseenter.hoverIntent":h,"mouseleave.hoverIntent":h},i.selector)}})(jQuery);


<!--  ALPHANUMERIC hoverIntent r7 -->
/**
* Alphanumeric Check Plugin
**/
(function($){

	$.fn.alphanumeric = function(p) { 

		p = $.extend({
			ichars: "!@#$%^&*()+=[]\\\';,/{}|\":<>?~`.- ",
			nchars: "",
			allow: ""
		  }, p);	

		return this.each
			(
				function() 
				{

					if (p.nocaps) p.nchars += "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					if (p.allcaps) p.nchars += "abcdefghijklmnopqrstuvwxyz";
					
					s = p.allow.split('');
					for ( i=0;i<s.length;i++) if (p.ichars.indexOf(s[i]) != -1) s[i] = "\\" + s[i];
					p.allow = s.join('|');
					
					var reg = new RegExp(p.allow,'gi');
					var ch = p.ichars + p.nchars;
					ch = ch.replace(reg,'');

					$(this).keypress
						(
							function (e)
								{
								
									if (!e.charCode) k = String.fromCharCode(e.which);
										else k = String.fromCharCode(e.charCode);
										
									if (ch.indexOf(k) != -1) e.preventDefault();
									if (e.ctrlKey&&k=='v') e.preventDefault();
									
								}
								
						);
						
					$(this).bind('contextmenu',function () {return false});
									
				}
			);

	};

	$.fn.numeric = function(p) {
	
		var az = "abcdefghijklmnopqrstuvwxyz";
		az += az.toUpperCase();

		p = $.extend({
			nchars: az
		  }, p);	
		  	
		return this.each (function()
			{
				$(this).alphanumeric(p);
			}
		);
			
	};
	
	$.fn.alpha = function(p) {

		var nm = "1234567890";

		p = $.extend({
			nchars: nm
		  }, p);	

		return this.each (function()
			{
				$(this).alphanumeric(p);
			}
		);
			
	};	

})(jQuery);
