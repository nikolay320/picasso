jQuery(document).ready(function($) {


	$(".new_idea .activity-header a:nth-child(2)").each(function(i, v){


		var regex = /\?p=[0-9]*/g; 
		var input = $(this).attr('href'); 
		if(regex.test(input)) {
		  var matches = input.match(regex);
		  for(var match in matches) {
		  } 
		} else {
		}

		var num_p_id = matches[match];
		num_p_id = num_p_id.replace("?p=", "");

		var dom_data = $(this);

		console.log(dom_data);

		$.post(window.location.href, {num_p : num_p_id}, function (data) {

			dom_data.html(" " + data);

		})



	});

	///question answer start

	$(".sabai-questions-add-answer-form input[type='submit']").click(function(evt){

		//evt.preventDefault();
		//console.log("test");
		var id_div_class = $(".sabai-entity-bundle-name-questions.sabai-entity-type-content").attr("id");
		var ques_user = $(".sabai-questions-main .sabai-user").attr("href");
		var title = $(".page-title").html();
		var receiver = plugin_data.receiver;

		console.log(ques_user);

		$.post(receiver, {
			post_id_class : id_div_class, 
			ques_user_link: ques_user, 
			noti: 1, 
			title: title,
			type: 'qAns',
			url: window.location.href,
		}, function(suc){
			//console.log(suc);
		});

	});

	///question answer end

//question answer comment start

	window.sabaiCommentsCommentNotify = function(id) {

var str = id;
var theObj = str.match(/\d+/);
var arr = $.makeArray( theObj );
var pid = arr[0];

	setTimeout(function(){
		console.log("time out");
	//console.log($(".sabai-comment-addcomment .sabai-btn.sabai-btn-primary"));
	$(".sabai-comment-addcomment .sabai-btn.sabai-btn-primary").click(function() {


		var userHref = $("#sabai-entity-content-"+pid+" .sabai-user").attr("href");


		var id_div_class = $(".sabai-entity-bundle-name-questions.sabai-entity-type-content").attr("id");
		var ques_user = userHref;
		var title = $(".page-title").html();
		var receiver = plugin_data.receiver;

		$.post(receiver, {
			post_id_class : id_div_class, 
			ques_user_link: ques_user, 
			noti: 1, 
			title: title,
			type: 'ansCom',
			url: window.location.href,
		}, function(suc){
			//console.log(suc);
		});

	});

	}, 3500)

		return 0;
	}

	$.each($(".sabai-questions-comments  .sabai-comment-comments-actions a"), function(i, v) {

	var commentsCommentPrevAct = $(this).attr("onclick");
	var id = $(this).attr("id");
	$(this).attr("onclick", " window.sabaiCommentsCommentNotify('"+id+"'); " + commentsCommentPrevAct);



	});
///ques ans comment end

///ques marked start

window.sabaiAnsAcc = function (id) {

	var pid = id;

		//$(".quesAccNoti-"+pid).click(function() {

		var userHref = $("#sabai-entity-content-"+pid+" .sabai-user").attr("href");


		var id_div_class = $(".sabai-entity-bundle-name-questions.sabai-entity-type-content").attr("id");
		var ques_user = userHref;
		var title = $(".page-title").html();
		var receiver = plugin_data.receiver;

			$.post(receiver, {
			post_id_class : id_div_class, 
			ques_user_link: ques_user, 
			noti: 1, 
			title: title,
			type: 'ansAcp',
			url: window.location.href,
		}, function(suc){
			//console.log(suc);
		});

	//});

	return 0;
};

$(".sabai-entity-links .fa.fa-check-circle").parent().addClass("qMarkAcc");

$.each($(".qMarkAcc"), function(i, v){

	var annAccPrevAct = $(this).attr("onclick");

	//$(this).attr("href", "#"); //temp, disable it

	var remote_url = $(this).attr("data-sabai-remote-url");


		var str = remote_url;
		var useRegex = /[(0-9)+.?(0-9)*]+/igm;
		var resultObj = useRegex.exec(str);

		var arr = $.makeArray( resultObj );
		var pid = arr[0];

	$(this).addClass("quesAccNoti-"+pid);


	$(this).attr("onclick", " window.sabaiAnsAcc('"+pid+"'); " + annAccPrevAct);

});


///ques marked end

///dir review start
//on prev page
var userLink = $(".sabai-directory-main .sabai-user").attr("href");
var prevDirBtnVal = $(".sabai-entity-bundle-name-directory_listing .sabai-directory-btn-review").attr("href");
var postID_class = $(".sabai-entity.sabai-entity-type-content.sabai-entity-bundle-name-directory-listing").attr("id");

$(".sabai-entity-bundle-name-directory_listing .sabai-directory-btn-review").attr("href", prevDirBtnVal+"?postID_class="+postID_class+"&author_link="+userLink);
//on post page



	$(".sabai-content-btn-add-directory-listing-review").click(function() {



	var postid_class = $("#postID_class").data("postid_class");

	var author_link = $("#author_link").data("author_link");

		var id_div_class = postid_class;
		var ques_user = author_link;
		var title = $(".sabai-form-field-label + .sabai-entity-permalink.sabai-entity-bundle-type-directory-listing").text();
		var receiver = plugin_data.receiver;

			$.post(receiver, {
			post_id_class : id_div_class, 
			ques_user_link: ques_user, 
			noti: 1, 
			title: title,
			type: 'dRev',
			url: window.location.href,
		}, function(suc){
			//console.log(suc);
		});

			return 0;

	});

///dir review end

///dir review comment start
	window.sabaiDirRevComment = function(id) {

var str = id;
var theObj = str.match(/\d+/);
var arr = $.makeArray( theObj );
var pid = arr[0];


	var postid_class = $("#postID_class").data("postid_class");

	var author_link = $("#author_link").data("author_link");

		var id_div_class = postid_class;
		var ques_user = author_link;
		var title = $(".sabai-form-field-label + .sabai-entity-permalink.sabai-entity-bundle-type-directory-listing").text();



	setTimeout(function(){
		console.log("time out");
	//console.log($(".sabai-comment-addcomment .sabai-btn.sabai-btn-primary"));
	$(".sabai-comment-addcomment .sabai-btn.sabai-btn-primary").click(function() {

		var userHref = $("#sabai-entity-content-"+pid+" .sabai-user").attr("href");
		var id_div_class = $(".sabai-entity-type-content.sabai-entity-bundle-name-directory-listing").attr("id");
		var ques_user = userHref;
		var title = $(".page-title").html();
		var receiver = plugin_data.receiver;

		$.post(receiver, {
			post_id_class : id_div_class, 
			ques_user_link: ques_user, 
			noti: 1, 
			title: title,
			type: 'dCom',
			url: window.location.href,
		}, function(suc){
			//console.log(suc);
		});

	});

	}, 3500)

		return 0;
	}

	$.each($(".sabai-directory-comments .sabai-comment-comments-actions a"), function(i, v) {
	
	var commentsCommentPrevAct = $(this).attr("onclick");
	var id = $(this).attr("id");
	$(this).attr("onclick", " window.sabaiDirRevComment('"+id+"'); " + commentsCommentPrevAct);



	});


///dir review comment end

	/*var commentsCommentPrevAct = $(".sabai-comment-comments-actions a").attr("onclick");

	$(".sabai-comment-comments-actions a").attr("onclick", " window.sabaiCommentsCommentNotify(); " + commentsCommentPrevAct);

	var commentsCommentPrevAct = $(".sabai-comment-comments-actions a").attr("onclick");

	console.log(commentsCommentPrevAct);*/




	
});