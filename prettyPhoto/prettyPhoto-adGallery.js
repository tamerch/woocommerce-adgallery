function adPrettyPhoto() {

	var links = jQuery('.ad-nav').find("a");
	var api_images = [];

	for (var i = 0; i < links.size(); i++) {
		api_images[i] = jQuery(links[i]).attr('href');
		jQuery(jQuery('.ad-nav').find("img")[i]).attr('width',450)
	}
	//api_titles = ['Title 1','Title 2','Title 3'];
	//api_descriptions = ['Description 1','Description 2','Description 3']
	jQuery.prettyPhoto.open(api_images);//,api_titles,api_descriptions);
}