<script>
jQuery(document).on('ready', function($){
	// show download text on download font-awesome icon
	jQuery(".dw-hover").hover(function(){
		jQuery(this).append(jQuery("<span>download</span>"));
	}, function() {
		jQuery(this).find("span:last").remove();
	});
	
	// show upload text on upload font-awesome icon
	jQuery(".up-hover").hover(function(){
		jQuery(this).append(jQuery("<span style='padding-right: 3px;margin-top:-3px;'>upload</span>"));
		jQuery('.cloud-upload').css('float','right');
	}, function() {
		jQuery(this).find("span:last").remove();
	});
	
	// fancybox initialization
	jQuery('.fancybox').fancybox({
		openEffect  : 'none',
		closeEffect : 'none',

		prevEffect : 'none',
		nextEffect : 'none',

		closeBtn  : true,
		helpers : {
			thumbs : {
				width  : 50,
				height : 50
			},
			title : {
				type : 'inside'
			},
			buttons	: {}
		},
		afterLoad : function() {
			//this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
		}
	});
	
	// slick slider for sliding albums
	jQuery(".albums").slick({
		dots: true,
		infinite: true,
		slidesToShow: 5,
		slidesToScroll: 2,
	});
	
});

//function used to slideshow the album photos
function getAlbumPhotoes(album_id){
	// remove previous album images
	jQuery('.fancybox').remove();
	toggleLoading(true);
	// ajax to call url to fatch images of selected album
	jQuery.ajax({
		url: "album_photoes.php",
		method: 'POST',
		data: {album_id:album_id},
		success: function(response){
			var pictures = JSON.parse(response);
			// loop to append all the images with anchor tag and source to "albumFeeds" div
			for (var i = 0; i <= pictures.length - 1; i++) {
				var div = jQuery("<a data-fancybox-group='gallery' class='fancybox'><img class='album_pics' src=" + pictures[i] + " /></a>");
				div.appendTo($("#albumFeeds"));
			}
			// add id to first image of album for auto select
			jQuery('.fancybox').first().attr('id','first_img');
			// trigger click for automatically click on first image of album
			jQuery("#first_img").trigger("click");
			jQuery("#albumFeeds").css('display','none');
			toggleLoading(false);
		},
		error: function(error){
			console.log("Something went wrong...");
		}
	});
}

// ajax loader toggling
function toggleLoading(loader){
	if(loader == true){
		jQuery('#loading').css('display','block');
	}else if(loader == false){
		jQuery('#loading').css('display','none');
	}
}

// function used for downloading single album
function downloadAlbum(user_id,album_id,album_name){
	//location.href="downloadAlbums.php?album_id=" + album_id;
	toggleLoading(true);
	url = "downloadAlbums.php?album_id=" + album_id;
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		try{
			if (this.readyState == 4 && this.status == 200) {
				toggleLoading(false);
				// show bootstrap alert on success
				var url = '<a href="user_space/' + user_id + '/' + album_name + '.zip">download here</a>';
				var str = '<div class="alert alert-success" style="padding:9px 0 9px 12px;"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;<b>success!</b> album <b>' + album_name + '</b> downloaded successfully. <span class="attch-header"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;' + url + '</span></div>';
				
				jQuery('#user_info').html(str);
				//alert('album downloaded successfully');
			}
		}catch(err){
				console.log(err.message);
				toggleLoading(false);
				// show bootstrap alert 
				var str = '<div class="alert alert-danger" style="padding:9px 0 9px 12px;"><i class="icon-envelope">Something went wrong.</div>';
				jQuery('#user_info').html(str);
			}
	};
	xhttp.open("GET", url, true);
	xhttp.send();
}

function getSelectedValues(album_id){
	// generate comma separated value of selected albums
	var aid = jQuery('#dSelect').val();
	if(aid == '')
		aid = album_id;
	else
		aid = aid + ',' + album_id;
	// store it to the hidden variable
	jQuery('#dSelect').val(aid);
}

// function used for selected album download
function downloadSelected(){
	var album_ids = jQuery('#dSelect').val();
	if(album_ids == '' || album_ids == null || album_ids.length <= 0){
		alert('Please select album....'); // if null
		return false;
	}
	//location.href="downloadSelectedAlbums.php?album_id=" + album_ids + "&allAlbums=false";
	var user_id = <?php echo $_SESSION['user_id'] ?>;
	//var albumIds = album_ids.toString().split(',');
	toggleLoading(true);
	jQuery.ajax({
		url: "downloadSelectedAlbums.php",
		method: 'POST',
		data: {album_id:album_ids,allAlbums:'false'},
		success: function(response){
			try{
				var result = JSON.parse(response);
				console.log(result);
				if(result['success'] == 'true'){
					toggleLoading(false);
					//alert('Selected albums downloaded...');
					// show bootstrap alert on success
					var url = "<a href='user_space/" + user_id + "/selected-albums.zip'>download here</a>";
					var str = '<div class="alert alert-success" style="padding:9px 0 9px 12px;"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;<b>success!</b> ' + result['count'] + ' albums downloaded successfully. <span class="attch-header"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;' + url + '</span></div>';
					jQuery('#user_info').html(str);
				}
			}catch(err){
				console.log(err.message);
				toggleLoading(false);
				// show bootstrap alert
				var str = '<div class="alert alert-danger" style="padding:9px 0 9px 12px;"><i class="icon-envelope">something went wrong.</div>';
				jQuery('#user_info').html(str);
			}
		},
		error: function(error){
			console.log("Something went wrong...");
		}
	});
}

// function used for downloading all the albums
function downloadAllAlbums(){
	//downloadAllTheAlbums('downloadScript');
	//location.href="downloadSelectedAlbums.php?album_id=&allAlbums=true&type=downloadScript";
	var user_id = <?php echo $_SESSION['user_id'] ?>;
	toggleLoading(true);
	url = "downloadSelectedAlbums.php?album_id=&allAlbums=true&type=downloadScript";
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200){
			toggleLoading(false);
			try{				
				//alert('All albums downloaded...');
				// show bootstrap alert on success
				var url = "<a href='user_space/" + user_id + "/all-albums.zip'>download here</a>";
				var str = '<div class="alert alert-success" style="padding:9px 0 9px 12px;"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;</i><b>success!</b> All the albums downloaded successfully. <span class="attch-header"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;' + url + '</span></div>';
				jQuery('#user_info').html(str);
			}catch(err){
				console.log(err.message);
				toggleLoading(false);
				// show bootstrap alert
				var str = '<div class="alert alert-danger" style="padding:9px 0 9px 12px;"><i class="icon-envelope">something went wrong.</div>';
				jQuery('#user_info').html(str);
			}
		}
	};
	xhttp.open("GET", url, true);
	xhttp.send();
}

// function to download album to local
function downloadToLocal(album_name){
	var user_id = <?php echo $_SESSION['user_id'] ?>;
	location.href="user_space/" + user_id + "/" + album_name + ".zip";
}

// google sign in function
function onSignIn(googleUser) {
	var profile = googleUser.getBasicProfile();
	console.log('ID: ' + profile.getId());
	console.log('Name: ' + profile.getName());
	console.log('Image URL: ' + profile.getImageUrl());
	console.log('Email: ' + profile.getEmail());
	jQuery('.moveToDrive').css('display','block');
	jQuery('.infotouser').text('Hello ' + profile.getName() + '.. Now you are signed in - ' + profile.getEmail());
}

// function used to generate pipe separated values
function getUploadSelectedValues(album_name){
	var aname = jQuery('#uSelect').val();
	if(aname == '')
		aname = album_name;
	else
		aname = aname + '|' + album_name;
	// store it to hidden variable
	jQuery('#uSelect').val(aname);
}

// function used to upload selected albums to drive
function uploadSelectedAll(){
	var album_names = jQuery('#uSelect').val();
	if(album_names == '' || album_names == null || album_names.length <= 0){
		alert('Please select album....');
		return false;
	}
	location.href='uploadToDrive.php?album_name=' + album_names + '&range=selected';
}

// hit link for all the albums
function uploadAllAlbums(){
	//downloadAllTheAlbums('downloadForUploadScript');
	location.href='uploadToDrive.php?album_name=&range=allAlbums';
}

/* function moveToDrive(album_name){
	toggleLoading(true);
	jQuery.ajax({
		url: "uploadToDrive.php",
		method: 'POST',
		data: {album_name:album_name,range:'single'},
		success: function(response){
			console.log(response);
			if(response == 'uploaded'){
				alert('Uploaded to drive');
			}
			if(response == 'AlbumNotFound'){
				alert('Download album to Move your Google drive...');
			}
			toggleLoading(false);
		},
		error: function(error){
			console.log("Something went wrong...");
		}
	});
} */

</script>