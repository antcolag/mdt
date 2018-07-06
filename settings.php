<?php
function mdt_settings(){
	global $mdt;
/*	wp_register_script( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), NULL, true );
	wp_register_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), NULL, true );
	wp_register_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', false, NULL, 'all' );

	wp_enqueue_script( 'bootstrap-js' );
	wp_enqueue_style( 'bootstrap-css' );
	wp_enqueue_style( 'jquery-ui' );*/
	$scheduletime = $mdt->getScheduleFrequency('array');
	$nextupdate = $mdt->getNextScheduleTime('array');
	$updatesettings = MsxDropshippingTool::getSharedOption('updateSettings');
	$nextsingleupdate = $mdt->getNextOneShotScheduleTime('array');
	$nextsingleupdatetime = $mdt->getNextOneShotScheduleTime();
	?>
	<!-- Latest compiled and minified CSS -->
<main>
	
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">	
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
<!-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/dot-luv/jquery-ui.css"> -->
<!-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/black-tie/jquery-ui.css"> -->
<!--  -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- ciao -->
<style type="text/css">

#save-update-fields,#reset-update-fields,#clear-update-fields{
	padding-left:1em;
	padding-right:1em;
	margin-top:1em;
}
#clock:hover{
	box-shadow: 0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,0.8);
}
main{
	width:100;
	background: #fff;
}
.update-time-field{
	width:3em;
}
#clock{
	height:140px;
	background: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjI1NnB4IiBoZWlnaHQ9IjI1NnB4IiB2aWV3Qm94PSIwIDAgNDg1LjIxMyA0ODUuMjEyIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0ODUuMjEzIDQ4NS4yMTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPGc+Cgk8cGF0aCBkPSJNNjAuNjUyLDc1LjgxNlYxNS4xNjNDNjAuNjUyLDYuNzgxLDY3LjQzMywwLDc1LjgxNywwYzguMzgsMCwxNS4xNjEsNi43ODEsMTUuMTYxLDE1LjE2M3Y2MC42NTMgICBjMCw4LjM4LTYuNzgxLDE1LjE2MS0xNS4xNjEsMTUuMTYxQzY3LjQzMyw5MC45NzgsNjAuNjUyLDg0LjE5Niw2MC42NTIsNzUuODE2eiBNMzE4LjQyNCw5MC45NzggICBjOC4zNzgsMCwxNS4xNjMtNi43ODEsMTUuMTYzLTE1LjE2MVYxNS4xNjNDMzMzLjU4Nyw2Ljc4MSwzMjYuODAyLDAsMzE4LjQyNCwwYy04LjM4MiwwLTE1LjE2OCw2Ljc4MS0xNS4xNjgsMTUuMTYzdjYwLjY1MyAgIEMzMDMuMjU2LDg0LjE5NiwzMTAuMDQyLDkwLjk3OCwzMTguNDI0LDkwLjk3OHogTTQ4NS4yMTIsMzYzLjkwNmMwLDY2Ljk5Ni01NC4zMTIsMTIxLjMwNy0xMjEuMzAzLDEyMS4zMDcgICBjLTY2Ljk4NiwwLTEyMS4zMDItNTQuMzExLTEyMS4zMDItMTIxLjMwN2MwLTY2Ljk4Niw1NC4zMTUtMTIxLjMsMTIxLjMwMi0xMjEuM0M0MzAuOSwyNDIuNjA2LDQ4NS4yMTIsMjk2LjkxOSw0ODUuMjEyLDM2My45MDZ6ICAgIE00NTQuODksMzYzLjkwNmMwLTUwLjE2MS00MC44MS05MC45NzYtOTAuOTgtOTAuOTc2Yy01MC4xNjYsMC05MC45NzYsNDAuODE0LTkwLjk3Niw5MC45NzZjMCw1MC4xNzEsNDAuODEsOTAuOTgsOTAuOTc2LDkwLjk4ICAgQzQxNC4wOCw0NTQuODg2LDQ1NC44OSw0MTQuMDc3LDQ1NC44OSwzNjMuOTA2eiBNMTIxLjMwNSwxODEuOTU1SDYwLjY1MnY2MC42NTFoNjAuNjUzVjE4MS45NTV6IE02MC42NTIsMzMzLjU4NGg2MC42NTNWMjcyLjkzICAgSDYwLjY1MlYzMzMuNTg0eiBNMTUxLjYyOSwyNDIuNjA2aDYwLjY1NHYtNjAuNjUxaC02MC42NTRWMjQyLjYwNnogTTE1MS42MjksMzMzLjU4NGg2MC42NTRWMjcyLjkzaC02MC42NTRWMzMzLjU4NHogICAgTTMwLjMyOCwzNjAuODkxVjE1MS42MjhoMzMzLjU4MnY2MC42NTNoMzAuMzI3Vjk0YzAtMTguNDIxLTE0LjY5Mi0zMy4zNDktMzIuODQzLTMzLjM0OWgtMTIuNjQ3djE1LjE2NiAgIGMwLDE2LjcwMS0xMy41OTYsMzAuMzI1LTMwLjMyMiwzMC4zMjVjLTE2LjczMSwwLTMwLjMyNi0xMy42MjQtMzAuMzI2LTMwLjMyNVY2MC42NTFIMTA2LjE0djE1LjE2NiAgIGMwLDE2LjcwMS0xMy41OTMsMzAuMzI1LTMwLjMyMiwzMC4zMjVjLTE2LjczMywwLTMwLjMyNy0xMy42MjQtMzAuMzI3LTMwLjMyNVY2MC42NTFIMzIuODU5QzE0LjcwNyw2MC42NTEsMC4wMDEsNzUuNTc5LDAuMDAxLDk0ICAgdjI2Ni44OTJjMCwxOC4zNiwxNC43MDYsMzMuMzQ2LDMyLjg1OCwzMy4zNDZoMTc5LjQyNHYtMzAuMzMxSDMyLjg1OUMzMS40ODUsMzYzLjkwNiwzMC4zMjgsMzYyLjQ4NywzMC4zMjgsMzYwLjg5MXogICAgTTMwMy4yNTYsMjQyLjYwNnYtNjAuNjUxaC02MC42NDh2NjAuNjUxSDMwMy4yNTZ6IE00MjguMjMxLDMzNC4zNTljLTUuOTIzLTUuOTI4LTE1LjUxOS01LjkyOC0yMS40MzcsMGwtNTMuNjAyLDUzLjYwMiAgIGwtMzIuMTctMzIuMTY2Yy01LjkyMy01LjkyMy0xNS41MTgtNS45MjMtMjEuNDQsMHMtNS45MjMsMTUuNTE5LDAsMjEuNDRsNDIuODg2LDQyLjg4NmMyLjk1OSwyLjk1OSw2Ljg0Miw0LjQzOCwxMC43MjUsNC40MzggICBjMy44NzQsMCw3Ljc1My0xLjQ3OSwxMC43MTYtNC40MzhsNjQuMzIyLTY0LjMyNkM0MzQuMTUzLDM0OS44NzIsNDM0LjE1MywzNDAuMjgyLDQyOC4yMzEsMzM0LjM1OXoiIGZpbGw9IiM2NjY2NjYiLz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K);
	background-size: contain;
	background-position: center top;
	background-repeat: no-repeat;
	cursor: pointer;
}
.update-time{
	width: 100%;
}
@media screen and (max-width: 1200px){
	#clock{
		background-position: left top;
	}
}
.hider{
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	border-radius: 2px;
	z-index: 1;
}
.hider:hover ~ .loading{
	box-shadow: 0px 0px 4px 2px rgba(200,0,0,0.8);
}
#conte{
	width: 95%;
	max-width: 1300px;
	padding: 1em;
	background: #fff;
}
#wpbody-content {
}
.row, .relative{
	position: relative;
}
#wpwrap #wpcontent{
	padding-left: 0;
}
#user-credentials button, .absolute, .sm-absolute, .hider{
	position: absolute;
}
@media screen and (max-width: 769px){
	#user-credentials button, .sm-absolute{
		position: relative;
	}
	#conte{
		margin-left: auto;
		margin-right: auto;
		width:100%;
	}
	#user-credentials button.save-btn{
		float:right;
		margin:auto;
		margin-top:0.7em;
		margin-bottom:0.5em;
	}
}
#user-credentials button, .centered{
	top:0;
	bottom:0;
	right:0;
	left:0;
	margin:auto;
}
#user-credentials button{
	left:0;
	right: initial;
}
.save-btn{
	height: 40px;
	width: 130px;
}
td.select, th.select{
	width:30px;
}
.center, td.select, th.select{
	text-align: center;
}
#conte td, #conte th{
	padding:2px;
}
.product-table th {
	background: #ffffcc;
}
.accordion table{
	height: auto !important;
}
.off{
	display: none;
}
.cell-content{
	max-height: 75px;
	max-width: 100px;
	overflow: hidden;
}
#catalog-table.search-on .product-table td{
	background-color: rgba(20, 255, 20, 0.1);
}
.tab{
	display: none;
	padding: 1em;
}
.tab.active{
	display: block;
}
.inline-block{
	display: inline-block;
}
#conte .nav-tab{
	box-shadow: none;
}
.product-table th.select input{
	display: none;
}
#conte .loading{
	background-image: url("https://upload.wikimedia.org/wikipedia/commons/c/cd/Vector_Loading_fallback.gif");
	background-position: center center;
	background-repeat: no-repeat;
	background-size: contain;
	border: none;
}
#conte input.loading:before{
	visibility: hidden;
}
#user-credentials-list>div{
	margin-bottom: 5px;
}
.text-right, #conte label{
	text-align: right;
}
#conte label{
	font-weight: normal;
}
#settings .set{
	background: #fff;
}
.ui-dialog .ui-dialog-content.padded, .padded{
	padding: 1em;
}
.product-field input{
	margin-right: 1em;
}
.half-margin{
	margin: 0.25em;
}
.margin{
	margin: 1em;
}
.no-pad-bottom{
	padding-bottom: 0;
}
.half-padded, #settings .set{
	padding: 0.5em;
}
.float-right{
	float:right;
}
.product-thumb{
	max-height: 40px;
	max-width: 40px;
}

th .cell-content.verify-wc-presence {
	position: relative;
	background: none;
}
th .cell-content.verify-wc-presence:after {
		content: "wc"
}
.cell-content.verify-wc-presence {
	position: absolute;
	width: 20px;
	height: 20px;
	background: #fe6;
	border-radius: 100%;
}
.cell-content.verify-wc-presence[data-in-wc="false"]{
	background-color: red;
}
.cell-content.verify-wc-presence[data-in-wc="true"]{
	background-color: green;
}
@media screen and (max-width: 992px) {
	#product-actions .float-right{
		float: left;
	}
}

#conte button.loading{
	border: 2px solid transparent
}
#conte button.loading .after {
	position:absolute;
	bottom:0;
	left:0;
	height:3px;
	background: #aaf;
	background: repeating-linear-gradient(45deg, transparent, transparent 3px, rgba(100,100,255, 0.5) 5px, rgba(100,100,255, 0.5) 10px);
	background-size: 120%;
	background-position: -5px;
	animation: progress 1s linear infinite;
	border-radius: 100%;
	transition: width 0.5s;
}
@keyframes progress {
	from {background-position: -10px;}
	to {background-position: 3px;}
}
#update-timer:after, #single-update-timer:after{
content:	"next "attr(data-update-type)"update will be fired in "
			attr(data-next-update-weeks)" weeks, "
			attr(data-next-update-days)" days, "
			attr(data-next-update-hours)" hours, "
			attr(data-next-update-minutes)" minutes and "
			attr(data-next-update-seconds)" seconds";
}

.no-margin-right{
	margin-right: 0;
}
#update-from-button[value]:after{
	background: #0f0;
}
#update-from-button[value="0"]:after, #update-from-button:after{
	content: "";
	border-radius: 100%;
	width: 10px;
	height: 10px;
	display: inline-block;
	margin-left: 3px;
	background: #f00;
}
</style>
<script type="text/javascript">
window.mdt = (function($){
	
	"use strict"
	
	var instance = new Mdt(),
		userfullProductAttributes = {
			"id": dummy,
			"name": dummy,
			"price": dummy,
			"priceRes": dummy,
			"priceBase": dummy,
			"images": function(a){return '<img class="product-thumb" src="'+(a && a[0] || '')+'">';},
			"groupProducts": dummy,
			"status": dummy,
			"quantity": dummy,
		}, productTableLoading = false, actionFiredByButton = false, lastStatus = [];
	
	$(function(){
		
		var updateTimer = document.querySelector('#update-timer');
		window.setInterval(function(){
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : '[{"getNextScheduleTime": ["array"]},{"getStatus": []}]'
				},
				success : function(r){
					updateTimer.dataset.nextUpdateWeeks = r[0]['weeks'] || '';
					updateTimer.dataset.nextUpdateDays = r[0]['days'] || '';
					updateTimer.dataset.nextUpdateHours = r[0]['hours'] || '';
					updateTimer.dataset.nextUpdateMinutes = r[0]['minutes'] || '';
					updateTimer.dataset.nextUpdateSeconds = r[0]['seconds'] || '';
					manageStatus(r[1]);
				},
			})
		}, 2000)
		
		$("#search").on("focus",function(){
			$("#catalog-table").addClass('search-on');
			$(".accordion").accordion( "option", "active", 0 );
		});
		
		$("#search").on("blur",function(){
			$("#catalog-table").removeClass('search-on');
			if($(this).val())
				return;
			$(".accordion").accordion( "option", "active", false );
		});
		
		$("#search").on("keyup", function(e){
			if(!e.srcElement.value){
				return $('.product-table tr').removeClass('off')
			};
			$('.product-table tr[data-msxid]:not([data-msxid*="'+e.srcElement.value+'"])').addClass('off');
			$('.product-table tr[data-msxid*="'+e.srcElement.value+'"]').removeClass('off')

		});
		
		$("#main-tab .nav-tab").on("click", function(e){
			e.preventDefault();
			$("#conte .tab, #conte .nav-tab").removeClass("nav-tab-active active");
			$("#"+this.dataset["tab"]).addClass("active");
			$(this).addClass("nav-tab-active");
			if( (this.dataset["tab"] == "product-catalog") && (!productTableLoading) ){
				initProductTable();
				productTableLoading = true;
			}
		})
		
		$("#select-all").on("click", function(){
			this.classList.add('loading');
			var self = this;
			window.setTimeout(function(){
				$( "#catalog-table .category-row>.select>input:"+(!self.checked? "checked":"not(:checked)") ).click();
				window.setTimeout(function(){self.classList.remove('loading');}, 0)
			}, 0);
		});
		
		$("#catalog-table").delegate('.category-row>.select>input[type="checkbox"]', "click", function(){
			this.classList.add('loading');
			var self = this;
			window.setTimeout(function(){
				$("#product-table-" + self.value + " tr:not(.off) td input:"+(!self.checked? "checked":"not(:checked)")).click();
				window.setTimeout(function(){self.classList.remove('loading');}, 0)
			}, 0);
		})
		
		$("#save-credentials").on("click", function(){
			this.classList.add('loading');
			var	self = this,
				usern = document.querySelector("#input-usern"),
				passwd = document.querySelector("#input-passwd"),
				BP = document.querySelector("#input-BP"),
				url = document.querySelector("#input-url"),
				mdt = [
					{"setSharedOption":["usern", usern.value]},
					{"setSharedOption":["passwd", passwd.value]},
					{"setSharedOption":["BP", BP.value]},
					{"setSharedOption":["url", url.value]}
				]
			window.setTimeout(function(){
					$.ajax({
						url : ajaxurl,
						type : "post",
						data : {
							action : 'mdt',
							mdt : JSON.stringify(mdt),
						},
						success : succesfullSaveCredential,
						error : errorSaveCredential
					});
			}, 0);
			function succesfullSaveCredential(r){
				updateCredentialFields(r, "successfully updated");
			}
			function errorSaveCredential(r){
				updateCredentialFields(r, "failed update");
			}
			
			function updateCredentialFields(r, text){
				usern.value = r[0];
				passwd.value = r[1];
				BP.value = r[2];
				self.classList.remove('loading');
				$( "<div class=\"padded\">"+text+"</div>" ).dialog({
					autoOpen: true,
					resizable: false,
					modal: true,
					title: 'Update feedback',
					buttons: [ {
						text: "OK",
						click: function() {
							$( this ).dialog( "close" );
						}
					} ]
				});
			}
		});
		
		$("#action-accordion").accordion( {active: 1} );
		$("#save-fields").on("click", function(){
			this.classList.add('loading');
			var self = this;
			window.setTimeout(function(){
				var checkboxes = $('.product-field input:checked'),
					fields = [];
				for(var i in checkboxes){
					if(checkboxes[i] instanceof Element){
						fields.push(checkboxes[i].value)
					}
				}
				fields = JSON.stringify(fields);
				$.ajax({
					url : ajaxurl,
					type : "post",
					data : {
						action : 'mdt',
						mdt : '[{"setSharedOption":["fields",'+fields+']}]',
					},
					success : succesfullSaveFields,
					error : errorSaveFields
				});
			}, 0);
			function succesfullSaveFields(r){
				updateFieldsCheckboxes(r, "successfully updated");
			}
			function errorSaveFields(r){
				updateFieldsCheckboxes(r, "failed update");
			}
			
			function updateFieldsCheckboxes(r, text){
				self.classList.remove('loading');
				$( "<div class=\"padded\">"+text+"</div>" ).dialog({
					autoOpen: true,
					resizable: false,
					modal: true,
					title: 'Update feedback',
					buttons: [ {
						text: "OK",
						click: function() {
							$( this ).dialog( "close" );
						}
					} ]
				});
			}
		})
		$("#auto-update,#save-products,#update-now,#auto-remove,#delete-products,#stop,#unlock").on("click", function(){
			this.classList.add('loading');
			var self = this;
			actionFiredByButton = true;
			window.setTimeout(function(){
				var values = [], fields = [];
				$("#catalog-table [data-msxid] input:checked").each(function() {
					values.push(this.value);
				})
				$("#product-actions .product-field input:checked").each(function() {
					fields.push(this.value);
				})
				$.ajax({
					url : ajaxurl,
					type : "post",
					data : {
						action : 'mdt',
						mdt : self.value,
						msxids: values,
						mdtfield: fields
					},
					success : succesAction,
					error : errorAction,
					beforeSend: function(jqxhr){
						manageStatus({'status': self.id})
					}
				});
				//log(jqxhr)
			})
			function succesAction(r){
				if(r && r.error)
					return errorAction(r);
				r && updateFieldsCheckboxes(r, self.value+" successfully done");
			}
			function errorAction(r){
				log(r);
				updateFieldsCheckboxes(r, self.value+" failed");
			}
			function updateFieldsCheckboxes(r, text){
				self.classList.remove('loading');
				$( "<div class=\"padded\">"+text+"</div>" ).dialog({
					autoOpen: true,
					resizable: false,
					modal: true,
					title: 'Update feedback',
					buttons: [ {
						text: "OK",
						click: function() {
							$( this ).dialog( "close" );
							window.location.href = window.location.href;
						}
					} ]
				});
			}
		})
		manageStatus(<?php $mdt->exec('getStatus',[],true) ?>);
		
		if(lastStatus['status']){
			busyDialog()
		}
		$("#hider").on('click', function(){
			busyDialog();
		})
		
		function busyDialog(){
			return $( "<div class=\"padded\">The pluggin is busy in '"+lastStatus['status']+"'</div>" ).dialog({
				autoOpen: true,
				resizable: false,
				modal: true,
				title: 'Plugin busy',
				buttons: [ {
					text: "OK",
					click: function() {
						$( this ).dialog( "close" );
					}
				} ]
			})
		}
		
		$("#save-update-fields").on("click", function(){
			
			var weeks = document.querySelector("#update-weeks").value,
				days = document.querySelector("#update-days").value,
				hours = document.querySelector("#update-hours").value,
				minutes = document.querySelector("#update-minutes").value,
				total = (60 * minutes)+(60 * 60 * hours)+(60 * 60 * 24 * days)+(60 * 60 * 24 * 7 * weeks),
				args = [ total ],
				time = parseInt(document.querySelector('#update-from-button').value),
				schedulesettings = {autoadd: document.querySelector('#autoadd').checked, autoremove: document.querySelector('#autoremove').checked}
			if(time){
				args.push((time/1000) + ((new Date()).getTimezoneOffset()*60));
			} else{
				args.push(total + (new Date()*1)/1000);
			}
			args = JSON.stringify([{setScheduleFrequency: args}, {setSharedOption: ['updateSettings',schedulesettings]}]);
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : args,
				},
				success : function(r){console.log(r)},
			});
		})
		
		$("#clear-update-fields").on('click', function(){
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : 'clearSchedule',
				},
				success : function(r){console.log(r)},
			});
		})
		
		$("#clock").on('click', function(){
			var self = this, container = $("<div></div>"), datepicker = container.datepicker({
				}),
				timeh = $("<input id=\"hour\" class=\"update-time-field\" type=\"number\" max=\"23\" min=\"0\">"),
				timem = $("<input id=\"minutes\" class=\"update-time-field\" type=\"number\" max=\"59\" min=\"0\">"),
				time = $("<div class=\"text-right margin no-margin-right\">time </div>");
				
			time.append(timeh,' : ',timem);
			container.append(datepicker);
			container.append(time);
			var time = <?php echo $nextsingleupdatetime*1000; ?> ||  Date.now()
			var date = new Date(time);
			datepicker.datepicker("setDate", date);
			timeh.val( parseInt(time/(60 * 60 * 1000))%24 - parseInt(date.getTimezoneOffset()/60) );
			timem.val( parseInt(time/(60 * 1000))%60 );
			log(time)
			container.dialog({
				autoOpen: true,
				resizable: false,
				modal: true,
				title: 'One shot update',
				width: 'auto',
				buttons: [ {
					text: "Save",
					click: function() {
						$( this ).dialog( "close" );
						$.ajax({
							url : ajaxurl,
							type : "post",
							data : {
								action : 'mdt',
								mdt : 'setOneShotSchedule',
								arguments: [(new Date(datepicker.val())*1+(((timeh.val()*60*60*1000)+(timem.val()*60*1000))))/1000]
							}
						});
					}
				},{
					text: "Clear",
					click: function() {
						$( this ).dialog( "close" );
						$.ajax({
							url : ajaxurl,
							type : "post",
							data : {
								action : 'mdt',
								mdt : 'clearOneShotSchedule'
							}
						});
					}
				},{
					text: "Cancel",
					click: function() {
						$( this ).dialog( "close" );
					}
				}]
			});
			
		})
		$("#reset-update-fields").on("click", function(){
			var self = this;
			this.classList.add('loading')
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : 'getScheduleFrequency',
					arguments: 'array'
				},
				success : function(r){
					document.querySelector("#update-weeks").value = r["weeks"];
					document.querySelector("#update-days").value = r["days"];
					document.querySelector("#update-hours").value = r["hours"];
					document.querySelector("#update-minutes").value = r["minutes"];
					self.classList.remove('loading')
				}
			});
		})
		$("#update-from-button").on("click", function(){
			var self = this, container = $("<div></div>"), datepicker = container.datepicker({
				}),
				timeh = $("<input id=\"hour\" class=\"update-time-field\" type=\"number\" max=\"23\" min=\"0\">"),
				timem = $("<input id=\"minutes\" class=\"update-time-field\" type=\"number\" max=\"59\" min=\"0\">"),
				time = $("<div class=\"text-right margin no-margin-right\">time </div>");
				
			time.append(timeh,' : ',timem)
			
			var date = new Date()
			datepicker.datepicker('setDate', $.datepicker.parseDate('@', ((($(self).val()||0) ) || date*1)));
			var seconds = parseInt( ($(self).val() || Date.now()) / 1000);
			var minutes = parseInt(seconds / 60)%60;
			var hours = (parseInt(seconds / (60*60))%24 - parseInt(date.getTimezoneOffset()/60));
			timeh.val(hours);
			timem.val(minutes);
			container.append(datepicker);
			container.append(time);
			
			
			container.dialog({
				autoOpen: true,
				resizable: false,
				modal: true,
				width: 'auto',
				title: 'Date picker',
				buttons: [ {
					text: "OK",
					click: function() {
						$( this ).dialog( "close" );
						self.value = new Date(datepicker.val())*1+(((timeh.val()*60*60*1000)+(timem.val()*60*1000))) - (new Date()).getTimezoneOffset()*60*1000
					}
				},{
					text: "Clear",
					click: function() {
						$( this ).dialog( "close" );
						self.value = "0";
					}
				},{
					text: "Cancel",
					click: function() {
						$( this ).dialog( "close" );
					}
				}]
			});
		})
		$('#save-tracking').on('click', function(){
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : 'setTrackingPage',
					arguments: document.querySelector('#select-tracking').value
				},
				success : function(r){
					$('<div></div>').dialog({
						autoOpen: true,
						resizable: false,
						modal: true,
						width: 'auto',
						title: 'Tracking saved',
						buttons: [{
							text: "OK",
							click: function() {
								$( this ).dialog( "close" );
								window.location.href = window.location.href;
							}
						}]
					});
				}
			});
		})
		
		$('#create-tracking').on('click', function(){
			$.ajax({
				url : ajaxurl,
				type : "post",
				data : {
					action : 'mdt',
					mdt : 'createTrackingPage',
					arguments: document.querySelector('#create-tracking-title').value
				},
				success : function(r){
					$('<div></div>').dialog({
						autoOpen: true,
						resizable: false,
						modal: true,
						width: 'auto',
						title: 'Tracking saved',
						buttons: [{
							text: "OK",
							click: function() {
								$( this ).dialog( "close" );
								window.location.href = window.location.href;
							}
						}]
					});
				}
			});
		})
		
	});

	return instance;
	
	function Mdt(){ }
		
	function initProductTable(){
		$.ajax({
			url : ajaxurl,
			type : "post",
			data : {
				action : 'mdt',
				mdt : 'fullCatalog',
			},
			success : function(r){return fillCatalogTable(r)},
			error : log
		});
	}

	
	function printLostInWc(lostInWc){
		var str = "", fullstr = "<tr class=\"category-row\">";
		for(var i in lostInWc){
			str += "<tr data-msxid=\""+i+"\"><td class=\"select\"><input type=\"checkbox\" value=\""+i+"\"></td><td>"
			str += "<div class=\"\">"+i+"</div></td><td><div class=\"\">"+lostInWc[i].name+"</div></td></tr>"
		}
		if(!str){
			return;
		}
		fullstr += "<td class=\"select\"><input type=\"checkbox\" value=\"lostInWc\"></td>";
		fullstr += "<td id=\"lostInWc-accordion\" class=\"accordion\"><h3>Lost in WooCommerce</h3>";
		fullstr += "<table id=\"product-table-lostInWc\" class=\"widefat striped col-xs-12 product-table\"><thead>";
		fullstr += "<tr><th class=\"select\"><input type=\"checkbox\" value=\""+i+"\"></th><th><div class=\"\">id</div></th><th><div class=\"\">name</div></th></thead><tbody>";
		fullstr += str;
		fullstr += "</tbody></table></td></tr>";
		$("#catalog-table>tbody").append(fullstr);
		$("#lostInWc-accordion").accordion({collapsible: true, active: false, animate:false})
	}
	function log(){
		console.log.apply(console, arguments);
	}

	function fillCatalogTable(r){
		var fullstr = "",
			str, tb;
		if(r.error){
			$( "<div class=\"padded\">"+r.error+"</div>" ).dialog({
				autoOpen: true,
				resizable: false,
				modal: true,
				width: '420px',
				title: r.type,
				buttons: [ {
					text: "OK",
					click: function() {
						$( this ).dialog( "close" );
					}
				} ]
			});
			return;
		}
		
		
		for(var category in r){
			if(!(tb = productTable(r[category], category)))
				continue;
			str = "";
			str += "<tr class=\"category-row\"><td class=\"select\"><input type=\"checkbox\" value=\""+category+"\"></td>";
			str += "<td class=\"accordion\"><h3>" + category + "</h3>" + tb + "</td></tr>";
			fullstr += str;
		}
		$("#catalog-table tbody").html(fullstr);
		fullstr = "";
		$("#select-all").removeClass("loading");
		$(".accordion").accordion({collapsible: true, active: false, animate:false});
		
		$.ajax({
			url : ajaxurl,
			type : "post",
			data : {
				action : 'mdt',
				mdt : 'lostInWc',
			},
			success : function(r){
				printLostInWc(r);
			},
			error : log
		});
		$.ajax({
			url : ajaxurl,
			type : "post",
			data : {
				action : "mdt",
				mdt : "getStoredProducts",
			},
			success : function(r){
				var x = [];
				for(var i in r){
					x.push( '[data-msxid="'+i+'"] div.cell-content.verify-wc-presence' )
				}
				$(x.join()).attr("data-in-wc", "true")
			},
			error : log
		})
		$("td>div.cell-content.verify-wc-presence").attr("data-in-wc", "false");		
	}
	function productTable(products, code){
		var fullstr = "",
			str, i = 0;
		fullstr = "<table id=\"product-table-"+code+"\" class=\"widefat striped col-xs-12 product-table\">";
		fullstr += "<thead>"+productRow({}, 'th', code)+"</thead><tbody>";
		for(var product in products){
			i++;
			fullstr += productRow(products[product]);
		}
		if(!i){
			return false;
		}
		fullstr += "</tbody></table>";
		return fullstr;
	}
	
	function dummy(a){return a;}
	
	function productRow(product, td, code){
		code = (product.id? "value=\""+product.id+"\"" : "");
		var	td = td || 'td',
			str = "<tr"+(product.id? (" data-msxid=\""+product.id+"\"") : "")+"><"+td+" class=\"select\"><input type=\"checkbox\" "+code+"></"+td+">", col;
		for(var i in userfullProductAttributes){
			col = userfullProductAttributes[i](product[i]);
			col = col == undefined? i : col;
			str += "<"+td+"><div class=\"cell-content\">"+col+"</div></"+td+">"
		}
		str += "<"+td+"><div class=\"cell-content verify-wc-presence\"></div></"+td+">";
		return str + "</tr>";
	}
		
	function selectdeselectall(arg, query){
		var checkboxes = $(query).children();
		for (var i in checkboxes)
			if(checkboxes[i] instanceof Element)
				checkboxes[i].checked = arg;
	}
	function manageStatus(s){
		s = s || [];
		if(s.error){
			return $( "<div class=\"padded\">"+s.error+"</div>" ).dialog({
				autoOpen: true,
				resizable: false,
				modal: true,
				width: '420px',
				title: s.type,
				buttons: [ {
					text: "OK",
					click: function() {
						$( this ).dialog( "close" );
					}
				} ]
			});
		}
		$("button.action.loading").removeClass('loading');
		var elm = s['status'] && $( 'button#'+(s['status'].replace(/\s+/, "-")) ),
			actioncompleted = (((s["actual"] || 0)/(s["total"] || 100))*100) + "%",
			progressBar = $(elm).find(".after");
			
		elm && elm.addClass('loading');
		if( (s['status'] === 'stop') || !s['status']){
			$(".action.loading .after").remove();
			if((!actionFiredByButton) && (lastStatus['status']))
				$( "<div class=\"padded\">Operation complete</div>" ).dialog({
					autoOpen: true,
					resizable: false,
					modal: true,
					width: '420px',
					title: 'Action '+lastStatus['status']+( s['status'] === 'stop'?' stopped' : ' completed' ),
					buttons: [ {
						text: "OK",
						click: function() {
							$( this ).dialog( "close" );
							window.location.href = window.location.href;
						}
					} ]
				});
			$('#hider').removeClass('hider');
			lastStatus = [];
			return;
		}
		$('#hider').addClass('hider');
		lastStatus = s;
		if(!progressBar.length){
		
			$(elm).append("<div class=\"after\"></div>");
			progressBar = $(".action.loading .after");
		}
		progressBar.css('width', actioncompleted);
	}
})(jQuery);
</script>
<div id="conte" class="wrap sm-center">
	<h2 id="main-tab" class="nav-tab-wrapper">
		<a id="settings-tab" class="nav-tab nav-tab-active" data-tab="settings" href="#settings">
			Settings
		</a>
		<a id="product-catalog-tab" class="nav-tab" data-tab="product-catalog" href="#product-catalog">
			Catalog
		</a>
		<a id="doc-tab" class="nav-tab" data-tab="documentation" href="#documentation">
			Documentation
		</a>
		<a id="about-tab" class="nav-tab" data-tab="about" href="#about">
			About
		</a>
	</h2>
	<div id="settings" class="tab active">
		<div class="row">
			<div id="user-credentials" class="col-lg-6 col-xs-12 set">
				<h3 class="row center">User credentials</h3>
				<div class="row">
					<div id="user-credentials-list" class="col-xs-12 col-sm-8">
						<div class="row">
							<label class="col-xs-3">usern</label>
							<input class="col-xs-8" type="text" id="input-usern" value="<?php echo $mdt->getSharedOption('usern'); ?>">
						</div>
						<div class="row">
							<label class="col-xs-3">BP</label>
							<input class="col-xs-8" type="text" id="input-BP" value="<?php echo $mdt->getSharedOption('BP'); ?>">
						</div>
						<div class="row">
							<label class="col-xs-3">url</label>
							<input class="col-xs-8" type="text" id="input-url" value="<?php echo $mdt->getSharedOption('url'); ?>">
						</div>
						<div class="row">
							<label class="col-xs-3">passwd</label>
							<input class="col-xs-7" type="password" id="input-passwd" value="<?php echo $mdt->getSharedOption('passwd') ?>">
							<div class="col-xs-1" style="text-align: center">
								<input  type="checkbox" onclick="document.querySelector('#input-passwd').type=(this.checked?'text':'password')">
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-4 col-sm-push-8 sm-absolute" style="height:100%">
						<button id="save-credentials" class="save-btn ui-button">save</button>
					</div>
				</div>
			</div>
			<div id="auto-update-place" class="col-lg-6 col-xs-12 set">
				<h3 class="row center">Automatic update</h3>
				<div class="row">
					<div class="col-xs-8">
						<div class="row">
							<div class="col-xs-12">Recurring updates</div>
							<div class="col-xs-6 col-sm-3">
								<div class="row">
									<div class="col-xs-12">
										Weeks
									</div>
									<div class="col-xs-12">
										<input type="number" id="update-weeks" class="update-time" min="0" value="<?php echo $scheduletime['weeks'] ?>">
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-3">
								<div class="row">
									<div class="col-xs-12">
										Days
									</div>
									<div class="col-xs-12">
										<input type="number" id="update-days" class="update-time" max="6" min="0" value="<?php echo $scheduletime['days'] ?>">
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-3">
								<div class="row">
									<div class="col-xs-12">
										Hours
									</div>
									<div class="col-xs-12">
										<input type="number" id="update-hours" class="update-time" max="23" min="0" value="<?php echo $scheduletime['hours'] ?>">
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-3">
								<div class="row">
									<div class="col-xs-12">
										Minutes
									</div>
									<div class="col-xs-12">
										<input type="number" id="update-minutes" class="update-time" max="59" min="0" value="<?php echo $scheduletime['minutes'] ?>">
									</div>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="row">
									<div class="col-xs-4">
										<div class="row">
											<div class="col-xs-12">
												From
											</div>
											<div class="col-xs-12">
												<button id="update-from-button" class="ui-button">pick a date</button>
												<!-- <input type="date" id="update-from" class="update-time"> -->
												<!-- <input type="time" id="update-from-time" class="update-time"> -->
											</div>
										</div>
									</div>
									<div class="col-xs-4">
										<div class="row">
											<div class="col-xs-12">
												autoadd
											</div>
											<div class="col-xs-12">
												<div class="col-xs-12"><input id="autoadd" type="checkbox"<?php if($updatesettings['autoadd']){echo ' checked="1"';} ?>></div>
											</div>
										</div>
									</div>
									<div class="col-xs-4">
										<div class="row">
											<div class="col-xs-12">
												autoremove
											</div>
											<div class="col-xs-12">
												<div class="col-xs-12"><input id="autoremove" type="checkbox"<?php if($updatesettings['autoremove']){echo ' checked="1"';} ?>></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-4">
								<button id="save-update-fields" class="ui-button">save</button>
							</div>
							<div class="col-xs-4">
								<button id="reset-update-fields" class="ui-button">reset</button>
							</div>
							<div class="col-xs-4">
								<button id="clear-update-fields" class="ui-button">clear</button>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 margin" style="margin-left:0;margin-right:0">
								<span id="update-timer" style="font-size: 0.8em;"
									data-next-update-weeks="<?php echo $nextupdate['weeks']; ?>"
									data-next-update-days="<?php echo $nextupdate['days']; ?>"
									data-next-update-hours="<?php echo $nextupdate['hours']; ?>"
									data-next-update-minutes="<?php echo $nextupdate['minutes']; ?>"
									data-next-update-seconds="<?php echo $nextupdate['minutes']; ?>"
								></span>
							</div>
							<div class="col-xs-12">
								<span id="single-update-timer" style="font-size: 0.8em;"
									data-update-type="single "
									data-next-update-weeks="<?php echo $nextsingleupdate['weeks']; ?>"
									data-next-update-days="<?php echo $nextsingleupdate['days']; ?>"
									data-next-update-hours="<?php echo $nextsingleupdate['hours']; ?>"
									data-next-update-minutes="<?php echo $nextsingleupdate['minutes']; ?>"
									data-next-update-seconds="<?php echo $nextsingleupdate['minutes']; ?>"
								></span>
							</div>
						</div>
					</div>
					<div class="col-xs-4">
						<div class="">Single update</div>
						<div class="">
							<div id="clock"></div>
						</div>
					</div>
				</div>
			</div>
			<div id="traking-page" class="col-lg-6 col-xs-12 set">
				<?php 
				$track = $mdt->getSharedOption('tracking');
				?>
				<h3>Tracking</h3>
				<div>
					<select id="select-tracking">
						<?php
						global $wpdb;
						$query = $wpdb->get_results(
							"	SELECT posts.post_title, posts.ID
								FROM $wpdb->posts as posts
								WHERE posts.post_type = 'page'
									AND posts.post_content LIKE '%[mdt_tracking]%'
								GROUP BY posts.ID
								ORDER BY posts.post_date
									DESC	"
						);
						
						foreach ($query as $value) {
							$is_selected = $track == $value->ID ? ' selected' : '';
							$title = $value->post_title;
							echo "<option$is_selected>$title</option>";
						}
						?>
					</select>
					<button id="save-tracking" class="save-btn ui-button">
						save
					</button>
				</div>
				<div>
					<input id="create-tracking-title" type="text">
						
					</input>
					<button id="create-tracking" class="save-btn ui-button">
						create new
					</button>
				</div>
			</div>
		</div>
		<div class="row">
			<div id="product-actions" class="col-xs-12 set">
				<h3 class="row center">Product actions</h3>
				<div class="row" id="action-accordion">
					<h4 class="col-xs-12">Updating fields</h4>
					<div class="col-xs-12">
					<?php 
					$fieldsMsg = [
						'post_content' => 'content',
						'post_title' => 'title',
						'post_excerpt' => 'excerpt',
						'price' => 'price',
						'regular_price' => 'max price',
						'sale_price' => 'min price',
						'price_res' => 'res price',
						'full_price' => 'full price',
						'images' => 'images',
						'videos' => 'videos',
						'product_cat' => 'category',
						'attributes' => 'attributes'
					];
					$fieldsStored = $mdt->getSharedOption('fields');
					if(!$fieldsStored){
						$fieldsStored = [];
					}
					foreach ($fieldsMsg as $key => $value) {
						$check = in_array($key, $fieldsStored)? 'checked="true"' : '';
						echo "<div class=\"product-field col-md-4 col-sm-6 col-xs-12\"><input type=\"checkbox\" value=\"$key\" $check><label>$value</label></div>";
					}
					?>
						<div class="col-xs-12 padded no-pad-bottom center"><button id="save-fields" class="save-btn ui-button">save</button></div>
					</div>
					<h4 class="col-xs-12">Actions</h4>
					<div class="col-xs-12">
						<div class="row">
							<div id="hider"></div>
							<button value="autoUpdate" class="half-padded ui-button col-xs-12 col-md-2 half-margin action" id="auto-update">auto update</button>
							<button value="saveAll" class="half-padded ui-button col-xs-12 col-md-2 half-margin action" id="save-products">save products</button>
							<button value="updateAll" class="half-padded ui-button col-xs-12 col-md-2 half-margin action" id="update-now">update now</button>
							<button value="autoRemove" class="half-padded ui-button col-xs-12 col-md-2 half-margin action" id="auto-remove">auto remove</button>
							<button value="deleteAll" class="half-padded ui-button col-xs-12 col-md-2 half-margin float-right action" id="delete-products">delete products</button>
						</div>
						<div class="row">
							<button value="stop" class="padded ui-button col-xs-12 col-md-6  half-margin" id="stop">stop</button>
							<button value="unlock" class="padded ui-button col-xs-12 col-md-5 half-margin float-right" id="unlock">unlock</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="about" class="tab">
		<div>
			Icons made by
			<a href="http://www.flaticon.com/authors/simpleicon" title="SimpleIcon">SimpleIcon</a>
			from
			<a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a>
			is licensed by
			<a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>
		</div>
	</div>
	<div id="documentation" class="tab">
		
	</div>
	<div id="product-catalog" class="tab">
		<table id="catalog-table" class="widefat striped col-xs-12">
			<thead>
				<tr>
					<th class="select"><input type="checkbox" id="select-all" class="loading"></th>
					<th>Catalog<input placeholder="search by id" id="search" type="text"></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
</main>
	<?php
}
?>