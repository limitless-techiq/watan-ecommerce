@php 
	$seo = null;
	if($seo_content){
		$seo = json_decode($seo_content->value, true);
		$seoImage = show_image(file_path()['seo_image']['path'].'/'.$seo['seo_image']);
		$seoSocialImage = show_image(file_path()['seo_image']['path'].'/'.$seo['social_image']);
	}
@endphp

@if($seo)
	<meta name="title" content="{{($general->meta_title)}}">
	<meta name="description" content="{{$seo['meta_description']}}">
	<meta name="robots" content="index,follow">
	<meta itemprop="image" content="{{$seoImage}}">
	<meta property="og:url" content="{{url()->current()}}">
	<meta property="og:type" content="website">
	<meta property="og:title" content="{{$seo['social_title']}}">
	<meta property="og:description" content="{{$seo['social_description']}}">
	<meta property="og:image" content="{{$seoSocialImage}}">
@endif