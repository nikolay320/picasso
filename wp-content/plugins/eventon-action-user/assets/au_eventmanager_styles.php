<?php
// styles for event manager thats printed on to the page
?>
.evcal_btn.evoau, .evoau_submission_form.loginneeded .evcal_btn{
	border-radius: 4px;
	border: none;
	color: #ffffff;
	background: #237ebd;
	text-transform: uppercase;
	text-decoration: none;
	border-radius: 4px;
	border-bottom: none;
	font: bold 14px arial;
	display: inline-block;
	padding: 8px 12px;
	margin-top: 4px
}
.evcal_btn.evoau:hover, .evoau_submission_form.loginneeded .evcal_btn:hover{color: #fff; opacity: 0.6;}
.eventon_actionuser_eventslist{
	border:1px solid #E2E2E2;
	border-radius:5px; overflow:hidden;
}
.eventon_actionuser_eventslist p{
	padding:5px 10px; margin: 0;
	border-bottom:1px solid #E2E2E2; position:relative;
}
.eventon_actionuser_eventslist p:hover{
	background-color: #FCF7F3;
}
.eventon_actionuser_eventslist p span{
	opacity: 0.7;
	font-style: italic;	
	display: block;
	font-size: 11px;
	text-transform: uppercase;		
}
.eventon_actionuser_eventslist p subtitle{font-weight:bold; text-transform:uppercase;}
.eventon_actionuser_eventslist p span em{
	padding:1px 5px 2px; background-color:#EAEAEA; display:inline-block; border-radius:5px; margin-bottom:5px;
}
.eventon_actionuser_eventslist .editEvent, .eventon_actionuser_eventslist .deleteEvent{
	opacity: 0.8; z-index:1; text-align:center;
	position:absolute;
	right:0px; top:0px;
	height:100%;
	width:50px;
	background-color:#3d3d3d;
	padding-top:30px;
	color:#ffffff;
}
.eventon_actionuser_eventslist .deleteEvent{
	right:50px;
	background-color:#E27D7D;
}
.eventon_actionuser_eventslist .editEvent:hover, .eventon_actionuser_eventslist .deleteEvent:hover{
	text-decoration: none; opacity: 1; color:#fff}

.eventon_actionuser_eventslist .editEvent:before, .eventon_actionuser_eventslist .deleteEvent:before{
	font-family: evo_FontAwesome;
}
.eventon_actionuser_eventslist em{clear: both;}
h3.evoauem_del_msg{padding: 4px 12px; border-radius: 5px; text-transform: uppercase;}
@media (max-width: 480px){
	.eventon_actionuser_eventslist .editEvent, .eventon_actionuser_eventslist .deleteEvent{
		width:30px;
	}
	.eventon_actionuser_eventslist .deleteEvent{right:30px;}
}