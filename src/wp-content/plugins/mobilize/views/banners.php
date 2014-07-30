<section id="mobilize-banners" class="mobilize-widget clearfix" style="padding-left: {{ padding }}; padding-right: {{ padding }};">
    <h6>{{ bannerTitle }}</h6>
    <p class="section-description">
        {{ bannerDescription }}
    </p>

    <div class="mobilize-banners" style="padding-left: 0;">
        <!-- banner de 250x250 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-1" src="{{ bannerURL250 }}">
        </div>
				<p>
				<div data-type="button" class="fb-share-button"
					data-href="{{ bannerURL250 }}"></div>
				</p>
        <textarea class="mobilize-code">{{ bannerCode250 }}</textarea>
    </div>

    <div class="mobilize-banners">
        <!-- banner de 200x200 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-2" src="{{ bannerURL200 }}">
        </div>
				<p>
				<div data-type="button" class="fb-share-button"
					data-href="{{ bannerURL200 }}"></div>
				</p>
        <textarea class="mobilize-code">{{ bannerCode200 }}</textarea>
    </div>

    <div class="mobilize-banners">
        <!-- banner de 125x125 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-3" src="{{ bannerURL125 }}">
        </div>
				<p>
				<div data-type="button" class="fb-share-button"
					data-href="{{ bannerURL125 }}"></div>
				</p>
        <textarea class="mobilize-code">{{ bannerCode125 }}</textarea>
    </div>

    <div class="mobilize-clear"></div>
</section>
